<?php

namespace App\Livewire;

use App\Livewire\Concerns\InteractsWithSiteContext;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class StoreSettings extends Component
{
    use InteractsWithSiteContext;

    public string $storeName = '';

    public string $whatsappNumber = '';

    public string $address = '';

    public string $deliveryFee = '0.00';

    public function mount(): void
    {
        $site = $this->currentSite();
        $config = $site->storeConfig()->first();

        if ($config !== null) {
            $this->storeName = $config->store_name;
            $this->whatsappNumber = $config->whatsapp_number;
            $this->address = $config->address ?? '';
            $this->deliveryFee = (string) $config->delivery_fee;

            return;
        }

        $this->storeName = $site->name;
        $this->whatsappNumber = $site->phone ?? '';
        $this->address = $site->address ?? '';
    }

    public function save(): void
    {
        $validated = $this->validate([
            'storeName' => ['required', 'string', 'max:255'],
            'whatsappNumber' => ['required', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:1000'],
            'deliveryFee' => ['required', 'numeric', 'min:0'],
        ]);

        $site = $this->currentSite();

        $site->storeConfig()->updateOrCreate([], [
            'store_name' => $validated['storeName'],
            'whatsapp_number' => $validated['whatsappNumber'],
            'address' => $validated['address'],
            'delivery_fee' => $validated['deliveryFee'],
        ]);

        $site->update([
            'name' => $validated['storeName'],
            'phone' => $validated['whatsappNumber'],
            'address' => $validated['address'],
        ]);

        session()->flash('status', 'Configurações salvas com sucesso.');
    }

    public function render(): mixed
    {
        return view('components.⚡store-settings');
    }
}
