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
        $this->theme = is_array($site->theme) && $site->theme !== [] ? $site->theme : $this->defaultTheme();
        $this->siteSettings = is_array($site->settings) ? $site->settings : [];
        $page = $site->pages()->orderByDesc('is_homepage')->orderBy('sort_order')->first();
        if (! $page) {
            $page = $this->createPageRecord('Home', 'home', true);
            $this->addStarterSection($page);
        } $this->loadPage($page->id);
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
        } $name = $suffix === 2 ? 'Nova página' : 'Nova página '.($suffix - 1);
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
        } $baseSlug = Str::slug($source->slug.'-copia');
        $slug = $baseSlug;
        $suffix = 2;
        while ($this->site->pages()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$suffix++;
        }$page = $this->site->pages()->create(['name' => $source->name.' (cópia)', 'slug' => $slug, 'status' => 'draft', 'is_homepage' => false, 'sort_order' => ((int) $this->site->pages()->max('sort_order')) + 1, 'seo' => $source->seo ?: []]);
        foreach ($source->sections as $section) {
            $page->sections()->create(['type' => $section->type, 'label' => $section->label, 'content' => $section->content ?: [], 'settings' => $section->settings ?: [], 'sort_order' => $section->sort_order, 'is_visible' => (bool) $section->is_visible]);
        }$this->refreshPages();
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
        } $replacement = $this->site->pages()->where('is_homepage', true)->first() ?? $this->site->pages()->orderBy('sort_order')->firstOrFail();
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
        }$this->pageSeo[$key] = Str::limit($value, $key === 'description' ? 320 : 500, '');
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
        }DB::transaction(function () use ($page): void {
            $this->site->update(['theme' => $this->theme, 'settings' => $this->siteSettings]);
            $page->update(['seo' => $this->pageSeo]);
            $existingIds = [];
            foreach ($this->sections as $index => $data) {
                $payload = ['type' => Str::limit((string) ($data['type'] ?? 'text'), 80, ''), 'label' => Str::limit((string) ($data['label'] ?? 'Secção'), 120, ''), 'content' => is_array($data['content'] ?? null) ? $data['content'] : [], 'settings' => is_array($data['settings'] ?? null) ? $data['settings'] : [], 'sort_order' => $index, 'is_visible' => (bool) ($data['is_visible'] ?? true)];
                $section = ! empty($data['id']) ? $page->sections()->findOrFail((int) $data['id']) : $page->sections()->create($payload);
                if (! empty($data['id'])) {
                    $section->update($payload);
                }$existingIds[] = $section->id;
                $this->sections[$index]['id'] = $section->id;
            }$page->sections()->whereNotIn('id', $existingIds ?: [0])->delete();
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
        }app(WebsiteVersionService::class)->create($this->site->fresh(), auth()->id(), Str::limit(trim($label) ?: 'Versão guardada', 120, ''));
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

    public function selectElement(string $elementId): void
    {
        abort_unless($this->selectedSection !== null && isset($this->sections[$this->selectedSection]), 422);
        $document = SiteSectionDocument::fromSection($this->sectionModel($this->selectedSection));
        abort_unless($this->findElement($document['nodes'] ?? [], $elementId) !== null, 404);
        $this->selectedElementId = $elementId;
    }

    public function updateSelectedElement(string $field, string $value): void
    {
        abort_unless($this->selectedSection !== null && $this->selectedElementId !== null, 422);
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
        $element = $this->findElement($document['nodes'] ?? [], $this->selectedElementId);
        abort_unless(is_array($element), 404);
        $settings = is_array($element['settings'] ?? null) ? $element['settings'] : [];
        ResponsiveStyles::setForDevice($settings, $this->device, $key, trim($value));
        $document = BuilderDocumentEditor::updateElement($document, $this->selectedElementId, null, $settings);
        $this->applyDocumentToSection($index, $document);
        $this->dirty = true;
        $this->statusMessage = 'Estilo responsivo alterado — por guardar';
    }

    public function removeElement(string $elementId): void
    {
        abort_unless($this->selectedSection !== null && isset($this->sections[$this->selectedSection]), 422);
        $index = $this->selectedSection;
        $document = SiteSectionDocument::fromSection($this->sectionModel($index));
        $document = BuilderDocumentEditor::removeElement($document, $elementId);
        $this->applyDocumentToSection($index, $document);
        if ($this->selectedElementId === $elementId) {
            $this->selectedElementId = null;
        }$this->dirty = true;
    }

    public function moveElement(string $elementId, int $offset): void
    {
        abort_unless($this->selectedSection !== null && isset($this->sections[$this->selectedSection]), 422);
        $index = $this->selectedSection;
        $document = SiteSectionDocument::fromSection($this->sectionModel($index));
        $document = BuilderDocumentEditor::moveElement($document, $elementId, max(-10, min(10, $offset)));
        $this->applyDocumentToSection($index, $document);
        $this->selectedElementId = $elementId;
        $this->dirty = true;
    }

    public function addSection(string $type): void
    {
        $allowed = ['hero', 'text', 'image', 'button', 'feature_grid', 'card', 'testimonials', 'faq', 'gallery', 'contact_form', 'product_grid', 'product_card', 'pricing', 'blog_posts', 'social_links', 'video', 'map', 'newsletter', 'cta'];
        abort_unless(in_array($type, $allowed, true), 422);
        $this->sections[] = ['id' => null, 'type' => $type, 'label' => Str::headline($type), 'content' => $this->defaultContent($type), 'settings' => ['background' => 'transparent', 'padding' => 'lg', 'align' => 'left'], 'is_visible' => true];
        $this->selectedSection = count($this->sections) - 1;
        $this->selectedElementId = null;
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
        $this->selectedElementId = null;
        $this->dirty = true;
    }

    public function deleteSection(int $index): void
    {
        abort_unless(isset($this->sections[$index]), 404);
        array_splice($this->sections, $index, 1);
        $this->selectedSection = $this->sections !== [] ? min($index, count($this->sections) - 1) : null;
        $this->selectedElementId = null;
        $this->dirty = true;
    }

    public function moveSection(int $index, string $direction): void
    {
        abort_unless(in_array($direction, ['up', 'down'], true), 422);
        $target = $direction === 'up' ? $index - 1 : $index + 1;
        if (! isset($this->sections[$index],$this->sections[$target])) {
            return;
        }[$this->sections[$index],$this->sections[$target]] = [$this->sections[$target], $this->sections[$index]];
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
    }

    public function selectSection(int $index): void
    {
        abort_unless(isset($this->sections[$index]), 404);
        $this->selectedSection = $index;
        $this->selectedElementId = null;
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
        if ($this->dirty) {
            $this->save();
        }$this->publishChecks = $this->formatPublishChecks(app(WebsitePublishingService::class)->validate($this->site->fresh()));
        $this->showPublish = true;
    }

    public function publish(): void
    {
        $this->save();
        $this->site->refresh();
        $result = app(WebsitePublishingService::class)->validate($this->site);
        $this->publishChecks = $this->formatPublishChecks($result);
        if (! $result['ok']) {
            $this->showPublish = true;

            return;
        }app(WebsitePublishingService::class)->publish($this->site, auth()->id());
        $this->site->refresh();
        $this->showPublish = false;
        $this->statusMessage = 'Website publicado';
    }

    private function formatPublishChecks(array $result): array
    {
        $checks = [];
        foreach ($result['errors'] ?? [] as $message) {
            $checks[] = ['level' => 'error', 'label' => 'Necessário', 'message' => $message];
        }foreach ($result['warnings'] ?? [] as $message) {
            $checks[] = ['level' => 'warning', 'label' => 'Recomendado', 'message' => $message];
        }if ($checks === []) {
            $checks[] = ['level' => 'success', 'label' => 'Tudo pronto', 'message' => 'O website passou todas as verificações de publicação.'];
        }

return $checks;
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
        abort_unless(in_array($key, ['primary', 'secondary', 'background', 'text', 'radius', 'font_heading', 'font_body'], true), 422);
        $value = trim($value);
        if (in_array($key, ['primary', 'secondary', 'background', 'text'], true) && ! preg_match('/^#[0-9a-f]{6}$/i', $value)) {
            abort(422, 'Cor inválida.');
        }if ($key === 'radius' && ! preg_match('/^(0|[0-9]+(?:\.[0-9]+)?)(px|rem|em|%)$/', $value)) {
            abort(422, 'Raio inválido.');
        }$this->theme[$key] = Str::limit($value, 100, '');
        $this->dirty = true;
    }

    public function applyTemplate(string $template): void
    {
        abort_unless(WebsiteTemplates::has($template), 422);
        if ($this->dirty) {
            $this->save();
        }app(WebsiteTemplateInstaller::class)->install($this->site->fresh(), $template);
        $this->site->refresh();
        $home = $this->site->pages()->where('is_homepage', true)->firstOrFail();
        $this->loadPage($home->id);
        $this->loadVersions();
        $this->showTemplates = false;
        $this->statusMessage = 'Template aplicado ao website inteiro';
    }

    public function generateWithAi(): void
    {
        $brief = trim($this->aiBrief);
        abort_if($brief === '', 422, 'Descreve o website que queres criar.');
        abort_if(mb_strlen($brief) > 4000, 422, 'A descrição do website é demasiado longa.');
        $result = app(WebsiteAiGenerator::class)->generate(['prompt' => $brief, 'description' => $brief, 'business_name' => $this->site->name, 'category' => $this->site->type, 'pages' => ['home', 'about', 'services', 'contact']]);
        $pages = is_array($result['pages'] ?? null) ? $result['pages'] : [];
        foreach ($pages as $slug => $pageData) {
            if (! is_string($slug) || ! is_array($pageData)) {
                continue;
            }$slug = Str::slug($slug);
            if ($slug === '') {
                continue;
            }$page = $this->site->pages()->updateOrCreate(['slug' => $slug], ['name' => Str::headline($slug), 'status' => 'draft', 'seo' => [], 'sort_order' => $this->pageSortOrder($slug)]);
            $sections = $this->sanitizeAiSections($pageData);
            $page->sections()->delete();
            foreach ($sections as $index => $section) {
                $page->sections()->create(['type' => $section['type'], 'label' => $section['label'], 'content' => $section['content'], 'settings' => ['background' => 'transparent', 'padding' => 'lg', 'align' => 'left'], 'sort_order' => $index, 'is_visible' => true]);
            }if ($slug === 'home') {
                $this->site->pages()->update(['is_homepage' => false]);
                $page->update(['is_homepage' => true]);
            }
        }$home = $this->site->pages()->where('is_homepage', true)->first() ?? $this->site->pages()->orderBy('sort_order')->first();
        if ($home) {
            $this->loadPage($home->id);
        }$this->refreshPages();
        $this->dirty = false;
        $this->showAi = false;
        $this->statusMessage = 'Website gerado por IA — revê as páginas antes de publicar';
    }

    private function sanitizeAiSections(array $sections): array
    {
        $result = [];
        foreach (array_slice($sections, 0, 20) as $section) {
            if (! is_array($section) || ! is_string($section['type'] ?? null) || ! in_array($section['type'], ['hero', 'text', 'image', 'button', 'feature_grid', 'card', 'testimonials', 'faq', 'gallery', 'contact_form', 'product_grid', 'product_card', 'pricing', 'blog_posts', 'social_links', 'video', 'map', 'newsletter', 'cta'], true)) {
                continue;
            }$type = $section['type'];
            $content = is_array($section['content'] ?? null) ? $section['content'] : $this->defaultContent($type);
            $result[] = ['type' => $type, 'label' => Str::limit((string) ($section['label'] ?? Str::headline($type)), 120, ''), 'content' => $this->sanitizeLegacyContent($content)];
        }

return $result;
    }

    private function sanitizeLegacyContent(array $content): array
    {
        array_walk_recursive($content, function (&$value): void {
            if (is_string($value)) {
                $value = Str::limit($value, 4000, '');
            }
        });

        return $content;
    }

    private function sanitizeElementContent(string $type, array $content): array
    {
        $definition = ElementRegistry::get($type);
        $content = array_intersect_key($content, array_flip(array_keys($definition['default_content'])));
        foreach ($content as &$value) {
            if (is_string($value)) {
                $value = Str::limit($value, 2000, '');
            }
        }

return $content;
    }

    private function lastElementId(array $document): ?string
    {
        $children = $document['nodes'][0]['children'] ?? [];
        $last = end($children);

        return is_array($last) && is_string($last['id'] ?? null) ? $last['id'] : null;
    }

    private function findElement(array $nodes, string $elementId): ?array
    {
        foreach ($nodes as $node) {
            if (($node['id'] ?? null) === $elementId) {
                return $node;
            }if (is_array($node['children'] ?? null)) {
                $found = $this->findElement($node['children'], $elementId);
                if ($found !== null) {
                    return $found;
                }
            }
        }

return null;
    }

    private function pageSortOrder(string $slug): int
    {
        $existing = $this->site->pages()->where('slug', $slug)->value('sort_order');

        return $existing !== null ? (int) $existing : ((int) $this->site->pages()->max('sort_order')) + 1;
    }

    private function createPageRecord(string $name, string $slug, bool $homepage = false): SitePage
    {
        return $this->site->pages()->create(['name' => $name, 'slug' => $slug, 'status' => 'draft', 'is_homepage' => $homepage, 'sort_order' => 0, 'seo' => []]);
    }

    private function addStarterSection(SitePage $page): void
    {
        $page->sections()->create(['type' => 'hero', 'label' => 'Hero', 'content' => $this->defaultContent('hero'), 'settings' => ['background' => 'transparent', 'padding' => 'lg', 'align' => 'left'], 'sort_order' => 0, 'is_visible' => true]);
    }

    private function defaultTheme(): array
    {
        return ['primary' => '#635bff', 'secondary' => '#111827', 'background' => '#ffffff', 'text' => '#111827', 'radius' => '1rem', 'font_heading' => 'Inter', 'font_body' => 'Inter'];
    }

    private function defaultContent(string $type): array
    {
        return match ($type) {
            'hero' => ['title' => 'O teu novo website', 'subtitle' => 'Uma presença online profissional.', 'button_label' => 'Saber mais', 'button_url' => '#'], 'text' => ['title' => 'Sobre nós', 'body' => 'Escreve aqui o conteúdo da tua secção.'], 'image' => ['url' => '', 'alt' => '', 'caption' => ''], 'button' => ['label' => 'Saber mais', 'url' => '#'], 'feature_grid' => ['title' => 'O que oferecemos', 'items' => [['title' => 'Qualidade', 'description' => 'Uma experiência pensada para os teus clientes.'], ['title' => 'Simplicidade', 'description' => 'Informação clara e fácil de encontrar.'], ['title' => 'Confiança', 'description' => 'Uma presença digital profissional.']]], 'testimonials' => ['title' => 'O que dizem os clientes', 'items' => [['name' => 'Cliente', 'quote' => 'Excelente experiência.']]], 'faq' => ['title' => 'Perguntas frequentes', 'items' => [['question' => 'Como funciona?', 'answer' => 'Adiciona aqui a resposta.']]], 'pricing' => ['title' => 'Planos', 'items' => [['name' => 'Essencial', 'price' => 'Desde 0€', 'description' => 'Uma opção simples para começar.']]], 'cta' => ['title' => 'Pronto para começar?', 'description' => 'Fala connosco e descobre como podemos ajudar.', 'button_label' => 'Contactar', 'button_url' => '#'], 'contact_form' => ['title' => 'Fala connosco'], 'newsletter' => ['title' => 'Recebe novidades', 'description' => 'Subscreve a nossa newsletter.'], default => ['title' => Str::headline($type)],
        };
    }

    public function render()
    {
        return view('livewire.builder-studio');
    }
}
