<?php

namespace App\Livewire;

use App\Livewire\Concerns\InteractsWithSiteContext;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Reports extends Component
{
    use InteractsWithSiteContext;

    public string $period = '30';

    public function render(): mixed
    {
        $from = match ($this->period) {
            'today' => now()->startOfDay(),
            '7' => now()->subDays(6)->startOfDay(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
            default => now()->subDays(29)->startOfDay(),
        };

        $orders = $this->currentSite()->orders()->where('sold_at', '>=', $from)->where('status', '!=', 'cancelada');

        return view('livewire.reports', [
            'sales' => $orders->count(),
            'revenue' => (float) $orders->sum('total'),
            'averageTicket' => (float) $orders->avg('total'),
            'customers' => $orders->whereNotNull('customer_id')->distinct('customer_id')->count('customer_id'),
        ]);
    }
}
