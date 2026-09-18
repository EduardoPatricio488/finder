<div class="min-h-screen bg-zinc-50 dark:bg-zinc-950">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-8">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm font-semibold text-indigo-600 dark:text-indigo-400">Loja</p>
                    <h1 class="mt-2 text-3xl font-bold tracking-tight text-zinc-950 dark:text-white">Produtos</h1>
                    <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">Gere os produtos da empresa, acompanha o stock e mantém o catálogo organizado.</p>
                </div>
                <button type="button" wire:click="openProductModal" class="inline-flex items-center justify-center gap-2 rounded-xl bg-zinc-950 px-5 py-3 text-sm font-bold text-white transition hover:bg-indigo-600 dark:bg-white dark:text-zinc-950 dark:hover:bg-indigo-500 dark:hover:text-white">
                    <flux:icon name="plus" class="size-4" />
                    Adicionar produto
                </button>
            </div>
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-3">
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500">Produtos apresentados</p>
                <p class="mt-2 text-2xl font-bold text-zinc-950 dark:text-white">{{ $products->count() }}</p>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500">Em stock</p>
                <p class="mt-2 text-2xl font-bold text-zinc-950 dark:text-white">{{ $products->where('stock', '>', 0)->count() }}</p>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500">Esgotados</p>
                <p class="mt-2 text-2xl font-bold text-zinc-950 dark:text-white">{{ $products->where('stock', '<=', 0)->count() }}</p>
            </div>
        </div>

        <div class="mt-6 rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex flex-col gap-3 lg:flex-row">
                <div class="relative flex-1">
                    <flux:icon name="magnifying-glass" class="pointer-events-none absolute left-3.5 top-1/2 size-5 -translate-y-1/2 text-zinc-400" />
                    <input wire:model.live.debounce.300ms="search" type="search" placeholder="Pesquisar por nome, descrição ou SKU..." class="w-full rounded-xl border-zinc-200 bg-zinc-50 py-3 pl-11 pr-4 text-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 dark:border-zinc-700 dark:bg-zinc-950">
                </div>
                <select wire:model.live="category" class="rounded-xl border-zinc-200 bg-zinc-50 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950 lg:w-52">
                    <option value="">Todas as categorias</option>
                    @foreach($categories as $item)
                        <option value="{{ $item->slug }}">{{ $item->name }}</option>
                    @endforeach
                </select>
                <select wire:model.live="availability" class="rounded-xl border-zinc-200 bg-zinc-50 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950 lg:w-44">
                    <option value="">Disponibilidade</option>
                    <option value="disponivel">Em stock</option>
                    <option value="esgotado">Esgotado</option>
                </select>
            </div>
            <div class="mt-3 flex flex-wrap gap-3">
                <input wire:model.live="minPrice" type="number" min="0" step=".01" placeholder="Preço mín." class="w-full rounded-xl border-zinc-200 bg-zinc-50 px-4 py-2.5 text-sm dark:border-zinc-700 dark:bg-zinc-950 sm:w-36">
                <input wire:model.live="maxPrice" type="number" min="0" step=".01" placeholder="Preço máx." class="w-full rounded-xl border-zinc-200 bg-zinc-50 px-4 py-2.5 text-sm dark:border-zinc-700 dark:bg-zinc-950 sm:w-36">
                <select wire:model.live="minRating" class="rounded-xl border-zinc-200 bg-zinc-50 px-4 py-2.5 text-sm dark:border-zinc-700 dark:bg-zinc-950">
                    <option value="">Qualquer avaliação</option>
                    <option value="4">4+ estrelas</option>
                    <option value="3">3+ estrelas</option>
                </select>
            </div>
        </div>

        <div class="mt-6 flex items-end justify-between">
            <div>
                <h2 class="text-xl font-bold text-zinc-950 dark:text-white">Catálogo de produtos</h2>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $products->count() }} {{ $products->count() === 1 ? 'produto encontrado' : 'produtos encontrados' }}</p>
            </div>
        </div>

        <div class="mt-5 overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-zinc-50 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:bg-zinc-800/50">
                        <tr>
                            <th class="px-5 py-4">Produto</th>
                            <th class="px-5 py-4">Descrição</th>
                            <th class="px-5 py-4">Categoria</th>
                            <th class="px-5 py-4">Stock</th>
                            <th class="px-5 py-4">Preço</th>
                            <th class="px-5 py-4">
                                <span class="group relative inline-flex cursor-help items-center gap-1">
                                    Avaliação
                                    <flux:icon name="information-circle" class="size-4 text-zinc-400" />
                                    <span class="pointer-events-none absolute bottom-full left-0 z-[9999] mb-2 w-64 rounded-lg bg-zinc-900 px-3 py-2 text-left text-xs font-medium normal-case tracking-normal text-white opacity-0 shadow-lg transition-opacity group-hover:opacity-100 dark:bg-white dark:text-zinc-900">
                                        Avaliação feita pelos clientes no site.
                                    </span>
                                </span>
                            </th>
                            <th class="px-5 py-4 text-right">Ação</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse($products as $product)
                            <tr wire:key="product-{{ $product->id }}" class="transition hover:bg-zinc-50 dark:hover:bg-zinc-800/40">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex shrink-0 items-center -space-x-2">
                                            @php
                                                $productPhotos = $product->images ?: ($product->image_url ? [$product->image_url] : []);
                                            @endphp
                                            @forelse(array_slice($productPhotos, 0, 3) as $photo)
                                                <img src="{{ asset('storage/'.$photo) }}" alt="{{ $product->name }}" class="size-11 rounded-xl border-2 border-white object-cover dark:border-zinc-900">
                                            @empty
                                                <div class="flex size-11 items-center justify-center rounded-xl bg-zinc-100 dark:bg-zinc-800">
                                                    <flux:icon name="photo" class="size-5 text-zinc-400" />
                                                </div>
                                            @endforelse
                                            @if(count($productPhotos) > 3)
                                                <span class="flex size-11 items-center justify-center rounded-xl border-2 border-white bg-zinc-100 text-xs font-bold text-zinc-600 dark:border-zinc-900 dark:bg-zinc-800 dark:text-zinc-300">3+</span>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-semibold text-zinc-950 dark:text-white">{{ $product->name }}</p>
                                            @if($product->description)
                                                <p class="mt-1 max-w-md text-xs leading-5 text-zinc-500 dark:text-zinc-400 line-clamp-2">{{ $product->description }}</p>
                                            @endif
                                            <p class="mt-0.5 max-w-xs truncate text-[11px] text-zinc-400">{{ $product->sku ?: 'Sem SKU' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    @if($product->description)
                                        <p class="max-w-sm line-clamp-2 text-sm text-zinc-600 dark:text-zinc-300">{{ $product->description }}</p>
                                    @else
                                        <span class="text-sm text-zinc-400">Sem descrição</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-zinc-600 dark:text-zinc-300">{{ $product->category?->name ?: 'Sem categoria' }}</td>
                                <td class="px-5 py-4">
                                    @if($product->stock > 0)
                                        <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">{{ $product->stock }} em stock</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-semibold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">Esgotado</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 font-bold text-zinc-950 dark:text-white">€ {{ number_format($this->salePrice($product), 2, ',', '.') }}</td>
                                <td class="px-5 py-4">
                                    @if($product->reviews_count > 0)
                                        <span class="font-semibold">{{ number_format((float) $product->reviews_avg_rating, 1, ',', '.') }}</span>
                                        <span class="text-zinc-400">({{ $product->reviews_count }})</span>
                                    @else
                                        <span class="text-zinc-400">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <button type="button" wire:click="openManageProductModal({{ $product->id }})" class="inline-flex items-center gap-2 rounded-lg border border-zinc-200 px-3 py-2 text-xs font-semibold transition hover:border-indigo-300 hover:text-indigo-600 dark:border-zinc-700 dark:hover:border-indigo-500 dark:hover:text-indigo-400">
                                        <flux:icon name="pencil-square" class="size-4" />
                                        Gerir
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-16 text-center">
                                    <flux:icon name="cube" class="mx-auto size-10 text-zinc-300 dark:text-zinc-600" />
                                    <p class="mt-3 font-semibold text-zinc-900 dark:text-white">Nenhum produto encontrado</p>
                                    <p class="mt-1 text-sm text-zinc-500">Experimenta alterar a pesquisa ou os filtros.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @if($productModalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-zinc-950/60 p-4 backdrop-blur-sm" wire:click="closeProductModal">
            <div class="w-full max-w-2xl overflow-hidden rounded-3xl bg-white shadow-2xl dark:bg-zinc-900" wire:click.stop>
                <div class="flex items-start justify-between border-b border-zinc-200 px-6 py-5 dark:border-zinc-800">
                    <div>
                        <p class="text-sm font-semibold text-indigo-600 dark:text-indigo-400">Novo produto</p>
                        <h2 class="mt-1 text-xl font-bold text-zinc-950 dark:text-white">Adicionar produto</h2>
                        <p class="mt-1 text-sm text-zinc-500">Adiciona um produto a este website.</p>
                    </div>
                    <button type="button" wire:click="closeProductModal" class="rounded-xl p-2 text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800" aria-label="Fechar">
                        <flux:icon name="x-mark" class="size-5" />
                    </button>
                </div>
                <form wire:submit="saveProduct" class="max-h-[75vh] overflow-y-auto p-6">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="mb-2 block text-sm font-semibold">Nome do produto</label>
                            <input wire:model="productName" type="text" class="w-full rounded-xl border-zinc-200 bg-zinc-50 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950" placeholder="Nome do produto">
                            @error('productName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-semibold">Categoria</label>
                            <select wire:model="productCategoryId" class="w-full rounded-xl border-zinc-200 bg-zinc-50 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950">
                                <option value="">Seleciona uma categoria</option>
                                @foreach($categories as $item)<option value="{{ $item->id }}">{{ $item->name }}</option>@endforeach
                            </select>
                            @if($productCategoryId && optional($categories->firstWhere('id', $productCategoryId))->slug === 'outros')
                                <input wire:model="productCustomCategory" type="text" class="mt-3 w-full rounded-xl border-zinc-200 bg-zinc-50 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950" placeholder="Nome da nova categoria">
                                <p class="mt-1 text-xs text-zinc-500">Escreve o nome da categoria que queres criar.</p>
                                @error('productCustomCategory') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            @endif
                            @error('productCategoryId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-semibold">Preço (€)</label>
                            <input wire:model="productPrice" type="number" min="0" step="0.01" class="w-full rounded-xl border-zinc-200 bg-zinc-50 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950">
                            @error('productPrice') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-semibold">Stock</label>
                            <input wire:model="productStock" type="number" min="0" class="w-full rounded-xl border-zinc-200 bg-zinc-50 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950">
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-semibold">Stock mínimo</label>
                            <input wire:model="productMinimumStock" type="number" min="0" class="w-full rounded-xl border-zinc-200 bg-zinc-50 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-2 block text-sm font-semibold">Descrição</label>
                            <textarea wire:model="productDescription" rows="3" class="w-full rounded-xl border-zinc-200 bg-zinc-50 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950"></textarea>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-2 block text-sm font-semibold">Imagem</label>
                            <input wire:model="productImages" type="file" multiple accept="image/jpeg,image/png,image/webp" class="w-full rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950">
                            <p class="mt-1 text-xs text-zinc-500">Podes adicionar até 10 fotografias, com até 10 MB por fotografia.</p>
                            @error('productImages') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            @error('productImages.*') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end gap-3 border-t border-zinc-200 pt-5 dark:border-zinc-800">
                        <button type="button" wire:click="closeProductModal" class="rounded-xl border border-zinc-200 px-4 py-2.5 text-sm font-semibold dark:border-zinc-700">Cancelar</button>
                        <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-indigo-700">Adicionar produto</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
    @if($manageProductModalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-zinc-950/60 p-4 backdrop-blur-sm" wire:click="closeManageProductModal">
            <div class="w-full max-w-2xl overflow-hidden rounded-3xl bg-white shadow-2xl dark:bg-zinc-900" wire:click.stop>
                <div class="flex items-start justify-between border-b border-zinc-200 px-6 py-5 dark:border-zinc-800">
                    <div>
                        <p class="text-sm font-semibold text-indigo-600 dark:text-indigo-400">Gestão de produto</p>
                        <h2 class="mt-1 text-xl font-bold text-zinc-950 dark:text-white">Gerir produto</h2>
                        <p class="mt-1 text-sm text-zinc-500">Edita os dados deste produto sem sair da página.</p>
                    </div>
                    <button type="button" wire:click="closeManageProductModal" class="rounded-xl p-2 text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800" aria-label="Fechar">
                        <flux:icon name="x-mark" class="size-5" />
                    </button>
                </div>
                <form wire:submit="updateProduct" class="max-h-[75vh] overflow-y-auto p-6">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="mb-2 block text-sm font-semibold">Nome do produto</label>
                            <input wire:model="productName" type="text" class="w-full rounded-xl border-zinc-200 bg-zinc-50 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950">
                            @error('productName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-semibold">Categoria</label>
                            <select wire:model="productCategoryId" class="w-full rounded-xl border-zinc-200 bg-zinc-50 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950">
                                @foreach($categories as $item)<option value="{{ $item->id }}">{{ $item->name }}</option>@endforeach
                            </select>
                            @if($productCategoryId && optional($categories->firstWhere('id', $productCategoryId))->slug === 'outros')
                                <input wire:model="productCustomCategory" type="text" class="mt-3 w-full rounded-xl border-zinc-200 bg-zinc-50 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950" placeholder="Nome da nova categoria">
                            @endif
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-semibold">Preço (€)</label>
                            <input wire:model="productPrice" type="number" min="0" step="0.01" class="w-full rounded-xl border-zinc-200 bg-zinc-50 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950">
                            @error('productPrice') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-semibold">Stock</label>
                            <input wire:model="productStock" type="number" min="0" class="w-full rounded-xl border-zinc-200 bg-zinc-50 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950">
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-semibold">Stock mínimo</label>
                            <input wire:model="productMinimumStock" type="number" min="0" class="w-full rounded-xl border-zinc-200 bg-zinc-50 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-2 block text-sm font-semibold">Descrição</label>
                            <textarea wire:model="productDescription" rows="3" class="w-full rounded-xl border-zinc-200 bg-zinc-50 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950"></textarea>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-2 block text-sm font-semibold">Adicionar fotografias</label>
                            <input wire:model="productImages" type="file" multiple accept="image/jpeg,image/png,image/webp" class="w-full rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950">
                            <p class="mt-1 text-xs text-zinc-500">As novas fotografias serão adicionadas às existentes. Até 10 MB por fotografia.</p>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end gap-3 border-t border-zinc-200 pt-5 dark:border-zinc-800">
                        <button type="button" wire:click="closeManageProductModal" class="rounded-xl border border-zinc-200 px-4 py-2.5 text-sm font-semibold dark:border-zinc-700">Cancelar</button>
                        <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-indigo-700">Guardar alterações</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>