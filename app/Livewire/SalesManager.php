<?php

namespace App\Livewire;

use App\Livewire\Concerns\InteractsWithSiteContext;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class SalesManager extends Component
{
    use InteractsWithSiteContext;

    public string $search = '';

    public string $status = '';

    public function render(): mixed
    {
        $sales = $this->currentSite()->orders()
            ->with(['customer', 'items'])
            ->when($this->search !== '', fn ($query) => $query->where('order_number', 'like', '%'.$this->search.'%')->orWhereHas('customer', fn ($customer) => $customer->where('name', 'like', '%'.$this->search.'%')))
            ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
            ->latest('sold_at')
            ->get();

        return view('livewire.sales-manager', compact('sales'));
    }
}
