<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Site;
use App\Models\SitePage;
use App\Models\SiteSection;
use App\Models\SiteVersion;
use App\Services\WebsiteAiGenerator;
use App\Services\WebsitePublishingService;
use App\Services\WebsiteTemplateInstaller;
use App\Services\WebsiteVersionRestoreService;
use App\Services\WebsiteVersionService;
use App\Support\WebsiteBuilder\BuilderDocumentEditor;
use App\Support\WebsiteBuilder\ElementRegistry;
use App\Support\WebsiteBuilder\ResponsiveStyles;
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

    public ?string $selectedElementId = null;

    public string $device = 'desktop';

    public string $panel = 'pages';

    public string $inspectorTab = 'content'; // content | style | seo

    public string $statusMessage = 'Guardado';

    public bool $dirty = false;

    public bool $showAi = false;

    public bool $showTemplates = false;

    public bool $showPublish = false;

    public bool $showSettings = false;

    public bool $showVersions = false;

    public array $publishChecks = [];

    public array $versions = [];

    // --- Pesquisa / catálogo ---
    public string $sectionQuery = '';

    public string $elementQuery = '';

    // --- Wizard de IA ---
    public int $aiStep = 1;

    public string $aiBusinessName = '';

    public string $aiCategory = '';

    public string $aiTone = 'profissional';

    public string $aiDescription = '';

    public array $aiPagesSelected = ['home', 'about', 'services', 'contact'];

    public function mount(Site $site): void
    {
        abort_unless($site->isManageableBy(auth()->user()), 403);
        $this->site = $site;
        $this->theme = is_array($site->theme) && $site->theme !== [] ? $site->theme : $this->defaultTheme();
        $this->siteSettings = is_array($site->settings) ? $site->settings : [];
        $page = $site->pages()->orderByDesc('is_homepage')->orderBy('sort_order')->first();
        if (! $page) {
            $page = $this->createPageRecord('Home', 'home', true);
            $this->addStarterSection($page);
        }
        $this->loadPage($page->id);
        $this->loadVersions();
    }

    public function loadPage(int $id): void
    {
        $page = $this->site->pages()->with('sections')->findOrFail($id);
        $this->pageId = $page->id;
        $this->sections = $page->sections->map(fn (SiteSection $section): array => ['id' => $section->id, 'type' => $section->type, 'label' => $section->label, 'content' => $section->content ?: [], 'settings' => $section->settings ?: [], 'is_visible' => (bool) $section->is_visible])->values()->all();
        $this->pageSeo = $page->seo ?: [];
        $this->selectedSection = $this->sections !== [] ? 0 : null;
        $this->selectedElementId = null;
        $this->inspectorTab = 'content';
        $this->refreshPages();
        $this->dirty = false;
        $this->statusMessage = 'Guardado';
    }

    public function refreshPages(): void
    {
        $this->pages = $this->site->pages()->orderBy('sort_order')->get()->map(fn (SitePage $page): array => ['id' => $page->id, 'name' => $page->name, 'slug' => $page->slug, 'is_homepage' => (bool) $page->is_homepage, 'status' => $page->status])->all();
    }

    public function currentPage(): array
    {
        return collect($this->pages)->firstWhere('id', $this->pageId) ?? [];
    }

    public function createPage(): void
    {
        $slug = 'nova-pagina';
        $suffix = 2;
        while ($this->site->pages()->where('slug', $slug)->exists()) {
            $slug = 'nova-pagina-'.$suffix++;
        }
        $name = $suffix === 2 ? 'Nova página' : 'Nova página '.($suffix - 1);
        $page = $this->site->pages()->create(['name' => $name, 'slug' => $slug, 'status' => 'draft', 'is_homepage' => false, 'sort_order' => ((int) $this->site->pages()->max('sort_order')) + 1, 'seo' => []]);
        $this->addStarterSection($page);
        $this->refreshPages();
        $this->loadPage($page->id);
        $this->statusMessage = 'Nova página criada';
    }

    public function duplicatePage(): void
    {
        $source = $this->site->pages()->with('sections')->findOrFail($this->pageId);
        if ($this->dirty) {
            $this->save();
            $source->refresh()->load('sections');
        }
        $baseSlug = Str::slug($source->slug.'-copia');
        $slug = $baseSlug;
        $suffix = 2;
        while ($this->site->pages()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$suffix++;
        }
        $page = $this->site->pages()->create(['name' => $source->name.' (cópia)', 'slug' => $slug, 'status' => 'draft', 'is_homepage' => false, 'sort_order' => ((int) $this->site->pages()->max('sort_order')) + 1, 'seo' => $source->seo ?: []]);
        foreach ($source->sections as $section) {
            $page->sections()->create(['type' => $section->type, 'label' => $section->label, 'content' => $section->content ?: [], 'settings' => $section->settings ?: [], 'sort_order' => $section->sort_order, 'is_visible' => (bool) $section->is_visible]);
        }
        $this->refreshPages();
        $this->loadPage($page->id);
        $this->statusMessage = 'Página duplicada';
    }

    public function deletePage(): void
    {
        $page = $this->site->pages()->findOrFail($this->pageId);
        abort_if($this->site->pages()->count() <= 1, 422, 'O website precisa de pelo menos uma página.');
        $wasHomepage = (bool) $page->is_homepage;
        $page->delete();
        if ($wasHomepage) {
            $replacement = $this->site->pages()->orderBy('sort_order')->firstOrFail();
            $this->setHomepage($replacement->id);
        }
        $replacement = $this->site->pages()->where('is_homepage', true)->first() ?? $this->site->pages()->orderBy('sort_order')->firstOrFail();
        $this->refreshPages();
        $this->loadPage($replacement->id);
        $this->statusMessage = 'Página eliminada';
    }

    public function setHomepage(int $id): void
    {
        DB::transaction(function () use ($id): void {
            $page = $this->site->pages()->findOrFail($id);
            $this->site->pages()->update(['is_homepage' => false]);
            $page->update(['is_homepage' => true]);
        });
        $this->refreshPages();
        $this->statusMessage = 'Homepage actualizada';
    }

    public function updatePageName(string $name): void
    {
        $name = trim($name);
        abort_if($name === '', 422, 'O nome da página não pode ficar vazio.');
        $page = $this->site->pages()->findOrFail($this->pageId);
        $page->update(['name' => Str::limit($name, 120, '')]);
        $this->refreshPages();
        $this->statusMessage = 'Nome da página actualizado';
    }

    public function updatePageSeo(string $key, string $value): void
    {
        abort_unless(in_array($key, ['title', 'description', 'canonical', 'og_image'], true), 422);
        $value = trim($value);
        if (in_array($key, ['canonical', 'og_image'], true) && $value !== '' && ! preg_match('#^(https://|/)#i', $value)) {
            abort(422, 'A ligação SEO deve começar por https:// ou /.');
        }
        $this->pageSeo[$key] = Str::limit($value, $key === 'description' ? 320 : 500, '');
        $this->dirty = true;
    }

    public function reorderPages(array $orderedIds): void
    {
        $allowedIds = $this->site->pages()->pluck('id')->map(fn ($id): int => (int) $id)->all();
        $orderedIds = array_values(array_filter(array_map('intval', $orderedIds), fn (int $id): bool => in_array($id, $allowedIds, true)));
        abort_unless(count($orderedIds) === count($allowedIds) && count(array_unique($orderedIds)) === count($allowedIds), 422);
        DB::transaction(function () use ($orderedIds): void {
            foreach ($orderedIds as $index => $id) {
                $this->site->pages()->whereKey($id)->update(['sort_order' => $index]);
            }
        });
        $this->refreshPages();
    }

    public function loadVersions(): void
    {
        $this->versions = $this->site->versions()->with('creator')->latest('version_number')->limit(30)->get()->map(fn (SiteVersion $version): array => ['id' => $version->id, 'version_number' => $version->version_number, 'label' => $version->label, 'created_at' => optional($version->created_at)->format('d/m/Y H:i'), 'created_by' => $version->creator?->name])->all();
    }

    public function save(): void
    {
        $page = $this->site->pages()->findOrFail($this->pageId);
        if ($this->dirty) {
            app(WebsiteVersionService::class)->create($this->site->fresh(), auth()->id(), 'Backup antes de guardar');
        }
        DB::transaction(function () use ($page): void {
            $this->site->update(['theme' => $this->theme, 'settings' => $this->siteSettings]);
            $page->update(['seo' => $this->pageSeo]);
            $existingIds = [];
            foreach ($this->sections as $index => $data) {
                $payload = ['type' => Str::limit((string) ($data['type'] ?? 'text'), 80, ''), 'label' => Str::limit((string) ($data['label'] ?? 'Secção'), 120, ''), 'content' => is_array($data['content'] ?? null) ? $data['content'] : [], 'settings' => is_array($data['settings'] ?? null) ? $data['settings'] : [], 'sort_order' => $index, 'is_visible' => (bool) ($data['is_visible'] ?? true)];
                $section = ! empty($data['id']) ? $page->sections()->findOrFail((int) $data['id']) : $page->sections()->create($payload);
                if (! empty($data['id'])) {
                    $section->update($payload);
                }
                $existingIds[] = $section->id;
                $this->sections[$index]['id'] = $section->id;
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
        app(WebsiteVersionService::class)->create($this->site->fresh(), auth()->id(), Str::limit(trim($label) ?: 'Versão guardada', 120, ''));
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
        $this->selectedElementId = $this->lastElementId($document);
        $this->dirty = true;
        $this->statusMessage = 'Elemento adicionado — por guardar';
    }

    public function duplicateElement(string $elementId): void
    {
        abort_unless($this->selectedSection !== null && isset($this->sections[$this->selectedSection]), 422);
        $index = $this->selectedSection;
        $document = SiteSectionDocument::fromSection($this->sectionModel($index));
        $element = $this->findElement($document['nodes'] ?? [], $elementId);
        abort_unless(is_array($element), 404);
        $containerId = $document['nodes'][0]['id'] ?? null;
        abort_unless(is_string($containerId), 422);

        // Nota: a API do BuilderDocumentEditor não expõe "inserir a seguir a X",
        // por isso o duplicado é acrescentado ao fundo da secção com o mesmo conteúdo.
        $document = BuilderDocumentEditor::addElement($document, $containerId, (string) $element['type']);
        $newId = $this->lastElementId($document);
        abort_unless($newId !== null, 422);
        $document = BuilderDocumentEditor::updateElement($document, $newId, is_array($element['content'] ?? null) ? $element['content'] : [], is_array($element['settings'] ?? null) ? $element['settings'] : []);
        $this->applyDocumentToSection($index, $document);
        $this->selectedElementId = $newId;
        $this->dirty = true;
        $this->statusMessage = 'Elemento duplicado — por guardar';
    }

    public function selectElement(string $elementId): void
    {
        abort_unless($this->selectedSection !== null && isset($this->sections[$this->selectedSection]), 422);
        $document = SiteSectionDocument::fromSection($this->sectionModel($this->selectedSection));
        abort_unless($this->findElement($document['nodes'] ?? [], $elementId) !== null, 404);
        $this->selectedElementId = $elementId;
        $this->inspectorTab = 'content';
    }

    public function updateSelectedElement(string $field, string $value): void
    {
        // Um blur de contenteditable pode chegar depois de uma actualização/rerender do Livewire.
        // Se a selecção já não existir, ignoramos esse update obsoleto em vez de devolver 422.
        if ($this->selectedSection === null || $this->selectedElementId === null) {
            return;
        }

        abort_unless(in_array($field, ['text', 'label', 'url', 'alt', 'caption', 'title', 'author', 'button_label', 'button_url'], true), 422);
        $index = $this->selectedSection;
        $document = SiteSectionDocument::fromSection($this->sectionModel($index));
        $element = $this->findElement($document['nodes'] ?? [], $this->selectedElementId);
        abort_unless(is_array($element), 404);
        $content = is_array($element['content'] ?? null) ? $element['content'] : [];
        $content[$field] = Str::limit($value, 2000, '');
        $content = $this->sanitizeElementContent((string) $element['type'], $content);
        $document = BuilderDocumentEditor::updateElement($document, $this->selectedElementId, $content, null);
        $this->applyDocumentToSection($index, $document);
        $this->dirty = true;
        $this->statusMessage = 'Elemento alterado — por guardar';
    }

    public function updateSelectedElementSetting(string $key, string $value): void
    {
        abort_unless($this->selectedSection !== null && $this->selectedElementId !== null, 422);
        abort_unless(in_array($key, ['font_size', 'padding', 'margin', 'align', 'width', 'visibility'], true), 422);
        $index = $this->selectedSection;
        $document = SiteSectionDocument::fromSection($this->sectionModel($index));