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

    public string $menuName = 'Menu principal';

    public string $menuLocation = 'header';

    public string $label = '';

    public string $url = '';

    public ?int $pageId = null;

    public ?int $parentId = null;

    public string $target = '_self';

    public bool $isVisible = true;

    public bool $menuApplied = false;

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
    }

    public function saveMenu(): void
    {
        $this->validate([
            'menuName' => ['required', 'string', 'max:100'],
            'menuLocation' => ['required', 'string', 'max:40'],
        ]);

        $menu = $this->menuId
            ? $this->site->menus()->findOrFail($this->menuId)
            : $this->site->menus()->create(['name' => $this->menuName, 'location' => $this->menuLocation]);

        $menu->update(['name' => $this->menuName, 'location' => $this->menuLocation]);
        $this->menuId = $menu->id;
        $this->menuApplied = false;
        session()->flash('status', 'Menu guardado. Agora podes aplicar as alterações no site.');
    }

    public function addItem(): void
    {
        $this->validate([
            'label' => ['required', 'string', 'max:100'],
            'pageId' => ['required', 'integer', 'exists:site_pages,id'],
            'parentId' => ['nullable', 'integer'],
            'target' => ['required', 'in:_self,_blank'],
        ]);

        if (! $this->menuId) {
            $this->saveMenu();
        }

        $menu = $this->site->menus()->findOrFail($this->menuId);
        $page = $this->pageId ? $this->site->pages()->findOrFail($this->pageId) : null;
        $parent = $this->parentId ? $menu->items()->findOrFail($this->parentId) : null;

        if ($this->pageId) {
            abort_unless(in_array($this->pageId, $this->availablePages()->pluck('id')->all(), true), 422, 'Esta página não está disponível na navegação.');
        }

        $menu->items()->create([
            'site_page_id' => $page?->id,
            'parent_id' => $parent?->id,
            'label' => $this->label,
            'url' => null,
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

    private function availablePages()
    {
        return $this->site->pages()
            ->whereIn('slug', ['home', 'about', 'contact'])
            ->orderBy('sort_order')
            ->get(['id', 'name', 'slug']);
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
        ]);
    }
}
