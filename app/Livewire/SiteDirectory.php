<?php

namespace App\Livewire;

use App\Models\Plan;
use App\Models\Site;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.hub')]
class SiteDirectory extends Component
{
    public string $name = '';

    public string $template = 'casa';

    public string $selectedTemplate = 'casa';

    public string $tagline = '';

    public string $description = '';

    public string $categoryLabel = 'Website';

    public string $accent = 'amber';

    public string $primaryColor = '#f59e0b';

    public function create(): void
    {
        $user = auth()->user();
        abort_unless($user, 403);
        abort_unless($user->isAdministrator() || $user->hasFinderPremium(), 403);

        $this->resetCreationForm();
    }

    public function chooseTemplate(string $template): void
    {
        $templates = [
            'casa' => [
                'template' => 'casa',
                'type' => 'online_store',
                'accent' => 'amber',
                'primaryColor' => '#f59e0b',
                'tagline' => 'Coisas bonitas para viver melhor.',
                'categoryLabel' => 'Loja online',
            ],
            'tech' => [
                'template' => 'tech',
                'type' => 'online_store',
                'accent' => 'sky',
                'primaryColor' => '#0ea5e9',
                'tagline' => 'Tecnologia para o teu dia a dia.',
                'categoryLabel' => 'Loja online',
            ],
            'studio' => [
                'template' => 'studio',
                'type' => 'business',
                'accent' => 'indigo',
                'primaryColor' => '#635bff',
                'tagline' => 'Uma presença digital profissional.',
                'categoryLabel' => 'Website',
            ],
        ];

        abort_unless(isset($templates[$template]), 422);

        $definition = $templates[$template];
        $this->template = $definition['template'];
        $this->selectedTemplate = $definition['template'];
        $this->accent = $definition['accent'];
        $this->primaryColor = $definition['primaryColor'];
        $this->tagline = $definition['tagline'];
        $this->categoryLabel = $definition['categoryLabel'];
    }

    public function save(): void
    {
        $user = auth()->user();
        abort_unless($user, 403);
        abort_unless($user->isAdministrator() || $user->hasFinderPremium(), 403);

        $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:80'],
        ]);

        $template = $this->selectedTemplate ?: $this->template;
        $type = match ($template) {
            'casa', 'tech' => 'online_store',
            default => 'business',
        };
        $isAdmin = $user->isAdministrator();
        $slug = Str::slug($this->name);
        $baseSlug = $slug ?: 'website';
        $slug = $baseSlug;
        $counter = 2;

        while (Site::query()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter++;
        }

        $site = Site::create([
            'name' => trim($this->name),
            'slug' => $slug,
            'owner_id' => $user->id,
            'plan_id' => Plan::query()->where('name', 'Free')->value('id'),
            'tagline' => $this->tagline,
            'description' => $this->description,
            'category_label' => $type === 'online_store' ? 'Loja online' : ($this->categoryLabel ?: 'Website'),
            'accent' => $this->accent,
            'primary_color' => $this->primaryColor,
            'secondary_color' => '#111827',
            'type' => $type,
            'status' => $isAdmin ? 'published' : 'draft',
            'is_published' => $isAdmin,
            'published_at' => $isAdmin ? now() : null,
            'settings' => [
                'template' => $template,
                'locale' => 'pt-PT',
                'timezone' => 'Europe/Lisbon',
            ],
            'seo' => ['title' => trim($this->name)],
        ]);

        $site->members()->syncWithoutDetaching([$user->id => ['role' => 'owner']]);

        $page = $site->pages()->create([
            'name' => 'Home',
            'slug' => 'home',
            'status' => $isAdmin ? 'published' : 'draft',
            'is_homepage' => true,
            'sort_order' => 0,
        ]);

        $page->sections()->create([
            'type' => 'hero',
            'label' => 'Destaque principal',
            'content' => [
                'title' => $site->name,
                'subtitle' => $site->tagline ?: 'Personaliza o teu website no Finder.',
                'button_label' => 'Saber mais',
                'button_url' => '#contacto',
            ],
            'settings' => ['background' => 'transparent', 'padding' => 'lg', 'align' => 'left'],
            'sort_order' => 0,
            'is_visible' => true,
        ]);

        session()->flash('status', 'Website criado.');
        $this->redirectRoute('builder.edit', ['site' => $site]);
    }

    public function delete(int $siteId): void
    {
        $user = auth()->user();
        abort_unless($user, 403);

        $site = Site::query()->findOrFail($siteId);
        abort_if($site->isProtected(), 403);
        abort_unless($user->isAdministrator() || $site->isManageableBy($user), 403);

        $site->delete();
    }

    public function render()
    {
        $user = auth()->user();

        $sites = Site::query()
            ->when($user === null, function ($query): void {
                $query->where('is_published', true)->where('status', 'published');
            })
            ->when($user !== null && ! $user->isAdministrator(), function ($query) use ($user): void {
                $query->where(function ($visible) use ($user): void {
                    $visible->where(function ($published): void {
                        $published->where('is_published', true)->where('status', 'published');
                    })
                        ->orWhere('owner_id', $user->id)
                        ->orWhereHas('members', fn ($members) => $members->whereKey($user->id));
                });
            })
            ->with('owner')
            ->withCount(['products', 'orders as sales_count'])
            ->latest('updated_at')
            ->get();

        return view('livewire.site-directory', [
            'sites' => $sites,
            'currentPlanLabel' => $user ? 'Finder '.($user->plan ?? 'Free') : 'Finder Free',
            'canCreateSites' => $user !== null && ($user->isAdministrator() || $user->hasFinderPremium()),
        ]);
    }

    private function resetCreationForm(): void
    {
        $this->name = '';
        $this->description = '';
        $this->chooseTemplate('casa');
    }
}
