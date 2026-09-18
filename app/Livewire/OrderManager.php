<?php

namespace App\Livewire;

use App\Livewire\Concerns\InteractsWithSiteContext;
use App\Services\FinderNotificationService;
use App\Services\StoreMailService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class OrderManager extends Component
{
    use InteractsWithSiteContext;

    public string $status = '';

    public function setStatus(int $orderId, string $status): void
    {
        abort_unless(in_array($status, ['pendente', 'pago', 'em_preparacao', 'enviado', 'entregue'], true), 422);
        $order = $this->currentSite()->orders()->with('customer')->findOrFail($orderId);
        $order->update(['status' => $status]);
        app(FinderNotificationService::class)->saleChanged($this->currentSite(), 'A encomenda #'.$order->order_number.' mudou para "'.$status.'".');
        app(StoreMailService::class)->orderStatusChanged($order->refresh()->load('customer'));

        if ($status === 'pago') {
            app(StoreMailService::class)->paymentReceived($order->refresh()->load('customer'));
        }
    }

    public function render(): mixed
    {
        return view('livewire.order-tracking', ['orders' => $this->currentSite()->orders()->with(['customer', 'items'])->when($this->status !== '', fn ($query) => $query->where('status', $this->status))->latest('sold_at')->get()]);
    }
}
