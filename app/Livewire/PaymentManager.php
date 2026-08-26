<?php

namespace App\Livewire;

use App\Livewire\Concerns\InteractsWithSiteContext;
use App\Services\StoreMailService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class PaymentManager extends Component
{
    use InteractsWithSiteContext;

    public string $status = '';

    public function markAsReceived(int $paymentId): void
    {
        $payment = $this->currentSite()->payments()->with('order.customer')->findOrFail($paymentId);
        $payment->update(['status' => 'recebido', 'paid_at' => now()]);
        $payment->order->update(['status' => 'pago']);
        app(StoreMailService::class)->paymentReceived($payment->order->refresh()->load('customer'));
        session()->flash('status', 'Pagamento marcado como recebido.');
    }

    public function render(): mixed
    {
        return view('livewire.payment-manager', ['payments' => $this->currentSite()->payments()->with('order.customer')->when($this->status !== '', fn ($query) => $query->where('status', $this->status))->latest()->get()]);
    }
}
