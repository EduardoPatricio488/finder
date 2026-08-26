<?php

use App\Models\StoreConfig;
use Livewire\Component;

new class extends Component
{
    public string $storeName = '';

    public string $whatsappNumber = '';

    public string $address = '';

    public string $deliveryFee = '0.00';

    public function mount(): void
    {
        $config = StoreConfig::query()->first();

        if ($config === null) {
            return;
        }

        $this->storeName = $config->store_name;
        $this->whatsappNumber = $config->whatsapp_number;
        $this->address = $config->address ?? '';
        $this->deliveryFee = (string) $config->delivery_fee;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'storeName' => ['required', 'string', 'max:255'],
            'whatsappNumber' => ['required', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:1000'],
            'deliveryFee' => ['required', 'numeric', 'min:0'],
        ]);

        StoreConfig::query()->updateOrCreate(
            ['id' => 1],
            [
                'store_name' => $validated['storeName'],
                'whatsapp_number' => $validated['whatsappNumber'],
                'address' => $validated['address'],
                'delivery_fee' => $validated['deliveryFee'],
            ],
        );

        session()->flash('status', 'Configurações salvas com sucesso.');
    }
};
?>

    <div class="max-w-3xl space-y-8">
        <div>
            <p class="text-sm font-medium text-stone-500">Administração</p>
            <h1 class="mt-1 text-2xl font-semibold tracking-tight">Configuração da loja</h1>
        </div>

        @if (session('status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">
                {{ session('status') }}
            </div>
        @endif

        <form wire:submit="save" class="space-y-6 rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
            <div class="grid gap-6 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label for="store-name" class="block text-sm font-medium text-stone-700">Nome da loja</label>
                    <input id="store-name" wire:model="storeName" type="text" class="mt-2 block w-full rounded-lg border-stone-300 px-3 py-2 shadow-sm focus:border-stone-500 focus:ring-stone-500">
                    @error('storeName') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="whatsapp-number" class="block text-sm font-medium text-stone-700">WhatsApp</label>
                    <input id="whatsapp-number" wire:model="whatsappNumber" type="text" placeholder="5511999999999" class="mt-2 block w-full rounded-lg border-stone-300 px-3 py-2 shadow-sm focus:border-stone-500 focus:ring-stone-500">
                    @error('whatsappNumber') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="delivery-fee" class="block text-sm font-medium text-stone-700">Taxa de entrega</label>
                    <input id="delivery-fee" wire:model="deliveryFee" type="number" min="0" step="0.01" class="mt-2 block w-full rounded-lg border-stone-300 px-3 py-2 shadow-sm focus:border-stone-500 focus:ring-stone-500">
                    @error('deliveryFee') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="md:col-span-2">
                    <label for="address" class="block text-sm font-medium text-stone-700">Endereço</label>
                    <textarea id="address" wire:model="address" rows="3" class="mt-2 block w-full rounded-lg border-stone-300 px-3 py-2 shadow-sm focus:border-stone-500 focus:ring-stone-500"></textarea>
                    @error('address') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
            <button type="submit" class="rounded-lg bg-stone-900 px-4 py-2 text-sm font-semibold text-white hover:bg-stone-700" wire:loading.attr="disabled">
                Guardar configurações
            </button>
        </form>
    </div>
