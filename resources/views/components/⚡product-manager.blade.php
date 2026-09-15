<div class="space-y-8">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-sm font-medium text-stone-500">Administração</p>
            <h1 class="mt-1 text-2xl font-semibold tracking-tight text-stone-950">Produtos</h1>
            <p class="mt-1 text-sm text-stone-500">Categorias e produtos do site atual, num único lugar.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('categories') }}" wire:navigate class="rounded-lg border border-stone-300 bg-white px-4 py-2 text-sm font-semibold text-stone-700 transition hover:border-stone-400 hover:bg-stone-50 hover:text-stone-900">Gerir categorias</a>
            <button type="button" wire:click="$set('editingProductId', null)" x-data x-on:click="$dispatch('open-product-form')" class="rounded-lg bg-stone-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-stone-700">Novo produto</button>
        </div>
    </div>

    @if (session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">{{ session('status') }}</div>
    @endif

    <section class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
        <div class="border-b border-stone-200 px-6 py-5">
            <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="flex size-9 items-center justify-center rounded-lg bg-amber-50 text-amber-700">⌂</span>
                        <h2 class="text-lg font-semibold text-stone-950">Categorias do site</h2>
                    </div>
                    <p class="mt-1 text-sm text-stone-500">Estas são as categorias existentes no site atual e o número de produtos associado a cada uma.</p>
                </div>
                <span class="inline-flex w-fit items-center rounded-full bg-stone-100 px-3 py-1 text-xs font-semibold text-stone-600">
                    {{ $categories->count() }} {{ $categories->count() === 1 ? 'categoria' : 'categorias' }}
                </span>
            </div>
        </div>

        <div class="p-6">
            @if ($categories->isEmpty())
                <div class="rounded-xl border border-dashed border-stone-300 bg-stone-50 px-6 py-10 text-center">
                    <p class="font-semibold text-stone-800">Ainda não existem categorias neste site.</p>
                    <p class="mt-1 text-sm text-stone-500">Cria uma categoria antes de adicionares produtos.</p>
                    <a href="{{ route('categories') }}" wire:navigate class="mt-4 inline-flex rounded-lg bg-stone-900 px-4 py-2 text-sm font-semibold text-white hover:bg-stone-700">Criar categoria</a>
                </div>
            @else
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach ($categories as $category)
                        <div wire:key="category-{{ $category->id }}" class="group rounded-xl border border-stone-200 bg-stone-50 p-4 transition hover:border-amber-300 hover:bg-amber-50/40">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h3 class="truncate font-semibold text-stone-900">{{ $category->name }}</h3>
                                    <p class="mt-1 text-xs text-stone-500">/{{ $category->slug }}</p>
                                </div>
                                <span class="shrink-0 rounded-full bg-white px-2.5 py-1 text-xs font-semibold text-stone-600 ring-1 ring-stone-200">
                                    {{ $category->products_count }} {{ $category->products_count === 1 ? 'produto' : 'produtos' }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <section class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
        <div class="border-b border-stone-200 px-6 py-5">
            <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                <div>
                    <h2 class="text-lg font-semibold text-stone-950">Produtos</h2>
                    <p class="mt-1 text-sm text-stone-500">Todos os produtos associados às categorias deste site.</p>
                </div>
                <span class="inline-flex w-fit items-center rounded-full bg-stone-100 px-3 py-1 text-xs font-semibold text-stone-600">
                    {{ $products->count() }} {{ $products->count() === 1 ? 'produto' : 'produtos' }}
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] text-left text-sm">
                <thead class="border-b border-stone-200 bg-stone-50 text-xs uppercase tracking-wide text-stone-500">
                    <tr>
                        <th class="px-6 py-3">Foto</th>
                        <th class="px-6 py-3">Produto</th>
                        <th class="px-6 py-3">Categoria</th>
                        <th class="px-6 py-3">Preço</th>
                        <th class="px-6 py-3">Stock</th>
                        <th class="px-6 py-3">Estado</th>
                        <th class="px-6 py-3 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($products as $product)
                        <tr wire:key="product-{{ $product->id }}" class="transition hover:bg-stone-50/70">
                            <td class="px-6 py-3">
                                @if ($product->image_url)
                                    <img src="{{ Storage::disk('public')->url($product->image_url) }}" alt="{{ $product->name }}" class="size-12 rounded-lg object-cover">
                                @else
                                    <div class="flex size-12 items-center justify-center rounded-lg bg-stone-100 text-xs text-stone-400">Sem foto</div>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                <div class="font-semibold text-stone-900">{{ $product->name }}</div>
                                @if ($product->description)
                                    <div class="mt-1 max-w-xs truncate text-xs text-stone-500">{{ $product->description }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                @if ($product->category)
                                    <span class="inline-flex rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-800">{{ $product->category->name }}</span>
                                @else
                                    <span class="text-stone-400">Sem categoria</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 font-medium text-stone-700">€ {{ number_format((float) $product->price, 2, ',', '.') }}</td>
                            <td class="px-6 py-3">
                                <span class="font-medium {{ $product->stock <= $product->minimum_stock ? 'text-red-600' : 'text-stone-600' }}">{{ $product->stock }}</span>
                            </td>
                            <td class="px-6 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $product->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-stone-100 text-stone-500' }}">
                                    {{ $product->is_active ? 'Ativo' : 'Inativo' }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-right">
                                <button type="button" wire:click="edit({{ $product->id }})" class="mr-3 font-medium text-stone-700 hover:text-stone-950">Editar</button>
                                <button type="button" wire:click="delete({{ $product->id }})" wire:confirm="Eliminar este produto?" class="font-medium text-red-600 hover:text-red-800">Eliminar</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <p class="font-semibold text-stone-800">Ainda não existem produtos.</p>
                                <p class="mt-1 text-sm text-stone-500">Depois de criares as categorias, adiciona aqui os produtos correspondentes.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div x-data="{ open: false }" x-on:open-product-form.window="open = true" x-on:keydown.escape.window="open = false" x-cloak>
        <div x-show="open" x-transition.opacity class="fixed inset-0 z-50 bg-black/40" x-on:click="open = false"></div>
        <div x-show="open" x-transition class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto p-4 sm:p-8">
            <form wire:submit="save" x-on:click.stop class="my-4 w-full max-w-3xl rounded-2xl border border-stone-200 bg-white p-6 shadow-2xl sm:my-8">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-stone-400">Catálogo</p>
                        <h2 class="mt-1 text-xl font-semibold text-stone-950">{{ $editingProductId ? 'Editar produto' : 'Adicionar produto' }}</h2>
                    </div>
                    <button type="button" x-on:click="open = false" class="flex size-9 items-center justify-center rounded-lg text-xl text-stone-400 hover:bg-stone-100 hover:text-stone-900" aria-label="Fechar">&times;</button>
                </div>

                <div class="mt-6 grid gap-5 md:grid-cols-2">
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
                    <label class="flex items-center gap-2 text-sm text-stone-700"><input wire:model="isActive" type="checkbox" class="rounded border-stone-300 text-stone-900 focus:ring-stone-500"> Produto ativo</label>
                </div>

                <div class="mt-6 flex justify-end gap-2 border-t border-stone-200 pt-5">
                    <button type="button" x-on:click="open = false" class="rounded-lg border border-stone-300 bg-white px-4 py-2 text-sm font-semibold text-stone-700 hover:bg-stone-50">Cancelar</button>
                    <button type="submit" class="rounded-lg bg-stone-900 px-4 py-2 text-sm font-semibold text-white hover:bg-stone-700" wire:loading.attr="disabled">Guardar produto</button>
                </div>
            </form>
        </div>
    </div>
</div>
