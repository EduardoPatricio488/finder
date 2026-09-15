<?php

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public bool $showProductForm = false;

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

    public function openForm(): void
    {
        $this->resetForm();
        $this->showProductForm = true;
    }

    public function closeForm(): void
    {
        $this->resetForm();
        $this->showProductForm = false;
    }

    public function edit(int $productId): void
    {
        $product = Product::query()->findOrFail($productId);

        $this->editingProductId = $product->id;
        $this->name = $product->name;
        $this->categoryId = $product->category_id;
        $this->description = $product->description ?? '';
        $this->price = (string) $product->price;
        $this->isActive = $product->is_active;
        $this->stock = $product->stock;
        $this->minimumStock = $product->minimum_stock;
        $this->image = null;
        $this->showProductForm = true;
    }

    public function save(): void
    {
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

        $product = $this->editingProductId === null
            ? new Product
            : Product::query()->findOrFail($this->editingProductId);

        $oldImage = $product->image_url;
        $product->category_id = $validated['categoryId'];
        $product->name = $validated['name'];
        $product->slug = Str::slug($validated['name']);
        $product->description = $validated['description'];
        $product->price = $validated['price'];
        $product->is_active = $validated['isActive'];
        $product->stock = $validated['stock'];
        $product->minimum_stock = $validated['minimumStock'];

        if ($this->image !== null) {
            $product->image_url = $this->image->store('products', 'public');
        }

        $product->save();

        if ($oldImage !== null && $this->image !== null) {
            Storage::disk('public')->delete($oldImage);
        }

        $this->closeForm();
        $this->loadData();
        session()->flash('status', 'Produto salvo com sucesso.');
    }

    public function delete(int $productId): void
    {
        $product = Product::query()->findOrFail($productId);
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
        $this->products = Product::query()->with('category')->latest()->get();
        $this->categories = Category::query()->orderBy('name')->get();
    }
};
?>

    <div class="space-y-8">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <p class="text-sm font-medium text-stone-500">Administração</p>
                <h1 class="mt-1 text-2xl font-semibold tracking-tight">Produtos</h1>
            </div>
            <button type="button" wire:click="openForm" class="rounded-lg bg-stone-900 px-4 py-2 text-sm font-semibold text-white hover:bg-stone-700">
                Novo produto
            </button>
        </div>

        @if (session('status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">
                {{ session('status') }}
            </div>
        @endif

        @if ($this->showProductForm)
            <form wire:submit="save" class="space-y-6 rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold">{{ $editingProductId ? 'Editar produto' : 'Adicionar produto' }}</h2>
                    <button type="button" wire:click="closeForm" class="text-sm font-medium text-stone-500 hover:text-stone-900">
                        Fechar
                    </button>
                </div>
                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <label for="product-name" class="block text-sm font-medium text-stone-700">Nome</label>
                        <input id="product-name" wire:model="name" type="text" class="mt-2 block w-full rounded-lg border-stone-300 px-3 py-2 shadow-sm focus:border-stone-500 focus:ring-stone-500">
                        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="category-id" class="block text-sm font-medium text-stone-700">Categoria</label>
                        <select id="category-id" wire:model="categoryId" class="mt-2 block w-full rounded-lg border-stone-300 px-3 py-2 shadow-sm focus:border-stone-500 focus:ring-stone-500">
                            <option value="">Selecione uma categoria</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('categoryId') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="product-price" class="block text-sm font-medium text-stone-700">Preço</label>
                        <input id="product-price" wire:model="price" type="number" min="0" step="0.01" class="mt-2 block w-full rounded-lg border-stone-300 px-3 py-2 shadow-sm focus:border-stone-500 focus:ring-stone-500">
                        @error('price') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="product-image" class="block text-sm font-medium text-stone-700">Foto</label>
                        <input id="product-image" wire:model="image" type="file" accept="image/*" class="mt-2 block w-full text-sm text-stone-600 file:mr-3 file:rounded-lg file:border-0 file:bg-stone-100 file:px-3 file:py-2 file:font-medium">
                        @error('image') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="product-stock" class="block text-sm font-medium text-stone-700">Stock atual</label>
                        <input id="product-stock" wire:model="stock" type="number" min="0" class="mt-2 block w-full rounded-lg border-stone-300 px-3 py-2 shadow-sm">
                    </div>
                    <div>
                        <label for="product-minimum-stock" class="block text-sm font-medium text-stone-700">Stock mínimo</label>
                        <input id="product-minimum-stock" wire:model="minimumStock" type="number" min="0" class="mt-2 block w-full rounded-lg border-stone-300 px-3 py-2 shadow-sm">
                    </div>
                    <div class="md:col-span-2">
                        <label for="product-description" class="block text-sm font-medium text-stone-700">Descrição</label>
                        <textarea id="product-description" wire:model="description" rows="3" class="mt-2 block w-full rounded-lg border-stone-300 px-3 py-2 shadow-sm focus:border-stone-500 focus:ring-stone-500"></textarea>
                    </div>
                    <label class="flex items-center gap-2 text-sm text-stone-700">
                        <input wire:model="isActive" type="checkbox" class="rounded border-stone-300 text-stone-900 focus:ring-stone-500">
                        Produto ativo
                    </label>
                </div>
                <button type="submit" class="rounded-lg bg-stone-900 px-4 py-2 text-sm font-semibold text-white hover:bg-stone-700" wire:loading.attr="disabled">
                    Guardar produto
                </button>
            </form>
        @endif

        <div class="flex items-center justify-between rounded-xl border border-amber-200 bg-amber-50 p-4 shadow-sm dark:border-amber-900/40 dark:bg-amber-950/20">
            <div>
                <p class="text-sm font-semibold text-stone-900 dark:text-white">Gestão de stock</p>
                <p class="mt-1 text-sm text-stone-600 dark:text-stone-300">Consulta e gere os movimentos e níveis de stock dos produtos.</p>
            </div>
            <a href="{{ route('admin.site.stock', $this->currentSite()) }}" wire:navigate class="shrink-0 rounded-lg bg-stone-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-stone-700 focus:outline-none focus:ring-2 focus:ring-stone-500 focus:ring-offset-2">
                Ir para Stock
            </a>
        </div>

        <div class="overflow-hidden rounded-xl border border-stone-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-stone-200 bg-stone-50 text-xs uppercase tracking-wide text-stone-500">
                        <tr>
                            <th class="px-6 py-3">Foto</th>
                            <th class="px-6 py-3">Nome</th>
                            <th class="px-6 py-3">Categoria</th>
                            <th class="px-6 py-3">Preço</th>
                            <th class="px-6 py-3">Stock</th>
                            <th class="px-6 py-3 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @forelse ($products as $product)
                            <tr wire:key="product-{{ $product->id }}">
                                <td class="px-6 py-3">
                                    @if ($product->image_url)
                                        <img src="{{ Storage::disk('public')->url($product->image_url) }}" alt="{{ $product->name }}" class="size-12 rounded-lg object-cover">
                                    @else
                                        <div class="flex size-12 items-center justify-center rounded-lg bg-stone-100 text-xs text-stone-400">Sem foto</div>
                                    @endif
                                </td>
                                <td class="px-6 py-3 font-medium text-stone-900">{{ $product->name }}</td>
                                <td class="px-6 py-3 text-stone-600">{{ $product->category->name }}</td>
                                <td class="px-6 py-3 text-stone-600">€ {{ number_format((float) $product->price, 2, ',', '.') }}</td>
                                <td class="px-6 py-3"><span class="font-medium {{ $product->stock <= $product->minimum_stock ? 'text-red-600' : 'text-stone-600' }}">{{ $product->stock }}</span></td>
                                <td class="px-6 py-3 text-right">
                                    <button type="button" wire:click="edit({{ $product->id }})" class="mr-3 font-medium text-stone-700 hover:text-stone-950">Editar</button>
                                    <button type="button" wire:click="delete({{ $product->id }})" wire:confirm="Eliminar este produto?" class="font-medium text-red-600 hover:text-red-800">Eliminar</button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-10 text-center text-stone-500">Nenhum produto cadastrado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
