<?php

namespace App\Livewire;

use App\Models\Order;
use App\Livewire\Concerns\InteractsWithSiteContext;
use App\Support\SiteContext;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class SalesManager extends Component
{
    use InteractsWithSiteContext;

    public string $search = '';

    public string $status = '';

    public string $catalogSiteId = '';

    public function updatedCatalogSiteId($siteId): void
    {
        $siteId = (string) $siteId;

        if ($siteId !== '') {
            abort_unless(
                SiteContext::manageableSitesQuery(auth()->user())->whereKey((int) $siteId)->exists(),
                404
            );
        }

        $this->catalogSiteId = $siteId;
    }

    public function render(): mixed
    {
        $availableSites = SiteContext::manageableSitesQuery(auth()->user())
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $siteIds = $availableSites->modelKeys();

        $sales = Order::query()
            ->whereIn('site_id', $siteIds)
            ->when($this->catalogSiteId !== '', fn ($query) => $query->where('site_id', (int) $this->catalogSiteId))
            ->when($this->search !== '', function ($query) {
                $search = trim($this->search);

                $query->where(function ($query) use ($search) {
                    $query->where('order_number', 'like', '%'.$search.'%')
                        ->orWhereHas('customer', fn ($customer) => $customer->where('name', 'like', '%'.$search.'%'));
                });
            })
            ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
            ->with(['customer', 'items', 'site'])
            ->latest('sold_at')
            ->get();

        return view('livewire.sales-manager', [
            'sales' => $sales,
            'availableSites' => $availableSites,
        ]);
    }
}
