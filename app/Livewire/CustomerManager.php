<?php

namespace App\Livewire;

use App\Livewire\Concerns\InteractsWithSiteContext;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class CustomerManager extends Component
{
    use InteractsWithSiteContext;

    public string $search = '';

    public function render(): mixed
    {
        $customers = $this->currentSite()->customers()
            ->withCount('orders')
            ->withSum('orders', 'total')
            ->withMax('orders', 'sold_at')
            ->when($this->search !== '', fn ($query) => $query->where('name', 'like', '%'.$this->search.'%')->orWhere('email', 'like', '%'.$this->search.'%'))
            ->latest()
            ->get();

        return view('livewire.customer-manager', compact('customers'));
    }
}
