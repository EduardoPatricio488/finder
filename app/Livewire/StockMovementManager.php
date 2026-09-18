<?php

namespace App\Livewire;

use App\Livewire\Concerns\InteractsWithSiteContext;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class StockMovementManager extends Component
{
    use InteractsWithSiteContext;

    public ?int $productId = null;

    public string $type = 'entrada';

    public int $quantity = 1;

    public string $note = '';

    public function save(): void
    {
        $data = $this->validate(['productId' => ['required', 'exists:products,id'], 'type' => ['required', 'in:entrada,saida,ajuste'], 'quantity' => ['required', 'integer', 'min:1'], 'note' => ['nullable', 'string', 'max:500']]);
        $site = $this->currentSite();
        $product = $site->products()->findOrFail($data['productId']);
        $delta = $data['type'] === 'saida' ? -$data['quantity'] : $data['quantity'];
        abort_if($product->stock + $delta < 0, 422, 'O stock não pode ficar negativo.');
        $product->increment('stock', $delta);
        $site->stockMovements()->create(['product_id' => $product->id, 'user_id' => auth()->id(), 'type' => $data['type'], 'quantity' => $delta, 'note' => $data['note']]);
        $this->reset(['productId', 'quantity', 'note']);
        $this->quantity = 1;
    }

    public function render(): mixed
    {
        $site = $this->currentSite();

        return view('livewire.stock-movement-manager', ['products' => $site->products()->orderBy('name')->get(), 'movements' => $site->stockMovements()->with(['product', 'user'])->latest()->limit(50)->get()]);
    }
}
