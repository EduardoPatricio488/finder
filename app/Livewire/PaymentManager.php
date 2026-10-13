<?php

namespace App\Livewire;

use App\Livewire\Concerns\InteractsWithSiteContext;
use App\Models\Payment;
use App\Services\StoreMailService;
use App\Support\SiteContext;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class PaymentManager extends Component
{
    use InteractsWithSiteContext;

    public string $status = '';

    public function markAsReceived(int $paymentId): void
    {
        $siteIds = SiteContext::manageableSitesQuery(auth()->user())->pluck('id');
        $payment = Payment::query()
            ->whereIn('site_id', $siteIds)
            ->with('order.customer')
            ->findOrFail($paymentId);

        $payment->update(['status' => 'recebido', 'paid_at' => now()]);

        if ($payment->order) {
            $payment->order->update(['status' => 'pago']);
            app(StoreMailService::class)->paymentReceived($payment->order->refresh()->load('customer'));
        }

        session()->flash('status', 'Pagamento marcado como recebido.');
    }

    public function render(): mixed
    {
        $siteIds = SiteContext::manageableSitesQuery(auth()->user())->pluck('id');

        return view('livewire.payment-manager', [
            'payments' => Payment::query()
                ->whereIn('site_id', $siteIds)
                ->with(['order.customer', 'order.site'])
                ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
                ->latest()
                ->get(),
        ]);
    }
}
