<?php

namespace App\Livewire;

use App\Models\Site;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Menus')]
class MenuManager extends Component
{
    public Site $site;

    public ?int $menuId = null;

    /** @var array<int, string> */
    public array $menuNames = [];

    public string $menuName = 'Menu principal';

    public string $menuLocation = 'header';

    public string $label = '';

    public string $url = '';

    public ?int $pageId = null;

    public ?int $parentId = null;

    public string $target = '_self';

    public bool $isVisible = true;

    public bool $menuApplied = false;

    /** @var array<string, int|null> */
    public array $requiredItemIds = [];

    /** @var array<string, string> */
    public array $requiredLabels = [];

    public function mount(Site $site): void
    {
        abort_unless($site->isManageableBy(auth()->user()), 403);
        $this->site = $site;
        $this->menuApplied = filled(data_get($site->settings, 'menu_applied_at'));
        $menu = $site->menus()->first();

        if ($menu) {
            $this->menuId = $menu->id;
            $this->menuName = $menu->name;
            $this->menuLocation = $menu->location ?: 'header';
        }

        $this->ensureRequiredMenuItems();
        $this->syncMenuNames();
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

        $currentMenu = $this->menuId ? $this->site->menus()->find($this->menuId) : null;
        $menu = $currentMenu && $currentMenu->name === $this->menuName
            ? $currentMenu
            : $this->site->menus()->where('name', $this->menuName)->first();

        if (! $menu) {
            $menu = $this->site->menus()->create([
                'name' => $this->menuName,
                'location' => $this->menuLocation,
            ]);
        } else {
            $menu->update([
                'name' => $this->menuName,
                'location' => $this->menuLocation,
            ]);
        }

        $this->menuId = $menu->id;
        $this->ensureRequiredMenuItems();
        $this->menuApplied = false;
        $this->syncMenuNames();
        session()->flash('status', 'Menu guardado. Agora podes aplicar as alterações no site.');
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

    private function syncMenuNames(): void
    {
        $this->menuNames = $this->site->menus()
            ->orderBy('id')
            ->pluck('name', 'id')
            ->all();
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
            'items' => $menu?->items ?? collect(),
            'menus' => $this->site->menus()->with('items.page')->orderBy('id')->get(),
        ]);
    }
}
