<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Site;
use App\Models\SitePage;
use App\Models\SiteSection;
use App\Services\WebsiteAiGenerator;
use App\Services\WebsitePublishingService;
use App\Support\WebsiteBuilder\WebsiteTemplates;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.builder')]
#[Title('Website Studio')]
class BuilderStudio extends Component
{
    public Site $site;
    public ?int $pageId = null;
    public array $pages = [];
    public array $sections = [];
    public array $theme = [];
    public array $siteSettings = [];
    public array $pageSeo = [];
    public ?int $selectedSection = null;
    public string $device = 'desktop';
    public string $panel = 'pages';
    public string $aiBrief = '';
    public string $statusMessage = 'Guardado';
    public bool $dirty = false;
    public bool $showAi = false;
    public bool $showTemplates = false;
    public bool $showPublish = false;
    public bool $showSettings = false;
    public array $publishChecks = [];

    public function mount(Site $site): void
    {
        abort_unless($site->isManageableBy(auth()->user()), 403);
        $this->site = $site;
        $this->theme = $site->theme ?: $this->defaultTheme();
        $this->siteSettings = $site->settings ?: [];
        $page = $site->pages()->orderByDesc('is_homepage')->orderBy('sort_order')->first();
        if (! $page) {
            $page = $this->createPageRecord('Home', 'home', true);
        }
        $this->loadPage($page->id);
    }

    public function loadPage(int $id): void
    {
        $page = $this->site->pages()->with('sections')->findOrFail($id);
        $this->pageId = $page->id;
        $this->sections = $page->sections->map(fn (SiteSection $section): array => [
            'id' => $section->id,
            'type' => $section->type,
            'label' => $section->label,
            'content' => $section->content ?: [],
            'settings' => $section->settings ?: [],
            'is_visible' => (bool) $section->is_visible,
        ])->values()->all();
        $this->pageSeo = $page->seo ?: [];
        $this->selectedSection = $this->sections !== [] ? 0 : null;
        $this->refreshPages();
        $this->dirty = false;
        $this->statusMessage = 'Guardado';
    }

    public function refreshPages(): void
    {
        $this->pages = $this->site->pages()->orderBy('sort_order')->get()->map(fn (SitePage $page): array => [
            'id' => $page->id,
            'name' => $page->name,
            'slug' => $page->slug,
            'is_homepage' => (bool) $page->is_homepage,
            'status' => $page->status,
        ])->all();
    }

    public function updated(string $property): void
    {
        if (str_starts_with($property, 'sections.') || str_starts_with($property, 'theme.') || str_starts_with($property, 'pageSeo.') || str_starts_with($property, 'siteSettings.')) {
            $this->dirty = true;
            $this->statusMessage = 'Alterações por guardar';
        }
    }

    public function selectSection(int $index): void
    {
        abort_unless(isset($this->sections[$index]), 404);
        $this->selectedSection = $index;
        $this->panel = 'inspector';
    }

    public function save(): void
    {
        $page = $this->site->pages()->findOrFail($this->pageId);
        DB::transaction(function () use ($page): void {
            $this->site->update(['theme' => $this->theme, 'settings' => $this->siteSettings]);
            $page->update(['seo' => $this->pageSeo]);
            $existingIds = [];
            foreach ($this->sections as $index => $data) {
                $payload = [
                    'type' => $data['type'],
                    'label' => Str::limit((string) ($data['label'] ?? Str::headline($data['type'])), 120, ''),
                    'content' => is_array($data['content'] ?? null) ? $data['content'] : [],
                    'settings' => is_array($data['settings'] ?? null) ? $data['settings'] : [],
                    'sort_order' => $index,
                    'is_visible' => (bool) ($data['is_visible'] ?? true),
                ];
                if (! empty($data['id'])) {
                    $section = $page->sections()->findOrFail((int) $data['id']);
                    $section->update($payload);
                    $existingIds[] = $section->id;
                } else {
                    $section = $page->sections()->create($payload);
                    $existingIds[] = $section->id;
                    $this->sections[$index]['id'] = $section->id;
                }
            }
            $page->sections()->whereNotIn('id', $existingIds ?: [0])->delete();
        });
        $this->dirty = false;
        $this->statusMessage = 'Guardado agora';
        $this->refreshPages();
    }

    public function addSection(string $type): void
    {
        $allowed = ['hero', 'text', 'image', 'button', 'feature_grid', 'card', 'testimonials', 'faq', 'gallery', 'contact_form', 'product_grid', 'product_card', 'pricing', 'blog_posts', 'social_links', 'video', 'map', 'newsletter', 'cta'];
        abort_unless(in_array($type, $allowed, true), 422);
        $this->sections[] = [
            'id' => null,
            'type' => $type,
            'label' => Str::headline($type),
            'content' => $this->defaultContent($type),
            'settings' => ['background' => 'transparent', 'padding' => 'lg', 'align' => 'left'],
            'is_visible' => true,
        ];
        $this->selectedSection = count($this->sections) - 1;
        $this->dirty = true;
        $this->panel = 'inspector';
    }

    public function duplicateSection(int $index): void
    {
        abort_unless(isset($this->sections[$index]), 404);
        $copy = $this->sections[$index];
        $copy['id'] = null;
        $copy['label'] = ($copy['label'] ?: 'Secção').' (cópia)';
        array_splice($this->sections, $index + 1, 0, [$copy]);
        $this->selectedSection = $index + 1;
        $this->dirty = true;
    }

    public function deleteSection(int $index): void
    {
        abort_unless(isset($this->sections[$index]), 404);
        array_splice($this->sections, $index, 1);
        $this->selectedSection = $this->sections !== [] ? min($index, count($this->sections) - 1) : null;
        $this->dirty = true;
    }

    public function moveSection(int $index, string $direction): void
    {
        abort_unless(in_array($direction, ['up', 'down'], true), 422);
        $target = $direction === 'up' ? $index - 1 : $index + 1;
        if (! isset($this->sections[$index], $this->sections[$target])) {
            return;
        }
        [$this->sections[$index], $this->sections[$target]] = [$this->sections[$target], $this->sections[$index]];
        $this->selectedSection = $target;
        $this->dirty = true;
    }

    public function toggleSection(int $index): void
    {
        abort_unless(isset($this->sections[$index]), 404);
        $this->sections[$index]['is_visible'] = ! (bool) $this->sections[$index]['is_visible'];
        $this->dirty = true;
    }

    public function createPage(string $name = 'Nova página'): void
    {
        $name = trim(strip_tags($name));
        abort_if($name === '', 422);
        $slug = Str::slug($name) ?: 'nova-pagina';
        $base = $slug;
        $number = 2;
        while ($this->site->pages()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.($number++);
        }
        $page = $this->site->pages()->create(['name' => Str::limit($name, 120, ''), 'slug' => $slug, 'status' => 'draft', 'is_homepage' => false, 'sort_order' => ((int) $this->site->pages()->max('sort_order')) + 1, 'seo' => []]);
        $this->loadPage($page->id);
    }

    public function duplicatePage(): void
    {
        $source = $this->site->pages()->with('sections')->findOrFail($this->pageId);
        $name = $source->name.' (cópia)';
        $slug = $source->slug.'-copia';
        $base = $slug;
        $number = 2;
        while ($this->site->pages()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.($number++);
        }
        $page = DB::transaction(function () use ($source, $name, $slug): SitePage {
            $newPage = $this->site->pages()->create(['name' => $name, 'slug' => $slug, 'status' => 'draft', 'is_homepage' => false, 'sort_order' => ((int) $this->site->pages()->max('sort_order')) + 1, 'seo' => $source->seo]);
            foreach ($source->sections as $section) {
                $newPage->sections()->create(['type' => $section->type, 'label' => $section->label, 'content' => $section->content, 'settings' => $section->settings, 'sort_order' => $section->sort_order, 'is_visible' => $section->is_visible]);
            }
            return $newPage;
        });
        $this->loadPage($page->id);
    }

    public function deletePage(): void
    {
        $page = $this->site->pages()->findOrFail($this->pageId);
        abort_if($page->is_homepage, 422, 'A homepage não pode ser eliminada.');
        $page->delete();
        $next = $this->site->pages()->orderBy('sort_order')->firstOrFail();
        $this->loadPage($next->id);
    }

    public function setHomepage(int $id): void
    {
        $page = $this->site->pages()->findOrFail($id);
        DB::transaction(function () use ($page): void {
            $this->site->pages()->update(['is_homepage' => false]);
            $page->update(['is_homepage' => true]);
        });
        $this->refreshPages();
    }

    public function reorderPages(array $ids): void
    {
        $ids = array_values(array_map('intval', $ids));
        $pages = $this->site->pages()->whereIn('id', $ids)->get()->keyBy('id');
        abort_unless($pages->count() === count($ids), 422);
        foreach ($ids as $index => $id) {
            $pages[$id]->update(['sort_order' => $index]);
        }
        $this->refreshPages();
    }

    public function updatePageName(string $name): void
    {
        $page = $this->site->pages()->findOrFail($this->pageId);
        $name = trim(strip_tags($name));
        abort_if($name === '', 422);
        $page->update(['name' => Str::limit($name, 120, '')]);
        $this->refreshPages();
    }

    public function applyTemplate(string $category): void
    {
        $templates = WebsiteTemplates::all();
        abort_unless(isset($templates[$category]), 422);
        $definition = $templates[$category];
        DB::transaction(function () use ($definition): void {
            $this->site->pages()->each(fn (SitePage $page) => $page->delete());
            foreach ($definition['pages'] as $index => $pageDefinition) {
                $page = $this->site->pages()->create(['name' => $pageDefinition['name'], 'slug' => $pageDefinition['slug'], 'status' => 'draft', 'is_homepage' => $index === 0, 'sort_order' => $index, 'seo' => ['title' => $pageDefinition['name'].' — '.$this->site->name]]);
                foreach ($pageDefinition['sections'] as $sortOrder => $type) {
                    $page->sections()->create(['type' => $type, 'label' => Str::headline($type), 'content' => $this->defaultContent($type), 'settings' => ['background' => 'transparent', 'padding' => 'lg', 'align' => 'left'], 'sort_order' => $sortOrder, 'is_visible' => true]);
                }
            }
        });
        $page = $this->site->pages()->where('is_homepage', true)->firstOrFail();
        $this->loadPage($page->id);
        $this->showTemplates = false;
    }

    public function generateWithAi(WebsiteAiGenerator $generator): void
    {
        $brief = trim($this->aiBrief);
        abort_if($brief === '', 422, 'Escreve primeiro o que queres criar.');
        $result = $generator->generate(['description' => $brief, 'business_name' => $this->site->name, 'pages' => ['home', 'about', 'services', 'contact']]);
        $pages = $result['pages'] ?? [];
        abort_if(! is_array($pages) || $pages === [], 422, 'Não foi possível criar uma estrutura válida.');
        DB::transaction(function () use ($pages): void {
            $this->site->pages()->each(fn (SitePage $page) => $page->delete());
            $order = 0;
            foreach ($pages as $slug => $sections) {
                if (! is_array($sections)) {
                    continue;
                }
                $name = match ($slug) {
                    'home' => 'Home',
                    'about' => 'Sobre nós',
                    'services' => 'Serviços',
                    'contact' => 'Contactos',
                    default => Str::headline((string) $slug),
                };
                $page = $this->site->pages()->create(['name' => $name, 'slug' => Str::slug((string) $slug), 'status' => 'draft', 'is_homepage' => $order === 0, 'sort_order' => $order, 'seo' => ['title' => $name.' — '.$this->site->name]]);
                foreach ($sections as $sortOrder => $section) {
                    if (! is_array($section) || ! is_string($section['type'] ?? null)) {
                        continue;
                    }
                    $page->sections()->create(['type' => $section['type'], 'label' => $section['label'] ?? Str::headline($section['type']), 'content' => is_array($section['content'] ?? null) ? $section['content'] : [], 'settings' => ['background' => 'transparent', 'padding' => 'lg', 'align' => 'left'], 'sort_order' => (int) $sortOrder, 'is_visible' => true]);
                }
                $order++;
            }
        });
        $page = $this->site->pages()->where('is_homepage', true)->firstOrFail();
        $this->loadPage($page->id);
        $this->showAi = false;
        $this->aiBrief = '';
    }

    public function runPublishChecks(WebsitePublishingService $publishing): void
    {
        $this->publishChecks = $publishing->validate($this->site);
        $this->showPublish = true;
    }

    public function publish(WebsitePublishingService $publishing): void
    {
        $this->save();
        $publishing->publish($this->site->fresh());
        $this->site->refresh();
        $this->showPublish = false;
        $this->statusMessage = 'Website online';
    }

    public function unpublish(WebsitePublishingService $publishing): void
    {
        $publishing->unpublish($this->site);
        $this->site->refresh();
        $this->statusMessage = 'Website em rascunho';
    }

    public function openPreview(): void
    {
        $this->save();
        $this->dispatch('open-builder-preview', url: route('site.public', ['site' => $this->site->slug, 'pageSlug' => $this->currentPage()['slug'] ?? null, 'preview' => 1]));
    }

    public function updateTheme(string $key, string $value): void
    {
        abort_unless(in_array($key, ['primary', 'secondary', 'background', 'text', 'font', 'button_radius', 'section_radius', 'shadow'], true), 422);
        $this->theme[$key] = trim(strip_tags($value));
        $this->dirty = true;
        $this->statusMessage = 'Design por guardar';
    }

    public function currentPage(): array
    {
        return collect($this->pages)->firstWhere('id', $this->pageId) ?? [];
    }

    private function createPageRecord(string $name, string $slug, bool $home = false): SitePage
    {
        return DB::transaction(function () use ($name, $slug, $home): SitePage {
            $page = $this->site->pages()->create(['name' => $name, 'slug' => $slug, 'status' => 'draft', 'is_homepage' => $home, 'sort_order' => 0, 'seo' => ['title' => $name.' — '.$this->site->name]]);
            foreach (['hero', 'feature_grid', 'cta'] as $index => $type) {
                $page->sections()->create(['type' => $type, 'label' => Str::headline($type), 'content' => $this->defaultContent($type), 'settings' => ['background' => 'transparent', 'padding' => 'lg', 'align' => 'left'], 'sort_order' => $index, 'is_visible' => true]);
            }
            return $page;
        });
    }

    private function defaultTheme(): array
    {
        return ['primary' => '#2563eb', 'secondary' => '#0f172a', 'background' => '#ffffff', 'text' => '#0f172a', 'font' => 'Inter', 'button_radius' => 'md', 'section_radius' => 'lg', 'shadow' => 'soft'];
    }

    private function defaultContent(string $type): array
    {
        return match ($type) {
            'hero' => ['title' => 'O teu negócio, apresentado de forma profissional.', 'subtitle' => 'Explica claramente o que ofereces e porque devem escolher-te.', 'button_label' => 'Saber mais', 'button_url' => '#contacto'],
            'text' => ['title' => 'Uma mensagem importante', 'body' => 'Escreve aqui a informação principal desta secção.'],
            'image' => ['url' => '', 'alt' => '', 'caption' => ''],
            'button' => ['label' => 'Saber mais', 'url' => '#'],
            'feature_grid' => ['title' => 'Porque escolher-nos', 'items' => [['title' => 'Qualidade', 'description' => 'Apresenta um benefício.'], ['title' => 'Confiança', 'description' => 'Mostra porque és uma boa escolha.'], ['title' => 'Experiência', 'description' => 'Explica o teu diferencial.']]],
            'card' => ['title' => 'Serviço em destaque', 'description' => 'Descrição curta e clara.', 'button_label' => 'Saber mais', 'button_url' => '#'],
            'testimonials' => ['title' => 'O que dizem os clientes', 'items' => [['name' => 'Cliente', 'role' => 'Cliente', 'quote' => 'Adiciona aqui um testemunho real.']]],
            'faq' => ['title' => 'Perguntas frequentes', 'items' => [['question' => 'Como funciona?', 'answer' => 'Explica aqui.']]],
            'gallery' => ['title' => 'Galeria', 'items' => []],
            'contact_form' => ['title' => 'Entra em contacto', 'description' => 'Envia-nos uma mensagem.', 'button_label' => 'Enviar mensagem'],
            'product_grid' => ['title' => 'Produtos', 'description' => 'Os teus produtos aparecem aqui.', 'limit' => 6],
            'product_card' => ['title' => 'Produto', 'description' => 'Descrição do produto.', 'price' => ''],
            'pricing' => ['title' => 'Planos', 'items' => [['name' => 'Essencial', 'price' => '49 €', 'description' => 'Plano inicial.', 'features' => ['Funcionalidade 1', 'Funcionalidade 2'], 'button_label' => 'Escolher', 'button_url' => '#contacto']]],
            'blog_posts' => ['title' => 'Artigos', 'items' => []],
            'social_links' => ['title' => 'Segue-nos', 'items' => []],
            'video' => ['title' => 'Vídeo', 'url' => ''],
            'map' => ['title' => 'Onde estamos', 'address' => ''],
            'newsletter' => ['title' => 'Recebe novidades', 'description' => 'Subscreve a nossa newsletter.', 'button_label' => 'Subscrever'],
            'cta' => ['title' => 'Vamos falar?', 'description' => 'Entra em contacto para saber mais.', 'button_label' => 'Contactar', 'button_url' => '#contacto'],
            default => [],
        };
    }

    public function render()
    {
        return view('livewire.builder-studio', ['templates' => WebsiteTemplates::all()]);
    }
}
