<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Site;
use App\Models\SitePage;
use App\Models\SiteSection;
use App\Models\SiteVersion;
use App\Services\WebsiteAiGenerator;
use App\Services\WebsitePublishingService;
use App\Services\WebsiteVersionRestoreService;
use App\Services\WebsiteVersionService;
use App\Support\WebsiteBuilder\BuilderDocumentEditor;
use App\Support\WebsiteBuilder\ElementRegistry;
use App\Support\WebsiteBuilder\SiteSectionDocument;
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
    public bool $showVersions = false;
    public array $publishChecks = [];
    public array $versions = [];

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
        $this->loadVersions();
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

    public function loadVersions(): void
    {
        $this->versions = $this->site->versions()->with('creator')->limit(30)->get()->map(fn (SiteVersion $version): array => [
            'id' => $version->id,
            'version_number' => $version->version_number,
            'label' => $version->label,
            'created_at' => optional($version->created_at)->format('d/m/Y H:i'),
            'created_by' => $version->creator?->name,
        ])->all();
    }

    public function save(): void
    {
        $page = $this->site->pages()->findOrFail($this->pageId);

        if ($this->dirty) {
            app(WebsiteVersionService::class)->create($this->site, auth()->id(), 'Antes de guardar alterações');
        }

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
                    $section = $page->sections()->findOrFail($data['id']);
                    $section->update($payload);
                    $existingIds[] = $section->id;
                    $this->sections[$index]['id'] = $section->id;
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
        $this->loadVersions();
    }

    public function saveVersion(string $label = 'Versão guardada'): void
    {
        if ($this->dirty) {
            $this->save();
        }

        app(WebsiteVersionService::class)->create($this->site->fresh(), auth()->id(), $label);
        $this->loadVersions();
        $this->statusMessage = 'Versão guardada';
    }

    public function restoreVersion(int $versionId): void
    {
        $version = $this->site->versions()->findOrFail($versionId);
        app(WebsiteVersionRestoreService::class)->restore($this->site, $version);
        $this->site->refresh();
        $page = $this->site->pages()->where('is_homepage', true)->first() ?? $this->site->pages()->firstOrFail();
        $this->loadPage($page->id);
        $this->loadVersions();
        $this->showVersions = false;
        $this->statusMessage = 'Versão restaurada';
    }

    public function addElement(string $type): void
    {
        abort_unless($this->selectedSection !== null && isset($this->sections[$this->selectedSection]), 422);
        abort_unless(ElementRegistry::has($type), 422);
        $index = $this->selectedSection;
        $document = SiteSectionDocument::fromSection($this->sectionModel($index));
        $containerId = $document['nodes'][0]['id'] ?? null;
        abort_unless(is_string($containerId), 422);
        $document = BuilderDocumentEditor::addElement($document, $containerId, $type);
        $this->applyDocumentToSection($index, $document);
        $this->dirty = true;
        $this->statusMessage = 'Elemento adicionado — por guardar';
    }

    public function removeElement(string $elementId): void
    {
        abort_unless($this->selectedSection !== null && isset($this->sections[$this->selectedSection]), 422);
        $index = $this->selectedSection;
        $document = SiteSectionDocument::fromSection($this->sectionModel($index));
        $document = BuilderDocumentEditor::removeElement($document, $elementId);
        $this->applyDocumentToSection($index, $document);
        $this->dirty = true;
    }

    public function moveElement(string $elementId, int $offset): void
    {
        abort_unless($this->selectedSection !== null && isset($this->sections[$this->selectedSection]), 422);
        $index = $this->selectedSection;
        $document = SiteSectionDocument::fromSection($this->sectionModel($index));
        $document = BuilderDocumentEditor::moveElement($document, $elementId, $offset);
        $this->applyDocumentToSection($index, $document);
        $this->dirty = true;
    }

    public function addSection(string $type): void
    {
        $allowed = ['hero', 'text', 'image', 'button', 'feature_grid', 'card', 'testimonials', 'faq', 'gallery', 'contact_form', 'product_grid', 'product_card', 'pricing', 'blog_posts', 'social_links', 'video', 'map', 'newsletter', 'cta'];
        abort_unless(in_array($type, $allowed, true), 422);
        $this->sections[] = ['id' => null, 'type' => $type, 'label' => Str::headline($type), 'content' => $this->defaultContent($type), 'settings' => ['background' => 'transparent', 'padding' => 'lg', 'align' => 'left'], 'is_visible' => true];
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

    private function sectionModel(int $index): SiteSection
    {
        $id = $this->sections[$index]['id'] ?? null;
        abort_unless(is_numeric($id), 422);
        return $this->site->pages()->findOrFail($this->pageId)->sections()->findOrFail((int) $id);
    }

    private function applyDocumentToSection(int $index, array $document): void
    {
        $data = SiteSectionDocument::toSectionData($document, $this->sections[$index]['content'] ?? [], $this->sections[$index]['settings'] ?? []);
        $this->sections[$index]['content'] = $data['content'];
        $this->sections[$index]['settings'] = $data['settings'];
    }

    public function updateElement(string $elementId, array $content, array $settings = []): void
    {
        abort_unless($this->selectedSection !== null && isset($this->sections[$this->selectedSection]), 422);
        $index = $this->selectedSection;
        $document = SiteSectionDocument::fromSection($this->sectionModel($index));
        $document = BuilderDocumentEditor::updateElement($document, $elementId, $content, $settings);
        $this->applyDocumentToSection($index, $document);
        $this->dirty = true;
        $this->statusMessage = 'Alteração por guardar';
    }

    public function selectSection(int $index): void
    {
        abort_unless(isset($this->sections[$index]), 404);
        $this->selectedSection = $index;
        $this->panel = 'inspector';
    }

    public function updatedDevice(string $device): void
    {
        if (! in_array($device, ['desktop', 'tablet', 'mobile'], true)) {
            $this->device = 'desktop';
        }
    }

    public function runPublishChecks(): void
    {
        $this->publishChecks = app(WebsitePublishingService::class)->validate($this->site->fresh());
        $this->showPublish = true;
    }

    public function publish(): void
    {
        $this->save();
        $this->site->refresh();
        $this->publishChecks = app(WebsitePublishingService::class)->validate($this->site);

        if (collect($this->publishChecks)->contains(fn (array $check): bool => ($check['level'] ?? '') === 'error')) {
            $this->showPublish = true;
            return;
        }

        app(WebsitePublishingService::class)->publish($this->site, auth()->id());
        $this->site->refresh();
        $this->showPublish = false;
        $this->statusMessage = 'Website publicado';
    }

    public function unpublish(): void
    {
        app(WebsitePublishingService::class)->unpublish($this->site);
        $this->site->refresh();
        $this->statusMessage = 'Website retirado do ar';
    }

    public function openPreview(): void
    {
        $page = $this->site->pages()->findOrFail($this->pageId);
        $this->dispatch('open-builder-preview', url: route('site.public', ['site' => $this->site->slug, 'pageSlug' => $page->slug, 'preview' => 1]));
    }

    public function updateTheme(string $key, string $value): void
    {
        $allowed = ['primary', 'secondary', 'background', 'text', 'radius', 'font_heading', 'font_body'];
        abort_unless(in_array($key, $allowed, true), 422);
        $this->theme[$key] = Str::limit($value, 100, '');
        $this->dirty = true;
    }

    public function applyTemplate(string $template): void
    {
        $definition = WebsiteTemplates::get($template);
        $this->sections = [];
        foreach ($definition['pages'][0]['sections'] ?? [] as $section) {
            $this->sections[] = ['id' => null, 'type' => $section['type'], 'label' => $section['label'], 'content' => $this->defaultContent($section['type']), 'settings' => ['background' => 'transparent', 'padding' => 'lg', 'align' => 'left'], 'is_visible' => true];
        }
        $this->selectedSection = $this->sections !== [] ? 0 : null;
        $this->dirty = true;
        $this->showTemplates = false;
    }

    public function generateWithAi(): void
    {
        $brief = trim($this->aiBrief);
        abort_if($brief === '', 422, 'Descreve o website que queres criar.');
        $result = app(WebsiteAiGenerator::class)->generate(['prompt' => $brief, 'site' => $this->site->name, 'category' => $this->site->type]);
        foreach ($result['pages'] ?? [] as $pageData) {
            $page = $this->site->pages()->updateOrCreate(['slug' => Str::slug($pageData['slug'] ?? $pageData['name'] ?? 'pagina')], ['name' => $pageData['name'] ?? 'Página', 'seo' => $pageData['seo'] ?? [], 'sort_order' => $this->site->pages()->count()]);
            if ($page->id === $this->pageId) {
                $this->loadPage($page->id);
            }
        }
        $this->dirty = true;
        $this->showAi = false;
        $this->statusMessage = 'Estrutura gerada por IA — por rever e guardar';
    }

    private function createPageRecord(string $name, string $slug, bool $homepage = false): SitePage
    {
        return $this->site->pages()->create(['name' => $name, 'slug' => $slug, 'status' => 'draft', 'is_homepage' => $homepage, 'sort_order' => 0, 'seo' => []]);
    }

    private function defaultTheme(): array
    {
        return ['primary' => '#635bff', 'secondary' => '#111827', 'background' => '#ffffff', 'text' => '#111827', 'radius' => '1rem', 'font_heading' => 'Inter', 'font_body' => 'Inter'];
    }

    private function defaultContent(string $type): array
    {
        return match ($type) {
            'hero' => ['title' => 'O teu novo website', 'subtitle' => 'Uma presença online profissional.', 'button_label' => 'Saber mais', 'button_url' => '#'],
            'text' => ['title' => 'Sobre nós', 'body' => 'Escreve aqui o conteúdo da tua secção.'],
            'image' => ['url' => '', 'alt' => '', 'caption' => ''],
            'button' => ['label' => 'Saber mais', 'url' => '#'],
            default => ['title' => Str::headline($type)],
        };
    }

    public function render()
    {
        return view('livewire.builder-studio');
    }
}
