<?php

namespace App\Livewire;

use App\Livewire\Concerns\InteractsWithSiteContext;
use App\Models\Order;
use App\Support\SiteContext;
use App\Services\FinderNotificationService;
use App\Services\StoreMailService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class OrderManager extends Component
{
    use InteractsWithSiteContext;

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

    public function setStatus(int $orderId, string $status): void
    {
        abort_unless(in_array($status, ['pendente', 'pago', 'em_preparacao', 'enviado', 'entregue'], true), 422);

        $siteIds = SiteContext::manageableSitesQuery(auth()->user())->pluck('id');
        $order = Order::query()->whereIn('site_id', $siteIds)->with(['customer', 'site'])->findOrFail($orderId);
        $site = $order->site;
        abort_unless($site !== null && $site->isManageableBy(auth()->user()), 404);
        $order->update(['status' => $status]);

        app(FinderNotificationService::class)->saleChanged(
            $site,
            'A encomenda #'.$order->order_number.' mudou para "'.$status.'".'
        );

        app(StoreMailService::class)->orderStatusChanged($order->refresh()->load('customer'));

        if ($status === 'pago') {
            app(StoreMailService::class)->paymentReceived($order->refresh()->load('customer'));
        }
    }

    public function isStepComplete(Order $order, string $step): bool
    {
        $steps = ['criada', 'pago', 'em_preparacao', 'enviado', 'entregue'];
        $current = $order->status === 'pendente' ? 'criada' : $order->status;
        $currentIndex = array_search($current, $steps, true);
        $stepIndex = array_search($step, $steps, true);

        if ($stepIndex === false) {
            return false;
        }

        return $currentIndex !== false && $currentIndex >= $stepIndex;
    }

    public function render(): mixed
    {
        $availableSites = SiteContext::manageableSitesQuery(auth()->user())
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $siteIds = $availableSites->modelKeys();

        $orders = Order::query()
            ->whereIn('site_id', $siteIds)
            ->when($this->catalogSiteId !== '', fn ($query) => $query->where('site_id', (int) $this->catalogSiteId))
            ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
            ->with(['customer', 'items', 'payments', 'site'])
            ->latest('sold_at')
            ->get();

        return view('livewire.order-manager', [
            'orders' => $orders,
            'availableSites' => $availableSites,
        ]);
    }
}
