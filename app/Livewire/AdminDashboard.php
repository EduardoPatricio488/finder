<?php

namespace App\Livewire;

use App\Livewire\Concerns\InteractsWithSiteContext;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class AdminDashboard extends Component
{
    use InteractsWithSiteContext;

    public function render(): mixed
    {
        $site = $this->currentSite();

        return view('livewire.admin-dashboard', [
            'site' => $site,
            'activeProducts' => $site->products()->where('is_active', true)->count(),
            'totalProducts' => $site->products()->count(),
            'totalUsers' => $site->members()->count() + ($site->owner_id === null ? 0 : 1),
            'recentUsers' => $site->members()->latest()->limit(5)->get(),
            'revenue' => (float) $site->orders()->whereDate('sold_at', today())->where('status', '!=', 'cancelada')->sum('total'),
            'sales' => $site->orders()->whereDate('sold_at', today())->where('status', '!=', 'cancelada')->count(),
            'lowStockProducts' => $site->products()->whereColumn('stock', '<=', 'minimum_stock')->orderBy('stock')->limit(5)->get(),
        ]);
    }
}
