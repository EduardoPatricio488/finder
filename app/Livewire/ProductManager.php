<?php

namespace App\Livewire;

use App\Livewire\Concerns\InteractsWithSiteContext;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.admin')]
class ProductManager extends Component
{
    use InteractsWithSiteContext;
    use WithFileUploads;

    public ?int $editingProductId = null;

    public string $name = '';

    public ?int $categoryId = null;

    public string $description = '';

    public string $price = '';

    public bool $isActive = true;

    public int $stock = 0;

    public int $minimumStock = 0;

    public $image;

    public $products = [];

    public $categories = [];

    public function mount(): void
    {
        $this->loadData();
    }

    public function edit(int $productId): void
    {
        $product = $this->currentSite()->products()->findOrFail($productId);

        $this->editingProductId = $product->id;
        $this->name = $product->name;
        $this->categoryId = $product->category_id;
        $this->description = $product->description ?? '';
        $this->price = (string) $product->price;
        $this->isActive = $product->is_active;
        $this->stock = $product->stock;
        $this->minimumStock = $product->minimum_stock;
        $this->image = null;
    }

    public function save(): void
    {
        $site = $this->currentSite();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'categoryId' => ['required', 'integer', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'isActive' => ['boolean'],
            'stock' => ['required', 'integer', 'min:0'],
            'minimumStock' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        abort_unless($site->categories()->whereKey($validated['categoryId'])->exists(), 404);

        $product = $this->editingProductId === null
            ? new Product(['site_id' => $site->id])
            : $site->products()->findOrFail($this->editingProductId);
        $oldImage = $product->image_url;

        $product->fill([
            'category_id' => $validated['categoryId'],
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'],
            'price' => $validated['price'],
            'is_active' => $validated['isActive'],
            'stock' => $validated['stock'],
            'minimum_stock' => $validated['minimumStock'],
        ]);

        if ($this->image !== null) {
            $product->image_url = $this->image->store('products', 'public');
        }

        $product->save();

        if ($oldImage !== null && $this->image !== null) {
            Storage::disk('public')->delete($oldImage);
        }

        $this->resetForm();
        $this->loadData();
        session()->flash('status', 'Produto salvo com sucesso.');
    }

    public function delete(int $productId): void
    {
        $product = $this->currentSite()->products()->findOrFail($productId);
        $image = $product->image_url;
        $product->delete();

        if ($image !== null) {
            Storage::disk('public')->delete($image);
        }

        $this->loadData();
        session()->flash('status', 'Produto excluído com sucesso.');
    }

    public function resetForm(): void
    {
        $this->reset(['editingProductId', 'name', 'categoryId', 'description', 'price', 'image', 'stock', 'minimumStock']);
        $this->isActive = true;
        $this->resetValidation();
    }

    private function loadData(): void
    {
        $site = $this->currentSite();

        $this->products = $site->products()->with('category')->latest()->get();
        $this->categories = $site->categories()->orderBy('name')->get();
    }

    public function render(): mixed
    {
        return view('components.⚡product-manager');
    }
}
