<?php

namespace App\Livewire;

use App\Models\Plan;
use App\Models\Site;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Criar website')]
#[Layout('layouts.app')]
class CreateSite extends Component
{
    public int $step = 1;
    public string $type = 'business';
    public string $template = 'studio';
    public string $name = '';
    public string $slug = '';
    public string $primaryColor = '#635bff';
    public string $secondaryColor = '#111827';
    public string $font = 'Inter';
    public array $pages = ['home', 'about', 'services', 'contact'];

    public array $types = [
        'personal' => ['label' => 'Pessoal', 'description' => 'Um espaço pessoal ou página de apresentação.'],
        'portfolio' => ['label' => 'Portfólio', 'description' => 'Mostra trabalho, projetos e experiência.'],
        'business' => ['label' => 'Empresa', 'description' => 'Uma presença profissional para a tua empresa.'],
        'online_store' => ['label' => 'Loja online', 'description' => 'Produtos, catálogo e estrutura de ecommerce.'],
        'blog' => ['label' => 'Blog', 'description' => 'Conteúdo, artigos e publicação regular.'],
        'restaurant' => ['label' => 'Restaurante', 'description' => 'Menu, localização, reservas e contacto.'],
        'services' => ['label' => 'Serviços', 'description' => 'Apresenta serviços e capta contactos.'],
        'landing' => ['label' => 'Landing page', 'description' => 'Uma página focada numa oferta ou campanha.'],
    ];

    public array $templates = [
        'studio' => ['label' => 'Studio', 'description' => 'Minimalista e editorial.'],
        'launch' => ['label' => 'Launch', 'description' => 'Moderno e orientado para conversão.'],
        'commerce' => ['label' => 'Commerce', 'description' => 'Focado em produtos e catálogo.'],
        'elegant' => ['label' => 'Elegant', 'description' => 'Premium, sofisticado e espaçado.'],
    ];

    public function updatedName($value): void { $this->slug = Str::slug($value); }

    public function next(): void
    {
        if ($this->step === 1) $this->validate(['type' => 'required|in:personal,portfolio,business,online_store,blog,restaurant,services,landing']);
        if ($this->step === 2) $this->validate(['template' => 'required|in:studio,launch,commerce,elegant']);
        if ($this->step < 4) $this->step++;
    }

    public function previous(): void { if ($this->step > 1) $this->step--; }

    public function togglePage(string $page): void
    {
        if ($page === 'home') return;
        $this->pages = in_array($page, $this->pages, true) ? array_values(array_diff($this->pages, [$page])) : [...$this->pages, $page];
    }

    public function create()
    {
        $this->validate([
            'name' => 'required|min:2|max:80',
            'slug' => 'required|alpha_dash|unique:sites,slug',
            'type' => 'required|string|max:40',
            'template' => 'required|string|max:40',
            'primaryColor' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'secondaryColor' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'font' => 'required|string|max:40',
        ]);

        $plan = Plan::query()->where('name', 'Free')->first() ?? Plan::query()->first();
        $site = Site::create([
            'name' => $this->name,
            'slug' => $this->slug,
            'owner_id' => auth()->id(),
            'plan_id' => $plan?->id,
            'status' => 'draft',
            'is_published' => false,
            'category_label' => $this->types[$this->type]['label'] ?? 'Website',
            'type' => $this->type,
            'primary_color' => $this->primaryColor,
            'secondary_color' => $this->secondaryColor,
            'theme' => ['primary' => $this->primaryColor, 'secondary' => $this->secondaryColor, 'accent' => $this->primaryColor, 'background' => '#ffffff', 'text' => '#111827', 'heading_font' => $this->font, 'body_font' => $this->font, 'radius' => 'lg', 'shadow' => 'sm', 'content_width' => '1200px'],
            'settings' => ['template' => $this->template, 'locale' => 'pt-PT', 'timezone' => 'Europe/Lisbon'],
            'seo' => ['title' => $this->name],
        ]);

        $site->members()->syncWithoutDetaching([auth()->id() => ['role' => 'owner']]);

        $labels = ['home' => 'Home', 'about' => 'Sobre', 'services' => 'Serviços', 'products' => 'Produtos', 'blog' => 'Blog', 'contact' => 'Contacto', 'faq' => 'FAQ', 'privacy' => 'Privacidade'];
        foreach (array_values(array_unique($this->pages)) as $index => $pageSlug) {
            $page = $site->pages()->create([
                'name' => $labels[$pageSlug] ?? Str::headline($pageSlug),
                'slug' => $pageSlug,
                'status' => 'draft',
                'is_homepage' => $pageSlug === 'home',
                'sort_order' => $index,
            ]);

            if ($pageSlug === 'home') {
                foreach (['hero', 'feature_grid', 'cta'] as $sectionIndex => $sectionType) {
                    $page->sections()->create([
                        'type' => $sectionType,
                        'label' => Str::headline($sectionType),
                        'content' => $this->defaultContent($sectionType),
                        'settings' => ['background' => 'transparent', 'padding' => 'lg'],
                        'sort_order' => $sectionIndex,
                    ]);
                }
            } else {
                $page->sections()->create([
                    'type' => 'text',
                    'label' => $labels[$pageSlug] ?? Str::headline($pageSlug),
                    'content' => ['title' => $labels[$pageSlug] ?? Str::headline($pageSlug), 'body' => 'Personaliza esta página no editor.'],
                    'settings' => ['background' => 'transparent', 'padding' => 'lg'],
                    'sort_order' => 0,
                ]);
            }
        }

        session()->flash('status', 'Website criado. Agora podes começar a editá-lo.');
        return redirect()->route('builder.edit', $site);
    }

    private function defaultContent(string $type): array
    {
        return match ($type) {
            'hero' => ['title' => 'Cria algo incrível.', 'subtitle' => 'Personaliza este conteúdo diretamente no Finder.', 'button_label' => 'Saber mais', 'button_url' => '#'],
            'feature_grid' => ['title' => 'Tudo o que precisas', 'items' => [['title' => 'Simples', 'description' => 'Edita sem código.'], ['title' => 'Flexível', 'description' => 'Adapta o teu design.'], ['title' => 'Rápido', 'description' => 'Publica em poucos minutos.']]],
            'cta' => ['title' => 'Pronto para começar?', 'description' => 'Publica o teu website quando estiveres satisfeito.', 'button_label' => 'Começar'],
            default => ['title' => Str::headline($type), 'description' => 'Personaliza esta secção.'],
        };
    }

    public function render() { return view('livewire.create-site'); }
}
