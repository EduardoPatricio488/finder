<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\Site;
use App\Models\SitePage;
use App\Models\SiteSection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.builder')]
#[Title('Editor')]
class BuilderEditor extends Component
{
    public Site $site;

    public ?int $pageId = null;

    public string $pageName = '';

    public string $pageSlug = '';

    public string $pageStatus = 'draft';

    public array $sections = [];

    public array $theme = [];

    public bool $dirty = false;

    public bool $showOnboarding = false;

    public ?int $selectedSection = null;

    public array $history = [];

    public array $future = [];

    public function mount(Site $site): void
    {
        abort_unless($site->isManageableBy(auth()->user()), 403);

        $this->site = $site->load('pages.sections');
        $this->theme = $site->theme ?: $this->defaultTheme();
        $this->showOnboarding = ! (bool) data_get($site->settings, 'builder.onboarding_seen', false);

        $page = $this->site->pages->firstWhere('is_homepage', true) ?? $this->site->pages->first();
        if (! $page) {
            $page = $this->createDefaultPage();
            $this->site->load('pages.sections');
        }

        $this->selectPage($page->id);
    }

    public function getCanUndoProperty(): bool
    {
        return count($this->history) > 0;
    }

    public function getCanRedoProperty(): bool
    {
        return count($this->future) > 0;
    }

    public function updated(string $property): void
    {
        if (str_ends_with($property, '.features') && is_string(data_get($this, $property))) {
            $features = preg_split('/\r\n|\r|\n/', data_get($this, $property));
            data_set($this, $property, array_values(array_filter(array_map('trim', $features))));
        }

        if (str_starts_with($property, 'sections.') || str_starts_with($property, 'theme.') || in_array($property, ['pageName', 'pageSlug', 'pageStatus'], true)) {
            $this->markDirty();
        }
    }

    public function dismissOnboarding(): void
    {
        $settings = $this->site->settings ?? [];
        data_set($settings, 'builder.onboarding_seen', true);
        $this->site->update(['settings' => $settings]);
        $this->site->refresh();
        $this->showOnboarding = false;
    }

    public function selectSection(?int $index): void
    {
        if ($index === null) {
            $this->selectedSection = null;

            return;
        }

        abort_unless(isset($this->sections[$index]), 404);
        $this->selectedSection = $index;
        $this->dispatch('builder-section-selected', section: $index);
    }

    public function selectPage(int $pageId): void
    {
        $page = $this->site->pages()->with('sections')->findOrFail($pageId);

        $this->pageId = $page->id;
        $this->pageName = $page->name;
        $this->pageSlug = $page->slug;
        $this->pageStatus = $page->status;
        $this->sections = $page->sections->map(fn (SiteSection $section) => [
            'id' => $section->id,
            'type' => $section->type,
            'label' => $section->label,
            'content' => $section->content ?? [],
            'settings' => $section->settings ?? [],
            'is_visible' => $section->is_visible,
        ])->values()->all();

        $this->syncProductGridItems();
        $this->selectedSection = count($this->sections) ? 0 : null;
        $this->history = [];
        $this->future = [];
        $this->dirty = false;
        $this->dispatch('builder-page-selected', pageId: $page->id);
    }

    public function updateInline(int $sectionIndex, string $field, string $value, ?int $itemIndex = null): void
    {
        abort_unless(isset($this->sections[$sectionIndex]), 404);

        $allowed = ['title', 'subtitle', 'body', 'description', 'label', 'button_label', 'button_url', 'caption', 'alt', 'address', 'question', 'answer', 'name', 'role', 'quote', 'price', 'excerpt', 'url'];
        abort_unless(in_array($field, $allowed, true), 422);

        $value = trim(strip_tags($value));
        $this->checkpoint();
        $this->selectedSection = $sectionIndex;

        if ($itemIndex !== null) {
            abort_unless(isset($this->sections[$sectionIndex]['content']['items'][$itemIndex]), 404);
            $this->sections[$sectionIndex]['content']['items'][$itemIndex][$field] = $value;
        } else {
            $this->sections[$sectionIndex]['content'][$field] = $value;
        }

        $this->markDirty();
        $this->dispatch('builder-inline-updated', section: $sectionIndex, item: $itemIndex, field: $field);
    }

    public function createPage(): void
    {
        $this->createPageFromPreset('recommended');
    }

    public function createPageFromPreset(string $preset): void
    {
        $presets = [
            'recommended' => ['name' => 'Nova página', 'slug' => 'nova-pagina', 'sections' => ['hero', 'feature_grid', 'cta']],
            'landing' => ['name' => 'Landing Page', 'slug' => 'landing-page', 'sections' => ['hero', 'feature_grid', 'testimonials', 'faq', 'cta']],
            'services' => ['name' => 'Serviços', 'slug' => 'servicos', 'sections' => ['hero', 'text', 'feature_grid', 'testimonials', 'cta']],
            'about' => ['name' => 'Sobre nós', 'slug' => 'sobre-nos', 'sections' => ['hero', 'text', 'feature_grid', 'testimonials', 'cta']],
            'contact' => ['name' => 'Contactos', 'slug' => 'contactos', 'sections' => ['hero', 'contact_form', 'map', 'cta']],
            'pricing' => ['name' => 'Preços', 'slug' => 'precos', 'sections' => ['hero', 'pricing', 'faq', 'cta']],
        ];

        abort_unless(isset($presets[$preset]), 422);
        $definition = $presets[$preset];
        $this->checkpoint();
        $slug = Str::slug($definition['slug']);
        $baseSlug = $slug;
        $counter = 2;
        while ($this->site->pages()->withTrashed()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter++;
        }

        $page = DB::transaction(function () use ($definition, $slug) {
            $page = $this->site->pages()->create(['name' => $definition['name'], 'slug' => $slug, 'status' => 'draft', 'is_homepage' => false, 'sort_order' => ((int) $this->site->pages()->max('sort_order')) + 1]);
            foreach ($definition['sections'] as $index => $type) {
                $page->sections()->create(['type' => $type, 'label' => Str::headline($type), 'content' => $this->defaultContent($type), 'settings' => ['background' => 'transparent', 'padding' => 'lg', 'align' => 'left'], 'sort_order' => $index, 'is_visible' => true]);
            }

            return $page;
        });

        $this->site->load('pages.sections');
        $this->selectPage($page->id);
    }

    public function duplicatePage(): void
    {
        $this->checkpoint();
        $source = $this->site->pages()->with('sections')->findOrFail($this->pageId);
        $slug = $source->slug.'-copia';
        $baseSlug = $slug;
        $counter = 2;
        while ($this->site->pages()->withTrashed()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter++;
        }

        $page = DB::transaction(function () use ($source, $slug) {
            $page = $this->site->pages()->create(['name' => $source->name.' (cópia)', 'slug' => $slug, 'status' => 'draft', 'is_homepage' => false, 'seo' => $source->seo, 'sort_order' => ((int) $this->site->pages()->max('sort_order')) + 1]);
            foreach ($source->sections as $section) {
                $page->sections()->create(['type' => $section->type, 'label' => $section->label, 'content' => $section->content, 'settings' => $section->settings, 'sort_order' => $section->sort_order, 'is_visible' => $section->is_visible]);
            }

            return $page;
        });

        $this->site->load('pages.sections');
        $this->selectPage($page->id);
    }

    public function deletePage(): void
    {
        $this->checkpoint();
        $page = $this->site->pages()->findOrFail($this->pageId);
        abort_if($page->is_homepage, 422, 'A homepage não pode ser eliminada.');
        $page->delete();
        $fallback = $this->site->pages()->orderBy('sort_order')->firstOrFail();
        $this->site->load('pages.sections');
        $this->selectPage($fallback->id);
    }

    public function addSection(string $type): void
    {
        $allowed = ['hero', 'text', 'image', 'button', 'feature_grid', 'card', 'testimonials', 'faq', 'gallery', 'contact_form', 'product_grid', 'product_card', 'pricing', 'blog_posts', 'social_links', 'video', 'map', 'newsletter', 'cta'];
        abort_unless(in_array($type, $allowed, true), 422);
        $this->checkpoint();
        $this->sections[] = ['id' => null, 'type' => $type, 'label' => Str::headline($type), 'content' => $this->defaultContent($type), 'settings' => ['background' => 'transparent', 'padding' => 'lg', 'align' => 'left'], 'is_visible' => true];
        $this->selectedSection = count($this->sections) - 1;
        if ($type === 'product_grid') {
            $this->syncProductGridItems();
        }
        $this->markDirty();
    }

    public function duplicateSection(int $index): void
    {
        abort_unless(isset($this->sections[$index]), 404);
        $this->checkpoint();
        $copy = $this->sections[$index];
        $copy['id'] = null;
        $copy['label'] = ($copy['label'] ?? Str::headline($copy['type'])).' (cópia)';
        array_splice($this->sections, $index + 1, 0, [$copy]);
        $this->selectedSection = $index + 1;
        $this->markDirty();
    }

    public function addItem(int $sectionIndex): void
    {
        abort_unless(isset($this->sections[$sectionIndex]), 404);
        $type = $this->sections[$sectionIndex]['type'];

        if ($type === 'product_grid') {
            $this->selectedSection = $sectionIndex;
            $this->syncProductGridItems($sectionIndex);
            $this->dispatch('builder-products-loaded', section: $sectionIndex);

            return;
        }

        $templates = [
            'feature_grid' => ['title' => 'Novo benefício', 'description' => 'Descreve este benefício.'],
            'testimonials' => ['name' => 'Cliente', 'role' => 'Função', 'quote' => 'Escreve aqui o testemunho.', 'image' => ''],
            'faq' => ['question' => 'Nova pergunta?', 'answer' => 'Escreve a resposta.'],
            'gallery' => ['url' => '', 'alt' => '', 'caption' => ''],
            'pricing' => ['name' => 'Novo plano', 'price' => '49 €', 'description' => 'Descrição do plano.', 'features' => ['Funcionalidade 1', 'Funcionalidade 2'], 'button_label' => 'Escolher', 'button_url' => '#'],
            'blog_posts' => ['title' => 'Novo artigo', 'excerpt' => 'Resumo do artigo.', 'url' => '#', 'image' => ''],
            'social_links' => ['label' => 'Nova rede', 'url' => '#'],
        ];

        if (! isset($templates[$type])) {
            $this->selectedSection = $sectionIndex;
            $this->dispatch('builder-item-not-supported', section: $sectionIndex, type: $type);

            return;
        }

        $this->checkpoint();
        if (! isset($this->sections[$sectionIndex]['content']['items']) || ! is_array($this->sections[$sectionIndex]['content']['items'])) {
            $this->sections[$sectionIndex]['content']['items'] = [];
        }
        $this->sections[$sectionIndex]['content']['items'][] = $templates[$type];
        $this->selectedSection = $sectionIndex;
        $this->markDirty();
    }

    public function removeItem(int $sectionIndex, int $itemIndex): void
    {
        abort_unless(isset($this->sections[$sectionIndex]['content']['items'][$itemIndex]), 404);
        abort_unless($this->sections[$sectionIndex]['type'] !== 'product_grid', 422);
        $this->checkpoint();
        array_splice($this->sections[$sectionIndex]['content']['items'], $itemIndex, 1);
        $this->markDirty();
    }

    public function moveItem(int $sectionIndex, int $itemIndex, string $direction): void
    {
        abort_unless(in_array($direction, ['up', 'down'], true), 422);
        abort_unless($this->sections[$sectionIndex]['type'] !== 'product_grid', 422);
        $items = $this->sections[$sectionIndex]['content']['items'] ?? [];
        $target = $direction === 'up' ? $itemIndex - 1 : $itemIndex + 1;
        if (! isset($items[$itemIndex]) || $target < 0 || $target >= count($items)) {
            return;
        }
        $this->checkpoint();
        [$items[$itemIndex], $items[$target]] = [$items[$target], $items[$itemIndex]];
        $this->sections[$sectionIndex]['content']['items'] = $items;
        $this->selectedSection = $sectionIndex;
        $this->markDirty();
    }

    public function reorderSections(array $orderedIds): void
    {
        if (count($orderedIds) !== count($this->sections)) {
            return;
        }
        $currentIds = collect($this->sections)->map(fn ($section, $index) => (string) ($section['id'] ?? 'new-'.$index))->values()->all();
        $orderedIds = array_map('strval', $orderedIds);
        if (count(array_unique($orderedIds)) !== count($orderedIds) || array_diff($orderedIds, $currentIds) || array_diff($currentIds, $orderedIds)) {
            abort(422, 'Ordem das secções inválida.');
        }
        if ($currentIds === $orderedIds) {
            return;
        }
        $this->checkpoint();
        $lookup = collect($this->sections)->mapWithKeys(function (array $section, int $index) {
            return [(string) ($section['id'] ?? 'new-'.$index) => $section];
        });
        $this->sections = array_values(array_map(fn (string $id) => $lookup[$id], $orderedIds));
        $this->selectedSection = 0;
        $this->markDirty();
        $this->dispatch('builder-sections-reordered');
    }

    public function setSectionVisibility(int $index, bool $visible): void
    {
        abort_unless(isset($this->sections[$index]), 404);
        $this->checkpoint();
        $this->sections[$index]['is_visible'] = $visible;
        $this->selectedSection = $index;
        $this->markDirty();
    }

    public function removeSection(int $index): void
    {
        abort_unless(isset($this->sections[$index]), 404);
        $this->checkpoint();
        unset($this->sections[$index]);
        $this->sections = array_values($this->sections);
        $this->selectedSection = count($this->sections) ? min($index, count($this->sections) - 1) : null;
        $this->markDirty();
    }

    public function moveSection(int $index, string $direction): void
    {
        abort_unless(in_array($direction, ['up', 'down'], true), 422);
        $target = $direction === 'up' ? $index - 1 : $index + 1;
        if ($target < 0 || $target >= count($this->sections)) {
            return;
        }
        $this->checkpoint();
        [$this->sections[$index], $this->sections[$target]] = [$this->sections[$target], $this->sections[$index]];
        $this->selectedSection = $target;
        $this->markDirty();
    }

    public function undo(): void
    {
        if (! $this->history) {
            return;
        }
        $this->future[] = $this->currentSnapshot();
        $this->restoreSnapshot(array_pop($this->history));
    }

    public function redo(): void
    {
        if (! $this->future) {
            return;
        }
        $this->history[] = $this->currentSnapshot();
        $this->restoreSnapshot(array_pop($this->future));
    }

    public function save(): void
    {
        $page = $this->site->pages()->findOrFail($this->pageId);
        $slug = Str::slug($this->pageSlug ?: $this->pageName);

        DB::transaction(function () use ($page, $slug) {
            $page->update(['name' => trim($this->pageName) ?: 'Sem título', 'slug' => $slug ?: 'pagina', 'status' => in_array($this->pageStatus, ['draft', 'published'], true) ? $this->pageStatus : 'draft']);
            $keepIds = [];
            foreach ($this->sections as $index => $data) {
                $section = ! empty($data['id']) ? $page->sections()->findOrFail($data['id']) : new SiteSection(['site_page_id' => $page->id]);
                $content = $data['content'] ?? [];
                if (($data['type'] ?? '') === 'product_grid') {
                    unset($content['items']);
                }
                $section->fill(['type' => $data['type'], 'label' => $data['label'] ?? Str::headline($data['type']), 'content' => $content, 'settings' => $data['settings'] ?? [], 'sort_order' => $index, 'is_visible' => (bool) ($data['is_visible'] ?? true)]);
                $section->save();
                $keepIds[] = $section->id;
                $this->sections[$index]['id'] = $section->id;
            }
            $query = $page->sections();
            if ($keepIds) {
                $query->whereNotIn('id', $keepIds);
            }
            $query->delete();
            $this->site->update(['theme' => $this->theme]);
        });

        $this->site->load('pages.sections');
        $this->syncProductGridItems();
        $this->dirty = false;
        $this->dispatch('builder-saved');
    }

    public function publish(): void
    {
        $this->save();
        $this->site->update(['is_published' => true, 'status' => 'published', 'published_at' => now()]);
        $this->site->refresh();
        $this->dispatch('builder-published');
    }

    public function unpublish(): void
    {
        $this->save();
        $this->site->update(['is_published' => false, 'status' => 'draft', 'published_at' => null]);
        $this->site->refresh();
        $this->dispatch('builder-unpublished');
    }

    private function markDirty(): void
    {
        $this->dirty = true;
        $this->dispatch('builder-dirty');
    }

    private function checkpoint(): void
    {
        $snapshot = $this->currentSnapshot();
        $last = end($this->history);
        if ($last === $snapshot) {
            $this->future = [];

            return;
        }

        $this->history[] = $snapshot;
        if (count($this->history) > 30) {
            array_shift($this->history);
        }
        $this->future = [];
    }

    private function currentSnapshot(): array
    {
        return ['sections' => $this->sections, 'pageName' => $this->pageName, 'pageSlug' => $this->pageSlug, 'pageStatus' => $this->pageStatus, 'theme' => $this->theme];
    }

    private function restoreSnapshot(array $snapshot): void
    {
        $this->sections = $snapshot['sections'];
        $this->pageName = $snapshot['pageName'];
        $this->pageSlug = $snapshot['pageSlug'];
        $this->pageStatus = $snapshot['pageStatus'];
        $this->theme = $snapshot['theme'];
        $this->selectedSection = count($this->sections) ? 0 : null;
        $this->markDirty();
    }

    private function syncProductGridItems(?int $onlySection = null): void
    {
        $products = $this->site->products()
            ->with('category')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        foreach ($this->sections as $index => $section) {
            if (($section['type'] ?? '') !== 'product_grid' || ($onlySection !== null && $onlySection !== $index)) {
                continue;
            }

            $this->sections[$index]['content']['items'] = $products->map(fn (Product $product): array => [
                'product_id' => $product->id,
                'title' => $product->name,
                'name' => $product->name,
                'description' => $product->description ?? '',
                'price' => number_format((float) $product->price, 2, ',', '.').' €',
                'category' => $product->category?->name ?? 'Sem categoria',
                'sku' => $product->sku ?? '',
                'stock' => (int) $product->stock,
                'image_url' => $product->image_url ?? '',
            ])->values()->all();
        }
    }

    private function createDefaultPage(): SitePage
    {
        return $this->site->pages()->create([
            'name' => 'Home',
            'slug' => 'home',
            'status' => 'draft',
            'is_homepage' => true,
            'sort_order' => 0,
        ]);
    }

    private function defaultTheme(): array
    {
        return ['primary' => $this->site->primary_color ?: '#635bff', 'secondary' => $this->site->secondary_color ?: '#111827', 'accent' => '#8b5cf6', 'background' => '#ffffff', 'text' => '#111827', 'heading_font' => 'Inter', 'body_font' => 'Inter', 'radius' => 'lg', 'shadow' => 'sm', 'content_width' => '1200px'];
    }

    private function defaultContent(string $type): array
    {
        return match ($type) {
            'hero' => ['title' => 'Cria algo incrível.', 'subtitle' => 'Personaliza este conteúdo directamente no Finder.', 'button_label' => 'Saber mais', 'button_url' => '#'],
            'text' => ['title' => 'Uma secção de conteúdo', 'body' => 'Escreve aqui a mensagem que queres mostrar aos teus visitantes.'],
            'image' => ['url' => '', 'alt' => '', 'caption' => ''],
            'button' => ['label' => 'Começar agora', 'url' => '#', 'target' => '_self'],
            'feature_grid' => ['title' => 'Tudo o que precisas', 'items' => [['title' => 'Simples', 'description' => 'Edita sem código.'], ['title' => 'Flexível', 'description' => 'Adapta o teu design.'], ['title' => 'Rápido', 'description' => 'Publica em poucos minutos.']]],
            'card' => ['title' => 'Título do cartão', 'description' => 'Descrição do cartão.'],
            'testimonials' => ['title' => 'O que dizem', 'items' => [['name' => 'Cliente', 'role' => 'Cliente', 'quote' => 'Excelente experiência.', 'image' => '']]],
            'faq' => ['title' => 'Perguntas frequentes', 'items' => [['question' => 'Como funciona?', 'answer' => 'Personaliza esta resposta.']]],
            'gallery' => ['items' => [['url' => '', 'alt' => '', 'caption' => '']]],
            'contact_form' => ['title' => 'Contacta-nos', 'description' => 'Envia-nos uma mensagem.', 'button_label' => 'Enviar mensagem'],
            'product_grid' => ['title' => 'Produtos', 'description' => 'Escolhe os teus produtos favoritos.', 'limit' => 6],
            'product_card' => ['title' => 'Produto', 'description' => 'Descrição do produto.'],
            'pricing' => ['title' => 'Planos', 'items' => [['name' => 'Plano', 'price' => '49 €', 'description' => 'Descrição.', 'features' => ['Funcionalidade 1', 'Funcionalidade 2'], 'button_label' => 'Escolher', 'button_url' => '#']]],
            'blog_posts' => ['title' => 'Artigos', 'items' => [['title' => 'Novo artigo', 'excerpt' => 'Resumo do artigo.', 'url' => '#', 'image' => '']]],
            'social_links' => ['title' => 'Segue-nos', 'items' => [['label' => 'Instagram', 'url' => '#']]],
            'video' => ['title' => 'Vídeo', 'url' => '', 'description' => ''],
            'map' => ['address' => '', 'embed_url' => ''],
            'newsletter' => ['title' => 'Recebe novidades', 'description' => 'Subscreve a nossa newsletter.', 'button_label' => 'Subscrever', 'placeholder' => 'O teu email'],
            'cta' => ['title' => 'Pronto para começar?', 'description' => 'Publica o teu website quando estiveres satisfeito.', 'button_label' => 'Começar', 'button_url' => '#'],
            default => ['title' => Str::headline($type), 'description' => 'Personaliza esta secção.'],
        };
    }

    public function render(): mixed
    {
        return view('livewire.builder-editor');
    }
}
