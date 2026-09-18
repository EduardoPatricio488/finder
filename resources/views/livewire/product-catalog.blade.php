<div class="min-h-screen bg-zinc-50 dark:bg-zinc-950">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="relative overflow-hidden rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-8">
            <div class="absolute -right-16 -top-20 size-56 rounded-full bg-indigo-100 blur-3xl dark:bg-indigo-500/10"></div>
            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <div class="flex items-center gap-2 text-sm font-semibold text-indigo-600 dark:text-indigo-400">
                        <flux:icon name="shopping-bag" class="size-4" /> Loja
                    </div>
                    <h1 class="mt-2 text-3xl font-bold tracking-tight text-zinc-950 dark:text-white">Produtos</h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-zinc-500 dark:text-zinc-400">Gere o catálogo da empresa, consulta o stock e mantém os produtos organizados num só lugar.</p>
                </div>
                </div>
        </div>

        <div class="mt-6 rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex flex-col gap-3 lg:flex-row">
                <div class="relative flex-1">
                    <flux:icon name="magnifying-glass" class="pointer-events-none absolute left-3.5 top-1/2 size-5 -translate-y-1/2 text-zinc-400" />
                    <input wire:model.live.debounce.300ms="search" type="search" placeholder="Pesquisar por nome, descrição ou SKU..." class="w-full rounded-xl border-zinc-200 bg-zinc-50 py-3 pl-11 pr-4 text-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 dark:border-zinc-700 dark:bg-zinc-950">
                </div>
                <select wire:model.live="category" class="rounded-xl border-zinc-200 bg-zinc-50 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950 lg:w-56">
                    <option value="">Todas as categorias</option>
                    @foreach($categories as $item)<option value="{{ $item->slug }}">{{ $item->name }}</option>@endforeach
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

        <div class="mt-8 flex items-end justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-zinc-950 dark:text-white">Gestão de produtos</h2>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $products->count() }} {{ $products->count() === 1 ? 'produto encontrado' : 'produtos encontrados' }}</p>
            </div>
        </div>

        <div class="mt-5 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
            @forelse($products as $product)
                <article wire:key="product-{{ $product->id }}" class="group flex flex-col overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm transition duration-200 hover:-translate-y-1 hover:shadow-xl dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="relative overflow-hidden bg-zinc-100 dark:bg-zinc-800">
                        @if($product->image_url)
                            <img src="{{ asset('storage/'.$product->image_url) }}" alt="{{ $product->name }}" class="aspect-[4/3] w-full object-cover transition duration-500 group-hover:scale-105">
                        @else
                            <div class="flex aspect-[4/3] items-center justify-center"><flux:icon name="photo" class="size-12 text-zinc-300 dark:text-zinc-600" /></div>
                        @endif
                        @if($product->stock <= 0)
                            <span class="absolute left-4 top-4 rounded-full bg-zinc-950/85 px-3 py-1.5 text-xs font-bold text-white">Esgotado</span>
                        @else
                            <span class="absolute left-4 top-4 rounded-full bg-emerald-500 px-3 py-1.5 text-xs font-bold text-white">{{ $product->stock }} em stock</span>
                        @endif
                    </div>
                    <div class="flex flex-1 flex-col p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <p class="text-xs font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">{{ $product->category?->name ?: 'Produto' }}</p>
                                <h3 class="mt-1 truncate text-lg font-bold text-zinc-950 dark:text-white">{{ $product->name }}</h3>
                            </div>
                            <p class="whitespace-nowrap text-lg font-bold text-zinc-950 dark:text-white">€ {{ number_format($this->salePrice($product), 2, ',', '.') }}</p>
                        </div>
                        @if($product->reviews_count > 0)
                            <div class="mt-2 flex items-center gap-1 text-sm"><span class="text-amber-500">★</span><span class="font-semibold">{{ number_format((float) $product->reviews_avg_rating, 1, ',', '.') }}</span><span class="text-zinc-400">({{ $product->reviews_count }})</span></div>
                        @endif
                        <p class="mt-3 line-clamp-2 text-sm leading-6 text-zinc-500 dark:text-zinc-400">{{ $product->description ?: 'Sem descrição disponível.' }}</p>
                        <a href="{{ route('admin.site.products', $site) }}" wire:navigate class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-zinc-950 px-4 py-3 text-sm font-bold text-white transition hover:bg-indigo-600 dark:bg-white dark:text-zinc-950 dark:hover:bg-indigo-500 dark:hover:text-white">
                            <flux:icon name="pencil-square" class="size-4" /> Gerir produto
                        </a>
                    </div>
                </article>
            @empty
                <div class="col-span-full rounded-3xl border border-dashed border-zinc-300 bg-white px-6 py-16 text-center dark:border-zinc-700 dark:bg-zinc-900">
                    <flux:icon name="magnifying-glass" class="mx-auto size-12 text-zinc-300 dark:text-zinc-600" />
                    <h3 class="mt-4 text-lg font-bold text-zinc-900 dark:text-white">Nenhum produto encontrado</h3>
                    <p class="mt-2 text-sm text-zinc-500">Experimenta alterar a pesquisa ou os filtros.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>