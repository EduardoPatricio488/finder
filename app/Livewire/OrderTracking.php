<?php

namespace App\Livewire;

use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.customer')]
class OrderTracking extends Component
{
    public function render(): mixed
    {
        return view('livewire.order-tracking', [
            'orders' => Order::query()
                ->with(['customer', 'items', 'payments'])
                ->whereHas('customer', fn ($query) => $query->where('email', auth()->user()->email))
                ->latest('sold_at')
                ->get(),
        ]);
    }

    public function isStepComplete(Order $order, string $step): bool
    {
        $steps = ['criada', 'pago', 'em_preparacao', 'enviado', 'entregue'];
        $current = $order->status === 'pendente' ? 'criada' : $order->status;
        $currentIndex = array_search($current, $steps, true);

        if ($step === 'pago' && $order->payments->contains(fn ($payment): bool => $payment->status === 'recebido')) {
            return true;
        }

        return $currentIndex !== false && $currentIndex >= array_search($step, $steps, true);
    }
}
