<?php

namespace App\Livewire;

use App\Models\Plan;
use App\Models\Site;
use App\Services\WebsiteAiGenerator;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Support\Website\TemplateLibrary;
use Livewire\Component;

#[Title('Criar website')]
#[Layout('layouts.app')]
class CreateSite extends Component
{
    public int $step = 1;

    public string $creationMode = 'ai';
    public string $type = 'personal';
    public string $template = 'studio';
    public string $name = '';
    public string $slug = '';
    public string $description = '';
    public string $audience = '';
    public string $goal = 'contact';
    public string $style = 'Moderno';
    public string $additionalInfo = '';
    public string $headline = '';
    public string $about = '';
    public string $featuredProducts = '';
    public string $shipping = '';
    public string $returns = '';
    public string $contactEmail = '';
    public string $primaryColor = '#635bff';
    public string $secondaryColor = '#111827';
    public string $font = 'Inter';

    public array $pages = ['home', 'about', 'contact'];
    public array $types = [];
    public array $templates = [];
    public array $pageLabels = [];

    public function mount(): void
    {
        $this->types = config('website.types', []);
        $this->templates = config('website.templates', []);
        $this->pageLabels = config('website.pages', []);
        $this->pages = array_values(array_unique(array_filter($this->pages, fn ($page) => array_key_exists($page, $this->pageLabels))));
        if ($this->pages === []) {
            $this->pages = ['home'];
        }
        $this->creationMode = 'ai';
        $this->type = 'personal';
        $this->template = 'studio';
    }

    public function updatedName($value): void
    {
        $this->slug = Str::slug($value);
    }

    public function updatedType(string $value): void
    {
        abort_unless(array_key_exists($value, config('website.types', [])), 422);

        $this->type = $value;
        $this->template = $this->recommendedTemplate($value);
        $this->pages = config("website.recommended_pages.{$value}", ['home', 'contact']);
        $this->style = config("website.templates.{$this->template}.style", 'Moderno');
        $this->primaryColor = config("website.templates.{$this->template}.accent", '#635bff');
    }

    public function selectCreationMode(string $mode): void
    {
        abort_unless($mode === 'ai', 422);
        $this->creationMode = 'ai';
    }

    public function next(): void
    {
        if ($this->step === 1) {
            $this->validate(['creationMode' => ['required', 'in:ai']]);
        }

        if ($this->step === 2) {
            $this->validate([
                'type' => ['required', 'in:'.implode(',', array_keys(config('website.types', [])))],
                'template' => ['required', 'in:'.implode(',', array_keys(config('website.templates', [])))],
            ]);
        }

        if ($this->step === 3) {
            if ($this->type === 'online_store' && trim($this->description) === '') {
                $this->description = trim($this->about ?: $this->headline ?: 'Loja online');
            }

            $rules = [
                'name' => ['required', 'min:2', 'max:80'],
                'description' => ['required', 'string', 'max:1000'],
            ];

            if ($this->type === 'online_store') {
                $rules['headline'] = ['nullable', 'string', 'max:200'];
                $rules['about'] = ['nullable', 'string', 'max:1000'];
                $rules['featuredProducts'] = ['nullable', 'string', 'max:1500'];
                $rules['shipping'] = ['nullable', 'string', 'max:1000'];
                $rules['returns'] = ['nullable', 'string', 'max:1000'];
                $rules['contactEmail'] = ['nullable', 'email', 'max:255'];
            }

            $this->validate($rules);
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
        abort_unless(array_key_exists($page, config('website.pages', [])), 422);

        if (in_array($page, $this->pages, true)) {
            if (count($this->pages) > 1) {
                $this->pages = array_values(array_diff($this->pages, [$page]));
            }

            return;
        }

        $this->pages[] = $page;
    }

    public function create(WebsiteAiGenerator $generator)
    {
        if ($this->type === 'online_store' && trim($this->description) === '') {
            $this->description = trim($this->about ?: $this->headline ?: 'Loja online');
        }

        $this->validate([
            'name' => ['required', 'min:2', 'max:80'],
            'slug' => ['required', 'alpha_dash', 'unique:sites,slug'],
            'type' => ['required', 'in:'.implode(',', array_keys(config('website.types', [])))],
            'template' => ['required', 'in:'.implode(',', array_keys(config('website.templates', [])))],
            'creationMode' => ['required', 'in:ai'],
            'primaryColor' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'secondaryColor' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'font' => ['required', 'string', 'max:40'],
            'description' => ['required', 'string', 'max:1000'],
        ]);

        $this->pages = array_values(array_unique(array_filter($this->pages, fn ($page) => array_key_exists($page, $this->pageLabels))));
        if ($this->pages === []) {
            $this->pages = ['home'];
        }

        $plan = Plan::query()->where('name', 'Free')->first() ?? Plan::query()->first();
        $template = config("website.templates.{$this->template}", []);

        $blueprint = $generator->generate([
            'mode' => 'ai',
            'type' => $this->type,
            'template' => $this->template,
            'business_name' => $this->name,
            'description' => $this->description,
            'audience' => $this->type === 'online_store' ? 'Clientes que procuram produtos online' : '',
            'goal' => $this->type === 'online_store' ? 'venda' : 'contact',
            'style' => $this->style,
            'pages' => $this->pages,
            'additional_info' => $this->additionalInfoForType(),
        ]);
        $aiPages = is_array($blueprint['pages'] ?? null) ? $blueprint['pages'] : [];

        $libraryPages = TemplateLibrary::pages($this->template, [
            'name' => $this->name,
            'description' => $this->description,
            'audience' => $this->type === 'online_store' ? 'Clientes que procuram produtos online' : '',
            'goal_label' => $this->goalLabel(),
            'style' => $this->style,
        ]);

        $theme = [
            'primary' => $this->primaryColor,
            'secondary' => $this->secondaryColor,
            'accent' => $template['accent'] ?? $this->primaryColor,
            'background' => '#ffffff',
            'text' => '#111827',
            'heading_font' => $this->font,
            'body_font' => $this->font,
            'radius' => 'lg',
            'shadow' => 'sm',
            'content_width' => '1200px',
        ];

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
            'theme' => $theme,
            'settings' => [
                'template' => $this->template,
                'locale' => 'pt-PT',
                'timezone' => 'Europe/Lisbon',
                'builder' => [
                    'creation_mode' => 'ai',
                    'template_style' => $this->style,
                    'brief' => [
                        'business_name' => $this->name,
                        'description' => $this->description,
                        'audience' => $this->type === 'online_store' ? 'Clientes que procuram produtos online' : '',
                        'goal' => $this->type === 'online_store' ? 'venda' : 'contact',
                        'additional_info' => $this->additionalInfoForType(),
                    ],
                ],
            ],
            'seo' => ['title' => $this->name],
        ]);

        $site->members()->syncWithoutDetaching([auth()->id() => ['role' => 'owner']]);

        foreach ($this->pages as $index => $pageSlug) {
            if (! array_key_exists($pageSlug, $this->pageLabels)) {
                continue;
            }

            $page = $site->pages()->create([
                'name' => $this->pageLabels[$pageSlug],
                'slug' => $pageSlug,
                'status' => 'draft',
                'is_homepage' => $pageSlug === 'home',
                'sort_order' => $index,
            ]);

            $sections = $aiPages[$pageSlug]
                ?? $libraryPages[$pageSlug]
                ?? $this->pageBlueprint($pageSlug);

            foreach ($sections as $sectionIndex => $section) {
                $page->sections()->create([
                    'type' => $section['type'],
                    'label' => $section['label'] ?? Str::headline($section['type']),
                    'content' => $section['content'] ?? [],
                    'settings' => array_merge(
                        ['background' => 'transparent', 'padding' => 'lg', 'align' => 'left'],
                        $section['settings'] ?? [],
                    ),
                    'sort_order' => $sectionIndex,
                    'is_visible' => true,
                ]);
            }
        }

        session()->flash('status', ($this->types[$this->type]['label'] ?? 'Website').' criado com AI Premium.');

        return redirect()->route('builder.edit', $site);
    }

    private function pageBlueprint(string $pageSlug): array
    {
        $name = $this->name ?: 'O teu nome';

        return match ($pageSlug) {
            'home' => [
                ['type' => 'hero', 'content' => ['title' => $name, 'subtitle' => $this->description ?: 'Uma presença digital profissional.', 'button_label' => 'Entrar em contacto', 'button_url' => '#contacto']],
                ['type' => 'cta', 'content' => ['title' => 'Vamos falar?', 'description' => 'Entra em contacto para saber mais.', 'button_label' => 'Entrar em contacto', 'button_url' => '#contacto']],
            ],
            'about' => [
                ['type' => 'hero', 'content' => ['title' => 'Sobre mim', 'subtitle' => $this->description ?: 'Conhece-me melhor.', 'button_label' => 'Contactar', 'button_url' => '#contacto']],
                ['type' => 'text', 'content' => ['title' => 'Sobre mim', 'body' => $this->description ?: 'Adiciona aqui mais informação sobre ti.']],
            ],
            'contact' => [
                ['type' => 'hero', 'content' => ['title' => 'Contacta-me', 'subtitle' => 'Entra em contacto.', 'button_label' => 'Enviar mensagem', 'button_url' => '#formulario']],
                ['type' => 'contact_form', 'content' => ['title' => 'Vamos conversar', 'description' => 'Envia uma mensagem.', 'button_label' => 'Enviar mensagem']],
            ],
            default => [],
        };
    }

    private function recommendedTemplate(string $type): string
    {
        return match ($type) {
            'online_store' => 'commerce',
            'business', 'agency', 'clinic', 'lawyer', 'real_estate' => 'professional',
            'portfolio', 'photographer' => 'creative',
            'restaurant', 'hotel', 'beauty', 'cafe' => 'elegant',
            'landing', 'startup' => 'launch',
            default => 'studio',
        };
    }

    private function goalLabel(): string
    {
        return $this->type === 'online_store' ? 'Comprar agora' : 'Entrar em contacto';
    }

    private function additionalInfoForType(): string
    {
        if ($this->type !== 'online_store') {
            return $this->additionalInfo;
        }

        return collect([
            $this->headline !== '' ? "Mensagem principal: {$this->headline}" : null,
            $this->about !== '' ? "Sobre a marca: {$this->about}" : null,
            $this->featuredProducts !== '' ? "Produtos em destaque: {$this->featuredProducts}" : null,
            $this->shipping !== '' ? "Envios e entregas: {$this->shipping}" : null,
            $this->returns !== '' ? "Trocas e devoluções: {$this->returns}" : null,
            $this->contactEmail !== '' ? "Email de apoio: {$this->contactEmail}" : null,
        ])->filter()->implode("\n");
    }
}
