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
        $this->pages = ['home', 'about', 'contact'];
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
        abort_unless($value === 'personal', 422);

        $this->type = 'personal';
        $this->template = 'studio';
        $this->pages = ['home', 'about', 'contact'];
        $this->style = config('website.templates.studio.style', 'Moderno');
        $this->primaryColor = config('website.templates.studio.accent', '#635bff');
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
                'type' => ['required', 'in:personal'],
                'template' => ['required', 'in:studio'],
            ]);
        }

        if ($this->step === 3) {
            $this->validate([
                'name' => ['required', 'min:2', 'max:80'],
                'description' => ['required', 'string', 'max:1000'],
            ]);
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
        return;
    }

    public function create(WebsiteAiGenerator $generator)
    {
        $this->validate([
            'name' => ['required', 'min:2', 'max:80'],
            'slug' => ['required', 'alpha_dash', 'unique:sites,slug'],
            'type' => ['required', 'in:personal'],
            'template' => ['required', 'in:studio'],
            'creationMode' => ['required', 'in:ai'],
            'primaryColor' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'secondaryColor' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'font' => ['required', 'string', 'max:40'],
            'description' => ['required', 'string', 'max:1000'],
        ]);

        $this->pages = ['home', 'about', 'contact'];

        $plan = Plan::query()->where('name', 'Free')->first() ?? Plan::query()->first();
        $template = config("website.templates.{$this->template}", []);

        $blueprint = $generator->generate([
            'mode' => 'ai',
            'type' => 'personal',
            'template' => 'studio',
            'business_name' => $this->name,
            'description' => $this->description,
            'audience' => '',
            'goal' => 'contact',
            'style' => $this->style,
            'pages' => ['home', 'about', 'contact'],
            'additional_info' => '',
        ]);
        $aiPages = is_array($blueprint['pages'] ?? null) ? $blueprint['pages'] : [];

        $libraryPages = TemplateLibrary::pages($this->template, [
            'name' => $this->name,
            'description' => $this->description,
            'audience' => '',
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
            'category_label' => $this->types['personal']['label'] ?? 'Website pessoal',
            'type' => 'personal',
            'primary_color' => $this->primaryColor,
            'secondary_color' => $this->secondaryColor,
            'theme' => $theme,
            'settings' => [
                'template' => 'studio',
                'locale' => 'pt-PT',
                'timezone' => 'Europe/Lisbon',
                'builder' => [
                    'creation_mode' => 'ai',
                    'template_style' => $this->style,
                    'brief' => [
                        'business_name' => $this->name,
                        'description' => $this->description,
                        'audience' => '',
                        'goal' => 'contact',
                        'additional_info' => '',
                    ],
                ],
            ],
            'seo' => ['title' => $this->name],
        ]);

        $site->members()->syncWithoutDetaching([auth()->id() => ['role' => 'owner']]);

        foreach (['home', 'about', 'contact'] as $index => $pageSlug) {
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

        session()->flash('status', 'Website pessoal criado com AI Premium.');

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

    private function goalLabel(): string
    {
        return 'Entrar em contacto';
    }
}
