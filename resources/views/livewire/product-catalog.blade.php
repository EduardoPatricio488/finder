<div class="min-h-screen bg-zinc-50 dark:bg-zinc-950">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-8">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm font-semibold text-indigo-600 dark:text-indigo-400">Loja</p>
                    <h1 class="mt-2 text-3xl font-bold tracking-tight text-zinc-950 dark:text-white">Produtos</h1>
                    <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">Gere os produtos da empresa, acompanha o stock e mantém o catálogo organizado.</p>
                </div>
                <a href="{{ route('admin.site.products', $site) }}" wire:navigate class="inline-flex items-center justify-center gap-2 rounded-xl bg-zinc-950 px-5 py-3 text-sm font-bold text-white transition hover:bg-indigo-600 dark:bg-white dark:text-zinc-950 dark:hover:bg-indigo-500 dark:hover:text-white">
                    <flux:icon name="plus" class="size-4" />
                    Gerir produtos
                </a>
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
                            <th class="px-5 py-4">Categoria</th>
                            <th class="px-5 py-4">Stock</th>
                            <th class="px-5 py-4">Preço</th>
                            <th class="px-5 py-4">Avaliação</th>
                            <th class="px-5 py-4 text-right">Ação</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse($products as $product)
                            <tr wire:key="product-{{ $product->id }}" class="transition hover:bg-zinc-50 dark:hover:bg-zinc-800/40">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="size-11 shrink-0 overflow-hidden rounded-xl bg-zinc-100 dark:bg-zinc-800">
                                            @if($product->image_url)
                                                <img src="{{ asset('storage/'.$product->image_url) }}" alt="{{ $product->name }}" class="size-full object-cover">
                                            @else
                                                <div class="flex size-full items-center justify-center"><flux:icon name="photo" class="size-5 text-zinc-400" /></div>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-semibold text-zinc-950 dark:text-white">{{ $product->name }}</p>
                                            <p class="mt-0.5 max-w-xs truncate text-xs text-zinc-500">{{ $product->sku ?: 'Sem SKU' }}</p>
                                        </div>
                                    </div>
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
                                    <a href="{{ route('admin.site.products', $site) }}" wire:navigate class="inline-flex items-center gap-2 rounded-lg border border-zinc-200 px-3 py-2 text-xs font-semibold transition hover:border-indigo-300 hover:text-indigo-600 dark:border-zinc-700 dark:hover:border-indigo-500 dark:hover:text-indigo-400">
                                        <flux:icon name="pencil-square" class="size-4" />
                                        Gerir
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-16 text-center">
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
</div>