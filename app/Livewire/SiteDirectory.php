<?php

namespace App\Livewire;

use App\Models\Site;
use App\Support\SiteContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.hub')]
class SiteDirectory extends Component
{
    public bool $showForm = false;

    public ?int $editingSiteId = null;

    public string $selectedTemplate = 'casa';

    public string $name = '';

    public string $slug = '';

    public string $tagline = '';

    public string $description = '';

    public string $categoryLabel = 'Site';

    public string $accent = 'amber';

    public string $type = 'online_store';

    public string $status = 'online';

    public string $primaryColor = '#f59e0b';

    public string $secondaryColor = '#1c1917';

    public bool $isPublished = true;

    public function create(): void
    {
        $this->ensureAuthenticated();
        $this->ensureCanCreateSites();
        $this->resetForm();
        $this->applyTemplate('casa');
        $this->showForm = true;
    }

    public function chooseTemplate(string $template): void
    {
        $this->ensureAuthenticated();
        $this->ensureCanCreateSites();
        $this->applyTemplate($template);
        $this->showForm = true;
    }

    public function edit(int $siteId): void
    {
        $this->ensureAuthenticated();

        $site = $this->manageableSitesQuery()->findOrFail($siteId);

        $this->editingSiteId = $site->id;
        $this->selectedTemplate = 'custom';
        $this->name = $site->name;
        $this->slug = $site->slug;
        $this->tagline = $site->tagline ?? '';
        $this->description = $site->description ?? '';
        $this->categoryLabel = $site->category_label;
        $this->accent = $site->accent;
        $this->type = $site->type;
        $this->status = $site->status;
        $this->primaryColor = $site->primary_color;
        $this->secondaryColor = $site->secondary_color;
        $this->isPublished = $site->is_published;
        $this->showForm = true;
    }

    public function cancel(): void
    {
        $this->resetForm();
    }

    public function save(): void
    {
        $this->ensureAuthenticated();

        if ($this->editingSiteId === null) {
            $this->ensureCanCreateSites();
        }

        if (blank($this->slug) && filled($this->name)) {
            $this->slug = Str::slug($this->name);
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:80', 'alpha_dash', Rule::unique('sites', 'slug')->ignore($this->editingSiteId)],
            'tagline' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'categoryLabel' => ['required', 'string', 'max:40'],
            'accent' => ['required', 'in:amber,stone,emerald,sky'],
            'type' => ['required', 'in:online_store,restaurant,services,portfolio,blog'],
            'status' => ['required', 'in:online,offline'],
            'primaryColor' => ['required', 'string', 'max:20'],
            'secondaryColor' => ['required', 'string', 'max:20'],
            'isPublished' => ['boolean'],
        ]);

        $site = $this->editingSiteId === null
            ? new Site
            : $this->manageableSitesQuery()->findOrFail($this->editingSiteId);

        if ($site->exists && $site->isProtected()) {
            $validated['slug'] = $site->slug;
            $validated['isPublished'] = true;
        }

        $site->fill([
            'owner_id' => $site->exists ? $site->owner_id : auth()->id(),
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'tagline' => $validated['tagline'] ?: null,
            'description' => $validated['description'] ?: null,
            'category_label' => $this->siteTypeLabel($validated['type']),
            'accent' => $validated['accent'],
            'type' => $validated['type'],
            'status' => $validated['status'],
            'primary_color' => $validated['primaryColor'],
            'secondary_color' => $validated['secondaryColor'],
            'subdomain' => $validated['slug'],
            'homepage' => [
                'hero_title' => $validated['tagline'] ?: $validated['name'],
                'hero_subtitle' => $validated['description'] ?: 'Gerir cada projeto a partir daqui.',
                'primary_button_label' => 'Explorar produtos',
                'sections' => [
                    'hero' => true,
                    'featured_products' => true,
                    'about' => true,
                    'categories' => true,
                    'newsletter' => true,
                    'footer' => true,
                ],
            ],
            'is_published' => $validated['isPublished'],
            'sort_order' => $site->exists ? $site->sort_order : ((int) Site::query()->max('sort_order')) + 1,
        ]);
        $site->save();

        $site->members()->syncWithoutDetaching([
            auth()->id() => ['role' => 'proprietario'],
        ]);

        $this->resetForm();
        session()->flash('status', 'Site guardado com sucesso.');
    }

    public function delete(int $siteId): void
    {
        $this->ensureAuthenticated();

        $site = $this->manageableSitesQuery()->findOrFail($siteId);

        abort_if($site->isProtected(), 403);

        $site->delete();
        session()->flash('status', 'Site removido com sucesso.');
    }

    public function render(): mixed
    {
        $user = auth()->user();
        $sitesQuery = ($user === null ? Site::query()->published() : $this->manageableSitesQuery())
            ->orderBy('sort_order')
            ->orderBy('name');

        if (Schema::hasColumn('products', 'site_id')) {
            $sitesQuery->withCount('products');
        }

        if (Schema::hasColumn('orders', 'site_id')) {
            $sitesQuery
                ->withCount(['orders as sales_count' => fn ($query) => $query->where('status', '!=', 'cancelada')])
                ->withMax('orders', 'updated_at');
        }

        $sites = $sitesQuery->get();

        return view('livewire.site-directory', [
            'sites' => $sites,
            'canManageSites' => $user !== null,
            'canCreateSites' => $user?->canCreateSites() === true,
            'currentPlanLabel' => $this->currentPlanLabel(),
            'siteTemplates' => $this->siteTemplates(),
        ]);
    }

    private function ensureAuthenticated(): void
    {
        abort_unless(auth()->check(), 403);
    }

    private function ensureCanCreateSites(): void
    {
        abort_unless(auth()->user()?->canCreateSites() === true, 403, 'Precisa do Finder Premium para criar um novo site.');
    }

    private function manageableSitesQuery(): Builder
    {
        $user = auth()->user();

        abort_unless($user !== null, 403);

        return SiteContext::manageableSitesQuery($user);
    }

    private function resetForm(): void
    {
        $this->reset([
            'showForm',
            'editingSiteId',
            'selectedTemplate',
            'name',
            'slug',
            'tagline',
            'description',
        ]);

        $this->categoryLabel = 'Site';
        $this->accent = 'amber';
        $this->type = 'online_store';
        $this->status = 'online';
        $this->primaryColor = '#f59e0b';
        $this->secondaryColor = '#1c1917';
        $this->isPublished = true;
        $this->showForm = false;
    }

    /**
     * @return array<string, array<string, string>>
     */
    private function siteTemplates(): array
    {
        return [
            'casa' => [
                'name' => 'Nova loja',
                'label' => 'Modelo Casa & Co.',
                'description' => 'Loja online premium com o mesmo estilo, estrutura e homepage da Casa & Co.',
                'tagline' => 'Coisas bonitas para viver melhor.',
                'body' => 'Uma seleção cuidada de produtos para oferecer, decorar e aproveitar os pequenos momentos do dia.',
                'accent' => 'amber',
                'type' => 'online_store',
                'primary_color' => '#f59e0b',
                'secondary_color' => '#1c1917',
            ],
            'tech' => [
                'name' => 'Tech Store',
                'label' => 'Tecnologia',
                'description' => 'Loja online para gadgets, acessórios e equipamentos.',
                'tagline' => 'Tecnologia para o teu dia a dia.',
                'body' => 'Produtos úteis, rápidos de encontrar e organizados para compras simples.',
                'accent' => 'sky',
                'type' => 'online_store',
                'primary_color' => '#0284c7',
                'secondary_color' => '#0f172a',
            ],
            'fashion' => [
                'name' => 'Fashion Store',
                'label' => 'Moda',
                'description' => 'Loja online para roupa, acessórios e coleções sazonais.',
                'tagline' => 'Peças escolhidas para todos os dias.',
                'body' => 'Uma montra elegante para apresentar coleções, novidades e produtos em destaque.',
                'accent' => 'stone',
                'type' => 'online_store',
                'primary_color' => '#78716c',
                'secondary_color' => '#1c1917',
            ],
            'restaurant' => [
                'name' => 'Restaurante',
                'label' => 'Restaurante',
                'description' => 'Estrutura preparada para menus, reservas e encomendas futuras.',
                'tagline' => 'Sabores para partilhar.',
                'body' => 'Um espaço para apresentar menus, horários, contactos e pedidos.',
                'accent' => 'emerald',
                'type' => 'restaurant',
                'primary_color' => '#059669',
                'secondary_color' => '#064e3b',
            ],
        ];
    }

    private function applyTemplate(string $template): void
    {
        $templateData = $this->siteTemplates()[$template] ?? $this->siteTemplates()['casa'];

        $this->selectedTemplate = $template;
        $this->name = $templateData['name'];
        $this->slug = '';
        $this->tagline = $templateData['tagline'];
        $this->description = $templateData['body'];
        $this->accent = $templateData['accent'];
        $this->type = $templateData['type'];
        $this->primaryColor = $templateData['primary_color'];
        $this->secondaryColor = $templateData['secondary_color'];
        $this->status = 'online';
        $this->isPublished = true;
    }

    private function siteTypeLabel(string $type): string
    {
        return match ($type) {
            'online_store' => 'Loja online',
            'restaurant' => 'Restaurante',
            'services' => 'Serviços',
            'portfolio' => 'Portfólio',
            'blog' => 'Blog',
            default => 'Site',
        };
    }

    private function currentPlanLabel(): string
    {
        return auth()->user()?->hasFinderPremium() === true ? 'Finder Premium' : 'Finder Free';
    }
}
