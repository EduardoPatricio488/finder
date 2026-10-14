<?php

namespace App\Livewire;

use App\Livewire\Concerns\InteractsWithSiteContext;
use App\Models\Site;
use App\Support\SiteContext;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class AdminDashboard extends Component
{
    use InteractsWithSiteContext;

    public ?string $selectedSiteId = null;

    public array $modelContent = [];

    public function mount(): void
    {
        // A gestão abre sempre com todos os websites. O filtro é opcional.
        $this->selectedSiteId = request()->query('site') ? (string) request()->query('site') : null;

        if ($this->selectedSiteId !== null) {
            $this->loadModelContent();
        }
    }

    public function updatedSelectedSiteId(): void
    {
        if (blank($this->selectedSiteId)) {
            $this->selectedSiteId = null;
            $this->modelContent = [];

            return;
        }

        $site = $this->selectedSite();
        session()->put('current_site_id', $site->id);
        $this->loadModelContent();
    }

    private function manageableSites()
    {
        return SiteContext::manageableSitesQuery(auth()->user())
            ->with('plan')
            ->orderBy('sort_order')
            ->orderBy('name');
    }

    private function selectedSite(): Site
    {
        abort_if(blank($this->selectedSiteId), 404);

        return $this->manageableSites()->whereKey((int) $this->selectedSiteId)->firstOrFail();
    }

    private function loadModelContent(): void
    {
        $site = $this->selectedSite();
        $this->modelContent = data_get($site->settings, 'model_content', []);

        foreach ($this->modelProfile($site)['fields'] as $field) {
            $key = (string) ($field['key'] ?? '');
            if ($key !== '' && ! array_key_exists($key, $this->modelContent)) {
                $this->modelContent[$key] = '';
            }
        }
    }

    public function saveModelContent(): void
    {
        $site = $this->selectedSite();
        $profile = $this->modelProfile($site);
        $rules = [];

        foreach ($profile['fields'] as $field) {
            $key = (string) ($field['key'] ?? '');
            if ($key === '') {
                continue;
            }

            $rules["modelContent.{$key}"] = ($field['required'] ?? false)
                ? ['required', 'string', 'max:5000']
                : ['nullable', 'string', 'max:5000'];

            if (($field['type'] ?? 'text') === 'email') {
                $rules["modelContent.{$key}"][] = 'email';
            }

            if (($field['type'] ?? 'text') === 'url') {
                $rules["modelContent.{$key}"][] = 'url';
            }
        }

        $this->validate($rules);

        $settings = $site->settings ?? [];
        $settings['model_content'] = collect($this->modelContent)
            ->map(fn ($value) => is_string($value) ? trim($value) : $value)
            ->all();

        $site->update(['settings' => $settings]);

        session()->flash('model-content-saved', 'Conteúdo do modelo guardado com sucesso.');
    }

    private function modelProfile(?Site $site = null): array
    {
        $site ??= $this->selectedSite();
        $type = (string) $site->type;

        return config("website.model_profiles.{$type}", [
            'label' => config("website.types.{$type}.label", 'Website'),
            'description' => 'Configura os conteúdos específicos deste website.',
            'icon' => 'globe-alt',
            'fields' => [],
            'sidebar' => [],
        ]);
    }

    public function render(): mixed
    {
        $sites = $this->manageableSites()->get();

        if (blank($this->selectedSiteId)) {
            $siteSummaries = $sites->map(function (Site $site): array {
                $validOrders = $site->orders()->where('status', '!=', 'cancelada');

                return [
                    'site' => $site,
                    'products' => $site->products()->count(),
                    'activeProducts' => $site->products()->where('is_active', true)->count(),
                    'orders' => (clone $validOrders)->count(),
                    'todaySales' => (clone $validOrders)->whereDate('sold_at', today())->count(),
                    'todayRevenue' => (float) (clone $validOrders)->whereDate('sold_at', today())->sum('total'),
                    'lowStock' => $site->products()->whereColumn('stock', '<=', 'minimum_stock')->count(),
                    'members' => $site->members()->count(),
                ];
            });

            $recentUsers = $sites
                ->flatMap(fn (Site $site) => $site->members()->latest()->limit(5)->get())
                ->sortByDesc('created_at')
                ->take(5)
                ->values();

            $lowStockProducts = $sites
                ->flatMap(fn (Site $site) => $site->products()
                    ->whereColumn('stock', '<=', 'minimum_stock')
                    ->orderBy('stock')
                    ->limit(5)
                    ->get())
                ->sortBy('stock')
                ->take(5)
                ->values();

            return view('livewire.admin-dashboard', [
                'allSitesMode' => true,
                'sites' => $sites,
                'siteSummaries' => $siteSummaries,
                'totalSites' => $sites->count(),
                'totalProducts' => $siteSummaries->sum('products'),
                'activeProducts' => $siteSummaries->sum('activeProducts'),
                'totalOrders' => $siteSummaries->sum('orders'),
                'totalUsers' => $siteSummaries->sum('members'),
                'revenue' => $siteSummaries->sum('todayRevenue'),
                'sales' => $siteSummaries->sum('todaySales'),
                'lowStockProducts' => $lowStockProducts,
                'recentUsers' => $recentUsers,
            ]);
        }

        $site = $this->selectedSite();
        $profile = $this->modelProfile($site);
        $fields = $profile['fields'] ?? [];
        $filled = collect($fields)->filter(function (array $field): bool {
            $value = $this->modelContent[$field['key']] ?? '';

            return filled($value);
        })->count();

        $memberCount = $site->members()->count();
        $ownerIsMember = $site->owner_id !== null
            && $site->members()->whereKey($site->owner_id)->exists();

        return view('livewire.admin-dashboard', [
            'allSitesMode' => false,
            'sites' => $sites,
            'site' => $site,
            'activeProducts' => $site->products()->where('is_active', true)->count(),
            'totalProducts' => $site->products()->count(),
            'totalUsers' => $memberCount + (($site->owner_id !== null && ! $ownerIsMember) ? 1 : 0),
            'recentUsers' => $site->members()->latest()->limit(5)->get(),
            'revenue' => (float) $site->orders()->whereDate('sold_at', today())->where('status', '!=', 'cancelada')->sum('total'),
            'sales' => $site->orders()->whereDate('sold_at', today())->where('status', '!=', 'cancelada')->count(),
            'lowStockProducts' => $site->products()->whereColumn('stock', '<=', 'minimum_stock')->orderBy('stock')->limit(5)->get(),
            'modelProfile' => $profile,
            'modelFields' => $fields,
            'modelFilled' => $filled,
            'modelTotal' => count($fields),
        ]);
    }
}
