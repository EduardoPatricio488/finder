<?php

namespace App\Livewire;

use App\Livewire\Concerns\InteractsWithSiteContext;
use App\Models\Promotion;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class PromotionManager extends Component
{
    use InteractsWithSiteContext;

    public ?int $editingPromotionId = null;

    public string $name = '';

    public string $discountType = 'percentagem';

    public string $discountValue = '';

    public string $startsAt = '';

    public string $endsAt = '';

    public array $productIds = [];

    public function save(): void
    {
        $site = $this->currentSite();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'discountType' => ['required', 'in:percentagem,fixo'],
            'discountValue' => ['required', 'numeric', 'gt:0'],
            'startsAt' => ['required', 'date'],
            'endsAt' => ['required', 'date', 'after:startsAt'],
            'productIds' => ['required', 'array', 'min:1'],
            'productIds.*' => ['integer', 'exists:products,id'],
        ]);

        $productIds = collect($validated['productIds'])->map(fn ($id): int => (int) $id)->all();

        abort_unless($site->products()->whereKey($productIds)->count() === count(array_unique($productIds)), 404);

        $promotion = Promotion::query()->updateOrCreate(
            ['id' => $this->editingPromotionId, 'site_id' => $site->id],
            [
                'site_id' => $site->id,
                'name' => $validated['name'],
                'discount_type' => $validated['discountType'],
                'discount_value' => $validated['discountValue'],
                'starts_at' => $validated['startsAt'],
                'ends_at' => $validated['endsAt'],
            ],
        );
        $promotion->products()->sync($productIds);

        $this->resetForm();
        session()->flash('status', 'Promoção guardada com sucesso.');
    }

    public function edit(int $promotionId): void
    {
        $promotion = $this->currentSite()->promotions()->with('products')->findOrFail($promotionId);
        $this->editingPromotionId = $promotion->id;
        $this->name = $promotion->name;
        $this->discountType = $promotion->discount_type;
        $this->discountValue = (string) $promotion->discount_value;
        $this->startsAt = $promotion->starts_at->format('Y-m-d\\TH:i');
        $this->endsAt = $promotion->ends_at->format('Y-m-d\\TH:i');
        $this->productIds = $promotion->products->pluck('id')->map(fn (int $id): string => (string) $id)->all();
    }

    public function delete(int $promotionId): void
    {
        $this->currentSite()->promotions()->findOrFail($promotionId)->delete();
        session()->flash('status', 'Promoção eliminada.');
    }

    public function resetForm(): void
    {
        $this->reset(['editingPromotionId', 'name', 'discountValue', 'startsAt', 'endsAt', 'productIds']);
        $this->discountType = 'percentagem';
        $this->resetValidation();
    }

    public function render(): mixed
    {
        return view('livewire.promotion-manager', [
            'promotions' => $this->currentSite()->promotions()->withCount('products')->latest()->get(),
            'products' => $this->currentSite()->products()->orderBy('name')->get(),
        ]);
    }
}
