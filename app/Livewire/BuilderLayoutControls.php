<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Site;
use App\Models\SitePage;
use App\Support\WebsiteBuilder\WebsiteTemplates;
use Livewire\Attributes\On;
use Livewire\Component;

final class BuilderLayoutControls extends Component
{
    public Site $site;
    public bool $open = false;
    public string $tab = 'templates';
    public ?int $pageId = null;
    public array $pages = [];
    public string $navigation = 'top';
    public string $sidebarVariant = 'solid';
    public string $sidebarWidth = '280px';

    public function mount(): void
    {
        $site = request()->route('site');
        abort_unless($site instanceof Site, 404);
        abort_unless($site->isManageableBy(auth()->user()), 403);
        $this->site = $site;
        $this->refreshPages();
        $this->pageId = $site->pages()->orderByDesc('is_homepage')->orderBy('sort_order')->value('id');
        $this->loadPageLayout();
    }

    public function refreshPages(): void
    {
        $this->pages = $this->site->pages()->orderBy('sort_order')->get()->map(fn (SitePage $page): array => [
            'id' => $page->id,
            'name' => $page->name,
            'slug' => $page->slug,
            'is_homepage' => (bool) $page->is_homepage,
        ])->all();
    }

    #[On('sync-builder-page')]
    public function syncPage(int $pageId): void
    {
        abort_unless($this->site->pages()->whereKey($pageId)->exists(), 404);
        $this->pageId = $pageId;
        $this->loadPageLayout();
    }

    public function selectPage(int $pageId): void
    {
        $this->syncPage($pageId);
        $this->dispatch('builder-page-selected', pageId: $pageId);
    }

    public function currentTemplate(): ?string
    {
        $theme = is_array($this->currentPage()->theme) ? $this->currentPage()->theme : [];

        return is_string($theme['layout_template'] ?? null) ? $theme['layout_template'] : null;
    }

    public function applyTemplate(string $template): void
    {
        $page = $this->currentPage();
        $definitions = $this->templates();
        abort_unless(isset($definitions[$template]), 422);
        $definition = $definitions[$template];
        $pageTheme = array_merge(is_array($page->theme) ? $page->theme : [], $definition);
        $pageTheme['layout_template'] = $template;
        $pageTheme['layout_label'] = $definition['label'];
        unset($pageTheme['label'], $pageTheme['description']);
        $page->update(['theme' => $pageTheme]);
        $this->loadPageLayout();
        $this->dispatch('builder-page-selected', pageId: $page->id);
        $this->dispatch('builder-layout-changed', pageId: $page->id);
    }

    public function saveLayout(): void
    {
        $page = $this->currentPage();
        abort_unless(in_array($this->navigation, ['top', 'left', 'right'], true), 422);
        abort_unless(in_array($this->sidebarVariant, ['solid', 'glass', 'minimal'], true), 422);
        abort_unless(in_array($this->sidebarWidth, ['240px', '250px', '260px', '270px', '280px', '290px', '300px', '320px'], true), 422);
        $theme = is_array($page->theme) ? $page->theme : [];
        $theme['navigation'] = $this->navigation;
        $theme['sidebar_variant'] = $this->sidebarVariant;
        $theme['sidebar_width'] = $this->sidebarWidth;
        $page->update(['theme' => $theme]);
        $this->dispatch('builder-page-selected', pageId: $page->id);
        $this->dispatch('builder-layout-changed', pageId: $page->id);
    }

    public function templates(): array
    {
        $templates = [];

        foreach (WebsiteTemplates::all() as $key => $definition) {
            $theme = is_array($definition['theme'] ?? null) ? $definition['theme'] : [];

            $templates[$key] = [
                'label' => (string) ($definition['name'] ?? $key),
                'description' => (string) ($definition['description'] ?? ''),
                'navigation' => in_array($theme['navigation'] ?? 'top', ['top', 'left', 'right'], true) ? $theme['navigation'] : 'top',
                'sidebar_variant' => in_array($theme['sidebar_variant'] ?? 'solid', ['solid', 'glass', 'minimal'], true) ? $theme['sidebar_variant'] : 'solid',
                'sidebar_width' => in_array($theme['sidebar_width'] ?? '280px', ['240px', '250px', '260px', '270px', '280px', '290px', '300px', '320px'], true) ? $theme['sidebar_width'] : '280px',
                'header_style' => (string) ($theme['header_style'] ?? 'modern'),
                'container' => (string) ($theme['container'] ?? 'wide'),
                'page_background' => (string) ($theme['background'] ?? '#ffffff'),
            ];
        }

        return $templates;
    }

    public function tools(): array
    {
        return [
            'elementos' => [
                ['type'=>'heading','label'=>'Título','icon'=>'T'], ['type'=>'text','label'=>'Texto','icon'=>'Aa'], ['type'=>'button','label'=>'Botão','icon'=>'↗'], ['type'=>'image','label'=>'Imagem','icon'=>'▧'], ['type'=>'quote','label'=>'Citação','icon'=>'“'], ['type'=>'video','label'=>'Vídeo','icon'=>'▶'], ['type'=>'form','label'=>'Formulário','icon'=>'☷'], ['type'=>'social_links','label'=>'Redes sociais','icon'=>'◎'],
            ],
            'seccoes' => [
                ['type'=>'hero','label'=>'Hero','icon'=>'✦'], ['type'=>'text','label'=>'Texto','icon'=>'▤'], ['type'=>'feature_grid','label'=>'Benefícios','icon'=>'⊞'], ['type'=>'gallery','label'=>'Galeria','icon'=>'▦'], ['type'=>'testimonials','label'=>'Testemunhos','icon'=>'❞'], ['type'=>'pricing','label'=>'Preços','icon'=>'€'], ['type'=>'faq','label'=>'FAQ','icon'=>'?'], ['type'=>'cta','label'=>'CTA','icon'=>'→'], ['type'=>'contact_form','label'=>'Contacto','icon'=>'✉'],
            ],
        ];
    }

    private function currentPage(): SitePage
    {
        return $this->site->pages()->findOrFail($this->pageId);
    }

    private function loadPageLayout(): void
    {
        $theme = is_array($this->currentPage()->theme) ? $this->currentPage()->theme : [];
        $navigation = $theme['navigation'] ?? 'top';
        $sidebarVariant = $theme['sidebar_variant'] ?? 'solid';
        $sidebarWidth = $theme['sidebar_width'] ?? '280px';

        $this->navigation = in_array($navigation, ['top', 'left', 'right'], true) ? $navigation : 'top';
        $this->sidebarVariant = in_array($sidebarVariant, ['solid', 'glass', 'minimal'], true) ? $sidebarVariant : 'solid';
        $this->sidebarWidth = in_array($sidebarWidth, ['240px', '250px', '260px', '270px', '280px', '290px', '300px', '320px'], true) ? $sidebarWidth : '280px';
    }

    public function render(): mixed
    {
        return view('livewire.builder-layout-controls');
    }
}
