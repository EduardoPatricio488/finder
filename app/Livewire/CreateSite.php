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

    public array $types = [];

    public array $templates = [];

    public function mount(): void
    {
        $this->types = config('website.types', []);
        $this->templates = config('website.templates', []);
    }

    public function updatedName($value): void
    {
        $this->slug = Str::slug($value);
    }

    public function next(): void
    {
        if ($this->step === 1) {
            $this->validate(['type' => ['required', 'in:'.implode(',', array_keys($this->types))]]);
        }

        if ($this->step === 2) {
            $this->validate(['template' => ['required', 'in:'.implode(',', array_keys($this->templates))]]);
        }

        if ($this->step < 4) {
            $this->step++;
        }
    }

    public function previous(): void
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    public function togglePage(string $page): void
    {
        if (! array_key_exists($page, config('website.pages', [])) || $page === 'home') {
            return;
        }

        $this->pages = in_array($page, $this->pages, true)
            ? array_values(array_diff($this->pages, [$page]))
            : [...$this->pages, $page];
    }

    public function create()
    {
        $this->validate([
            'name' => 'required|min:2|max:80',
            'slug' => 'required|alpha_dash|unique:sites,slug',
            'type' => ['required', 'in:'.implode(',', array_keys($this->types))],
            'template' => ['required', 'in:'.implode(',', array_keys($this->templates))],
            'primaryColor' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'secondaryColor' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'font' => 'required|string|max:40',
        ]);

        $plan = Plan::query()->where('name', 'Free')->first() ?? Plan::query()->first();
        $pageLabels = config('website.pages', []);

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
            'theme' => [
                'primary' => $this->primaryColor,
                'secondary' => $this->secondaryColor,
                'accent' => $this->primaryColor,
                'background' => '#ffffff',
                'text' => '#111827',
                'heading_font' => $this->font,
                'body_font' => $this->font,
                'radius' => 'lg',
                'shadow' => 'sm',
                'content_width' => '1200px',
            ],
            'settings' => [
                'template' => $this->template,
                'locale' => 'pt-PT',
                'timezone' => 'Europe/Lisbon',
            ],
            'seo' => ['title' => $this->name],
        ]);

        $site->members()->syncWithoutDetaching([auth()->id() => ['role' => 'owner']]);

        foreach (array_values(array_unique($this->pages)) as $index => $pageSlug) {
            if (! array_key_exists($pageSlug, $pageLabels)) {
                continue;
            }

            $page = $site->pages()->create([
                'name' => $pageLabels[$pageSlug],
                'slug' => $pageSlug,
                'status' => 'draft',
                'is_homepage' => $pageSlug === 'home',
                'sort_order' => $index,
            ]);

            $sections = match ($pageSlug) {
                'home' => ['hero', 'feature_grid', 'cta'],
                'contact' => ['text', 'contact_form'],
                'products' => ['text', 'product_grid'],
                'blog' => ['text', 'blog_posts'],
                'faq' => ['text', 'faq'],
                default => ['text'],
            };

            foreach ($sections as $sectionIndex => $sectionType) {
                $page->sections()->create([
                    'type' => $sectionType,
                    'label' => Str::headline($sectionType),
                    'content' => $this->defaultContent($sectionType, $pageSlug),
                    'settings' => ['background' => 'transparent', 'padding' => 'lg'],
                    'sort_order' => $sectionIndex,
                ]);
            }
        }

        session()->flash('status', 'Website criado. Agora podes começar a editá-lo.');

        return redirect()->route('builder.edit', $site);
    }

    private function defaultContent(string $type, string $pageSlug = 'home'): array
    {
        $typeLabel = $this->types[$this->type]['label'] ?? 'website';

        return match ($type) {
            'hero' => [
                'title' => $this->name,
                'subtitle' => $this->defaultTagline($typeLabel),
                'button_label' => 'Conhecer',
                'button_url' => '#',
            ],
            'feature_grid' => [
                'title' => 'Uma presença digital clara e profissional',
                'items' => [
                    ['title' => 'Conteúdo', 'description' => 'Apresenta o que fazes com uma estrutura simples e clara.'],
                    ['title' => 'Experiência', 'description' => 'Organiza páginas, navegação e conteúdo num único website.'],
                    ['title' => 'Contacto', 'description' => 'Facilita o contacto através dos canais que escolheres.'],
                ],
            ],
            'cta' => [
                'title' => 'Fala connosco',
                'description' => 'Encontra a informação certa e entra em contacto.',
                'button_label' => 'Contactar',
                'button_url' => '#',
            ],
            'contact_form' => [
                'title' => 'Entra em contacto',
                'description' => 'Envia uma mensagem e entraremos em contacto contigo.',
            ],
            'product_grid' => ['title' => 'Produtos'],
            'blog_posts' => ['title' => 'Artigos', 'items' => []],
            'faq' => ['title' => 'Perguntas frequentes', 'items' => []],
            default => [
                'title' => $pageSlug === 'about' ? 'Sobre nós' : ($this->types[$this->type]['label'] ?? Str::headline($pageSlug)),
                'body' => $this->defaultPageBody($pageSlug, $typeLabel),
            ],
        };
    }

    private function defaultTagline(string $typeLabel): string
    {
        return match ($this->type) {
            'portfolio' => 'Uma seleção do nosso trabalho, projetos e experiência.',
            'online_store' => 'Descobre os nossos produtos e encontra o que procuras.',
            'restaurant' => 'Conhece o nosso espaço, menu e formas de contacto.',
            'blog' => 'Ideias, artigos e conteúdos publicados num só lugar.',
            'services' => 'Conhece os nossos serviços e encontra a solução certa.',
            'landing' => 'Uma presença digital focada naquilo que queres comunicar.',
            'personal' => 'Um espaço pessoal para apresentar quem somos e o que fazemos.',
            default => "Uma presença digital profissional para $typeLabel.",
        };
    }

    private function defaultPageBody(string $pageSlug, string $typeLabel): string
    {
        return match ($pageSlug) {
            'about' => 'Conhece melhor o nosso projeto, experiência e forma de trabalhar.',
            'services' => 'Apresentamos aqui os serviços que disponibilizamos e a forma como podemos ajudar.',
            'products' => 'Consulta os produtos disponíveis e escolhe os que melhor respondem às tuas necessidades.',
            'blog' => 'Explora os nossos artigos, novidades e conteúdos.',
            'faq' => 'Encontra respostas às perguntas mais frequentes sobre este website e os nossos serviços.',
            'privacy' => 'Consulta aqui a informação de privacidade e tratamento de dados deste website.',
            default => "Este é o espaço dedicado a apresentar $typeLabel de forma clara e profissional.",
        };
    }

    public function render(): mixed
    {
        return view('livewire.create-site');
    }
}
