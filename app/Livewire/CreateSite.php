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

    public string $creationMode = 'model';
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

    public array $pages = ['home', 'about', 'services', 'contact'];
    public array $types = [];
    public array $templates = [];
    public array $pageLabels = [];

    public function mount(): void
    {
        $this->types = config('website.types', []);
        $this->templates = config('website.templates', []);
        $this->pageLabels = config('website.pages', []);
        $this->pages = config('website.recommended_pages.personal', ['home', 'about', 'contact']);
    }

    public function updatedName($value): void
    {
        $this->slug = Str::slug($value);
    }

    public function updatedType(string $value): void
    {
        $recommended = config("website.recommended_pages.{$value}", ['home', 'about', 'contact']);
        $this->pages = array_values(array_unique($recommended));

        $this->template = match ($value) {
            'online_store' => 'commerce',
            'restaurant', 'hotel', 'beauty' => 'elegant',
            'portfolio', 'photographer', 'agency' => 'creative',
            'lawyer', 'clinic', 'real_estate' => 'professional',
            'startup', 'landing' => 'launch',
            default => 'studio',
        };

        $this->style = config("website.templates.{$this->template}.style", 'Moderno');
        $this->primaryColor = config("website.templates.{$this->template}.accent", '#635bff');
    }

    public function selectCreationMode(string $mode): void
    {
        abort_unless(in_array($mode, ['ai', 'model', 'free'], true), 422);
        $this->creationMode = $mode;
    }

    public function next(): void
    {
        if ($this->step === 1) {
            $this->validate(['creationMode' => ['required', 'in:ai,model,free']]);
        }

        if ($this->step === 2) {
            $this->validate([
                'type' => ['required', 'in:'.implode(',', array_keys($this->types))],
                'template' => ['required', 'in:'.implode(',', array_keys($this->templates))],
            ]);
        }

        if ($this->step === 3) {
            $this->validate([
                'name' => ['required', 'min:2', 'max:80'],
                'description' => ['required_if:creationMode,ai', 'nullable', 'string', 'max:1000'],
                'audience' => ['nullable', 'string', 'max:500'],
                'goal' => ['required', 'string', 'max:80'],
                'style' => ['required', 'string', 'max:80'],
                'additionalInfo' => ['nullable', 'string', 'max:1500'],
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
        if (! array_key_exists($page, $this->pageLabels) || $page === 'home') {
            return;
        }

        $this->pages = in_array($page, $this->pages, true)
            ? array_values(array_diff($this->pages, [$page]))
            : [...$this->pages, $page];
    }

    public function create(WebsiteAiGenerator $generator)
    {
        $this->validate([
            'name' => ['required', 'min:2', 'max:80'],
            'slug' => ['required', 'alpha_dash', 'unique:sites,slug'],
            'type' => ['required', 'in:'.implode(',', array_keys($this->types))],
            'template' => ['required', 'in:'.implode(',', array_keys($this->templates))],
            'creationMode' => ['required', 'in:ai,model,free'],
            'primaryColor' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'secondaryColor' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'font' => ['required', 'string', 'max:40'],
        ]);

        $plan = Plan::query()->where('name', 'Free')->first() ?? Plan::query()->first();
        $template = config("website.templates.{$this->template}", []);

        $aiPages = [];
        if ($this->creationMode === 'ai') {
            $blueprint = $generator->generate([
                'mode' => $this->creationMode,
                'type' => $this->type,
                'template' => $this->template,
                'business_name' => $this->name,
                'description' => $this->description,
                'audience' => $this->audience,
                'goal' => $this->goal,
                'style' => $this->style,
                'pages' => array_values(array_unique($this->pages)),
                'additional_info' => $this->additionalInfo,
            ]);
            $aiPages = is_array($blueprint['pages'] ?? null) ? $blueprint['pages'] : [];
        }

        $libraryPages = TemplateLibrary::pages($this->template, [
            'name' => $this->name,
            'description' => $this->description,
            'audience' => $this->audience,
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
                    'creation_mode' => $this->creationMode,
                    'template_style' => $this->style,
                    'brief' => [
                        'business_name' => $this->name,
                        'description' => $this->description,
                        'audience' => $this->audience,
                        'goal' => $this->goal,
                        'additional_info' => $this->additionalInfo,
                    ],
                ],
            ],
            'seo' => ['title' => $this->name],
        ]);

        $site->members()->syncWithoutDetaching([auth()->id() => ['role' => 'owner']]);

        foreach (array_values(array_unique($this->pages)) as $index => $pageSlug) {
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

        session()->flash('status', 'Website criado. Agora podes editar tudo directamente no Builder.');

        return redirect()->route('builder.edit', $site);
    }

    private function pageBlueprint(string $pageSlug): array
    {
        $label = $this->types[$this->type]['label'] ?? 'negócio';
        $name = $this->name ?: 'O teu negócio';

        return match ($pageSlug) {
            'home' => [
                ['type' => 'hero', 'content' => ['title' => $name, 'subtitle' => $this->description ?: "Uma presença digital profissional para $label.", 'button_label' => $this->goalLabel(), 'button_url' => '#contacto']],
                ['type' => 'feature_grid', 'content' => ['title' => 'Porque escolher-nos', 'items' => [
                    ['title' => 'Experiência', 'description' => 'Apresenta aqui a experiência e o valor que ofereces.'],
                    ['title' => 'Qualidade', 'description' => 'Explica o que diferencia o teu trabalho.'],
                    ['title' => 'Confiança', 'description' => 'Mostra aos visitantes porque podem confiar em ti.'],
                ]]],
                ['type' => 'cta', 'content' => ['title' => 'Vamos falar?', 'description' => 'Dá o próximo passo e entra em contacto connosco.', 'button_label' => $this->goalLabel(), 'button_url' => '#contacto']],
            ],
            'about' => [
                ['type' => 'hero', 'content' => ['title' => 'Sobre nós', 'subtitle' => $this->description ?: 'Conhece melhor a nossa história e a forma como trabalhamos.', 'button_label' => 'Contactar', 'button_url' => '#contacto']],
                ['type' => 'text', 'content' => ['title' => 'A nossa história', 'body' => $this->additionalInfo ?: 'Conta aqui a história, os valores e a experiência do teu negócio.']],
            ],
            'services' => [
                ['type' => 'hero', 'content' => ['title' => 'Serviços', 'subtitle' => 'Descobre como podemos ajudar.', 'button_label' => 'Pedir informações', 'button_url' => '#contacto']],
                ['type' => 'feature_grid', 'content' => ['title' => 'O que fazemos', 'items' => [
                    ['title' => 'Serviço principal', 'description' => 'Explica o serviço e o benefício para o cliente.'],
                    ['title' => 'Serviço adicional', 'description' => 'Apresenta outra solução que disponibilizas.'],
                    ['title' => 'Acompanhamento', 'description' => 'Mostra como acompanhas os teus clientes.'],
                ]]],
            ],
            'products' => [
                ['type' => 'hero', 'content' => ['title' => 'Os nossos produtos', 'subtitle' => 'Explora o catálogo e encontra o que procuras.', 'button_label' => 'Ver produtos', 'button_url' => '#produtos']],
                ['type' => 'product_grid', 'content' => ['title' => 'Produtos', 'description' => 'Produtos disponíveis no teu catálogo.', 'limit' => 6]],
            ],
            'gallery' => [
                ['type' => 'hero', 'content' => ['title' => 'Galeria', 'subtitle' => 'Conhece melhor o nosso trabalho através das imagens.', 'button_label' => 'Contactar', 'button_url' => '#contacto']],
                ['type' => 'gallery', 'content' => ['items' => [['url' => '', 'alt' => '', 'caption' => 'Adiciona a primeira imagem.']]]],
            ],
            'pricing' => [
                ['type' => 'hero', 'content' => ['title' => 'Planos e preços', 'subtitle' => 'Escolhe a opção mais adequada às tuas necessidades.', 'button_label' => 'Contactar', 'button_url' => '#contacto']],
                ['type' => 'pricing', 'content' => ['title' => 'Escolhe o teu plano', 'items' => [
                    ['name' => 'Essencial', 'price' => '49 €', 'description' => 'Para começar.', 'features' => ['Funcionalidade 1', 'Funcionalidade 2'], 'button_label' => 'Escolher', 'button_url' => '#'],
                    ['name' => 'Profissional', 'price' => '99 €', 'description' => 'Para quem precisa de mais.', 'features' => ['Funcionalidade 1', 'Funcionalidade 2', 'Funcionalidade 3'], 'button_label' => 'Escolher', 'button_url' => '#'],
                ]]],
            ],
            'faq' => [
                ['type' => 'hero', 'content' => ['title' => 'Perguntas frequentes', 'subtitle' => 'Encontra rapidamente as respostas às dúvidas mais comuns.', 'button_label' => 'Contactar', 'button_url' => '#contacto']],
                ['type' => 'faq', 'content' => ['title' => 'Perguntas frequentes', 'items' => [
                    ['question' => 'Como funciona?', 'answer' => 'Explica aqui como funciona o teu serviço.'],
                    ['question' => 'Como posso entrar em contacto?', 'answer' => 'Adiciona aqui os teus canais de contacto.'],
                ]]],
            ],
            'contact' => [
                ['type' => 'hero', 'content' => ['title' => 'Contacta-nos', 'subtitle' => 'Estamos disponíveis para esclarecer dúvidas e ajudar-te.', 'button_label' => 'Enviar mensagem', 'button_url' => '#formulario']],
                ['type' => 'contact_form', 'content' => ['title' => 'Entra em contacto', 'description' => 'Envia-nos uma mensagem e responderemos assim que possível.', 'button_label' => 'Enviar mensagem']],
                ['type' => 'map', 'content' => ['address' => '', 'embed_url' => '']],
            ],
            default => [
                ['type' => 'hero', 'content' => ['title' => $name, 'subtitle' => $this->description ?: 'Apresenta o teu trabalho online.', 'button_label' => $this->goalLabel(), 'button_url' => '#contacto']],
                ['type' => 'cta', 'content' => ['title' => 'Fala connosco', 'description' => 'Estamos disponíveis para ajudar.', 'button_label' => $this->goalLabel(), 'button_url' => '#contacto']],
            ],
        };
    }

    private function goalLabel(): string
    {
        return match ($this->goal) {
            'buy' => 'Comprar',
            'quote' => 'Pedir orçamento',
            'booking' => 'Fazer marcação',
            'call' => 'Ligar',
            'message' => 'Enviar mensagem',
            default => 'Entrar em contacto',
        };
    }
}
