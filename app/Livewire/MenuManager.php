<?php

namespace App\Livewire;

use App\Models\Site;
use App\Support\SiteContext;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Menus')]
class MenuManager extends Component
{
    public Site $site;

    public ?int $menuId = null;

    public string $menuName = 'Menu principal';

    public string $menuLocation = 'header';

    public string $sidebarType = 'fixed';

    public string $sidebarPosition = 'top';

    public string $sidebarAppearance = 'light';

    public string $sidebarWidth = 'normal';

    public string $sidebarIcons = 'shown';

    /** @var array<int, string> */
    public array $sidebarPageIds = [];

    public string $label = '';

    public string $url = '';

    public ?int $pageId = null;

    public ?int $parentId = null;

    public string $target = '_self';

    public bool $isVisible = true;

    public bool $menuApplied = false;

    public string $newPageName = '';

    public string $newPageSlug = '';

    public string $newPageStatus = 'draft';

    /** @var array<string, int|null> */
    public array $requiredItemIds = [];

    /** @var array<string, string> */
    public array $requiredLabels = [];

    public function mount(): void
    {
        $site = SiteContext::current();
        abort_unless($site instanceof Site && $site->isManageableBy(auth()->user()), 403);
        $this->site = $site;
        if (! $site->pages()->where('slug', 'cart')->exists()) {
            $site->pages()->create([
                'name' => 'Carrinho',
                'slug' => 'cart',
                'status' => 'published',
                'is_homepage' => false,
                'seo' => ['title' => 'Carrinho — '.$site->name, 'description' => 'Consulta e gere os produtos no teu carrinho.'],
                'theme' => [],
                'sort_order' => ((int) $site->pages()->max('sort_order')) + 1,
            ]);
        }
        session()->put('current_site_id', $site->id);
        $this->menuApplied = filled(data_get($site->settings, 'menu_applied_at'));
        $this->sidebarType = 'fixed';
        $this->sidebarPosition = (string) data_get($site->settings, 'sidebar.position', 'top');
        $this->sidebarAppearance = (string) data_get($site->settings, 'sidebar.appearance', 'light');
        $this->sidebarWidth = (string) data_get($site->settings, 'sidebar.width', 'normal');
        $this->sidebarIcons = (string) data_get($site->settings, 'sidebar.icons', 'shown');
        $this->sidebarPageIds = $site->pages()->orderBy('sort_order')->pluck('id')->map(fn ($id) => (string) $id)->all();
        $menu = $site->menus()->first();

        if ($menu) {
            $this->menuId = $menu->id;
            $this->menuName = $menu->name;
            $this->menuLocation = $menu->location ?: 'header';
        }

        $this->ensureRequiredMenuItems();
    }

    public function updatedMenuId($value): void
    {
        if (! $value) {
            return;
        }

        $menu = $this->site->menus()->find($value);

        if (! $menu) {
            return;
        }

        $this->menuName = $menu->name;
        $this->menuLocation = $menu->location ?: 'header';
        $this->requiredItemIds = [];
        $this->requiredLabels = [];
        $this->ensureRequiredMenuItems();
    }

    public function saveMenu(): void
    {
        $this->validate([
            'menuName' => ['required', 'string', 'max:100'],
            'menuLocation' => ['required', 'string', 'max:40'],
        ]);

        $menu = $this->menuId ? $this->site->menus()->find($this->menuId) : null;

        abort_unless($menu, 422, 'O menu principal ainda não está disponível.');

        $menu->update([
            'name' => $this->menuName,
            'location' => $this->menuLocation,
        ]);

        $this->menuId = $menu->id;
        $this->ensureRequiredMenuItems();
        $this->menuApplied = false;
        session()->flash('status', 'Menu guardado. Agora podes aplicar as alterações no site.');
    }

    public function saveSidebar(): void
    {
        $this->validate([
            'sidebarType' => ['required', 'in:fixed'],
            'sidebarPosition' => ['required', 'in:top,left,right'],
            'sidebarAppearance' => ['required', 'in:light,dark'],
            'sidebarWidth' => ['required', 'in:narrow,normal,wide'],
            'sidebarIcons' => ['required', 'in:shown,hidden'],
            'sidebarPageIds' => ['array', 'max:100'],
            'sidebarPageIds.*' => ['required', 'integer', 'distinct', 'exists:site_pages,id'],
        ]);

        $sitePageIds = $this->site->pages()->pluck('id')->map(fn ($id) => (string) $id)->all();
        $validPageIds = array_values(array_filter(
            array_map('strval', $this->sidebarPageIds),
            fn (string $id) => in_array($id, $sitePageIds, true)
        ));
        $validPageIds = collect($this->site->pages()->orderBy('sort_order')->pluck('id'))
            ->map(fn ($id) => (string) $id)
            ->filter(fn (string $id) => in_array($id, $validPageIds, true))
            ->values()
            ->all();

        $settings = $this->site->settings ?? [];
        data_set($settings, 'sidebar.type', $this->sidebarType);
        data_set($settings, 'sidebar.position', $this->sidebarPosition);
        data_set($settings, 'sidebar.appearance', $this->sidebarAppearance);
        data_set($settings, 'sidebar.width', $this->sidebarWidth);
        data_set($settings, 'sidebar.icons', $this->sidebarIcons);
        data_set($settings, 'sidebar.page_ids', $validPageIds);
        data_set($settings, 'sidebar.updated_at', now()->toIso8601String());
        $this->site->update(['settings' => $settings]);
        $this->site->refresh();
        session()->flash('status', 'Sidebar guardada. Aplica as alterações e pré-visualiza o website.');
        $this->menuApplied = false;
    }

    public function deletePage(int $pageId): void
    {
        $page = $this->site->pages()->findOrFail($pageId);

        if ($page->is_homepage || in_array($page->slug, ['home', 'about', 'contact'], true)) {
            session()->flash('status', 'Esta é uma página obrigatória do website e não pode ser eliminada.');
            return;
        }

        $menuIds = $this->site->menus()->pluck('id');
        $menuItems = \App\Models\SiteMenuItem::query()
            ->whereIn('site_menu_id', $menuIds)
            ->where('site_page_id', $page->id)
            ->get();

        $itemIds = $menuItems->pluck('id')->all();
        if ($itemIds !== []) {
            \App\Models\SiteMenuItem::query()
                ->whereIn('site_menu_id', $menuIds)
                ->whereIn('parent_id', $itemIds)
                ->delete();
            $menuItems->each->delete();
        }

        $settings = $this->site->settings ?? [];
        $pageIds = array_values(array_filter(
            data_get($settings, 'sidebar.page_ids', []),
            fn ($id) => (string) $id !== (string) $page->id
        ));
        data_set($settings, 'sidebar.page_ids', $pageIds);
        $this->site->update(['settings' => $settings]);

        $page->delete();
        $this->sidebarPageIds = array_values(array_filter(
            $this->sidebarPageIds,
            fn ($id) => (string) $id !== (string) $page->id
        ));
        $this->menuApplied = false;

        session()->flash('status', 'Página eliminada com sucesso.');
    }

    public function createPage(): void
    {
        $this->newPageName = trim($this->newPageName);
        $this->newPageSlug = \Illuminate\Support\Str::slug($this->newPageSlug ?: $this->newPageName);

        $this->validate([
            'newPageName' => ['required', 'string', 'max:255'],
            'newPageSlug' => ['required', 'string', 'max:120', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'unique:site_pages,slug,NULL,id,site_id,'.$this->site->id],
            'newPageStatus' => ['required', 'in:draft,published'],
        ]);

        $page = $this->site->pages()->create([
            'name' => $this->newPageName,
            'slug' => $this->newPageSlug,
            'status' => $this->newPageStatus,
            'is_homepage' => false,
            'seo' => [],
            'theme' => [],
            'sort_order' => ((int) $this->site->pages()->max('sort_order')) + 1,
        ]);

        $this->reset('newPageName', 'newPageSlug');
        $this->newPageStatus = 'draft';
        session()->flash('status', 'Página criada. Já podes editar o conteúdo e adicioná-la ao menu.');
        $this->redirectRoute('site.manage.pages.edit', ['page' => $page->id]);
    }

    public function addItem(): void
    {
        $this->validate([
            'label' => ['required', 'string', 'max:100'],
            'pageId' => ['nullable', 'integer', 'exists:site_pages,id'],
            'url' => ['nullable', 'string', 'max:2048'],
            'parentId' => ['nullable', 'integer'],
            'target' => ['required', 'in:_self,_blank'],
        ]);

        abort_if(! $this->pageId && ! filled(trim($this->url)), 422, 'Indica uma página ou um endereço para o novo menu.');

        if (! $this->menuId) {
            $this->saveMenu();
        }

        $menu = $this->site->menus()->findOrFail($this->menuId);
        $page = $this->pageId ? $this->site->pages()->findOrFail($this->pageId) : null;
        $parent = $this->parentId ? $menu->items()->findOrFail($this->parentId) : null;

        if ($this->pageId) {
            abort_unless(in_array($this->pageId, $this->availablePages()->pluck('id')->all(), true), 422, 'Esta página não está disponível na navegação.');

            if (in_array($page?->slug, ['home', 'about', 'contact'], true)) {
                abort(422, 'As páginas essenciais já estão disponíveis no menu.');
            }
        }

        $menu->items()->create([
            'site_page_id' => $page?->id,
            'parent_id' => $parent?->id,
            'label' => $this->label,
            'url' => $page?->id ? null : trim($this->url),
            'target' => $this->target,
            'sort_order' => ($menu->items()->where('parent_id', $parent?->id)->max('sort_order') ?? -1) + 1,
            'is_visible' => $this->isVisible,
        ]);

        $this->reset(['label', 'pageId', 'parentId']);
        $this->url = '';
        $this->menuApplied = false;
        $this->target = '_self';
        $this->isVisible = true;
    }

    public function removeItem(int $itemId): void
    {
        abort_if(in_array($itemId, array_values(array_filter($this->requiredItemIds)), true), 422, 'As páginas essenciais não podem ser eliminadas.');

        $menu = $this->site->menus()->findOrFail($this->menuId);
        $item = $menu->items()->findOrFail($itemId);
        $item->children()->update(['parent_id' => $item->parent_id]);
        $item->delete();
        $this->menuApplied = false;
    }

    public function toggleItem(int $itemId): void
    {
        $menu = $this->site->menus()->findOrFail($this->menuId);
        $item = $menu->items()->findOrFail($itemId);
        $item->update(['is_visible' => ! $item->is_visible]);
        $this->menuApplied = false;
    }

    public function applyMenu(): void
    {
        abort_unless($this->menuId, 422, 'Guarda primeiro o menu antes de o aplicar no site.');

        $settings = $this->site->settings ?? [];
        data_set($settings, 'menu_applied_at', now()->toIso8601String());
        $this->site->update(['settings' => $settings]);
        $this->site->refresh();
        $this->menuApplied = true;
        session()->flash('menu-applied', 'A navegação foi aplicada no site.');
    }

    private function ensureRequiredMenuItems(): void
    {
        $menu = $this->menuId ? $this->site->menus()->find($this->menuId) : null;

        if (! $menu) {
            $menu = $this->site->menus()->create([
                'name' => $this->menuName,
                'location' => 'header',
            ]);
            $this->menuId = $menu->id;
        }

        foreach ($this->availablePages() as $page) {
            $item = $menu->items()->where('site_page_id', $page->id)->first();

            if (! $item) {
                $item = $menu->items()->create([
                    'site_page_id' => $page->id,
                    'parent_id' => null,
                    'label' => $this->pageLabel($page->slug),
                    'url' => null,
                    'target' => '_self',
                    'sort_order' => match ($page->slug) {
                        'home' => 0,
                        'about' => 1,
                        'contact' => 2,
                        default => 99,
                    },
                    'is_visible' => true,
                ]);
            }

            $this->requiredItemIds[$page->slug] = $item->id;
            $this->requiredLabels[$page->slug] = $item->label;
        }
    }

    private function availablePages()
    {
        return $this->site->pages()
            ->whereIn('slug', ['home', 'about', 'contact'])
            ->orderBy('sort_order')
            ->get(['id', 'name', 'slug']);
    }

    public function updatedRequiredLabels(string $value, string $key): void
    {
        if (! in_array($key, ['home', 'about', 'contact'], true)) {
            return;
        }

        $itemId = $this->requiredItemIds[$key] ?? null;

        if (! $itemId) {
            return;
        }

        $label = trim($value);

        if ($label === '') {
            $this->requiredLabels[$key] = $this->pageLabel($key);
            return;
        }

        $menu = $this->site->menus()->findOrFail($this->menuId);
        $menu->items()->whereKey($itemId)->update(['label' => $label]);
        $this->menuApplied = false;
    }

    public function pageLabel(string $slug): string
    {
        return match ($slug) {
            'home' => 'Início',
            'about' => 'Sobre mim',
            'contact' => 'Contactos',
            default => $slug,
        };
    }

    public function render(): mixed
    {
        $menu = $this->menuId ? $this->site->menus()->with('items.children')->find($this->menuId) : null;

        return view('livewire.menu-manager', [
            'pages' => $this->availablePages(),
            'allPages' => $this->site->pages()->orderBy('sort_order')->get(['id', 'name', 'slug', 'status', 'is_homepage']),
            'items' => $menu?->items ?? collect(),
            'menus' => collect(),
        ]);
    }
}
