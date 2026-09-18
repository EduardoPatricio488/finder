<div class="min-h-screen bg-zinc-50 dark:bg-zinc-950">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-8">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm font-semibold text-indigo-600 dark:text-indigo-400">Loja</p>
                    <h1 class="mt-2 text-3xl font-bold tracking-tight text-zinc-950 dark:text-white">Produtos</h1>
                    <div class="mt-4 max-w-md">
                        <label for="product-site-selector" class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Website / Workspace</label>
                        <select id="product-site-selector" wire:model.live="selectedSiteId" class="w-full rounded-xl border-zinc-200 bg-zinc-50 px-4 py-3 text-sm font-semibold text-zinc-900 shadow-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                            @foreach($availableSites as $availableSite)
                                <option value="{{ $availableSite->id }}">{{ $availableSite->name }}</option>
                            @endforeach
                        </select>
                        <p class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">Os produtos apresentados e criados nesta página pertencem exclusivamente ao website selecionado.</p>
                    </div>
                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        @if($site)
                            <span class="inline-flex items-center gap-2 rounded-full border border-indigo-200 bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-700 dark:border-indigo-500/20 dark:bg-indigo-500/10 dark:text-indigo-300">
                                <flux:icon name="globe-alt" class="size-4" />
                                Website: {{ $site->name }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-300">
                                <flux:icon name="exclamation-triangle" class="size-4" />
                                Não estão associados a nenhum website
                            </span>
                        @endif
                    </div>
                    <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">Gere os produtos da empresa, acompanha o stock e mantém o catálogo organizado.</p>
                </div>
                <button type="button" wire:click="openProductModal" class="inline-flex items-center justify-center gap-2 rounded-xl bg-zinc-950 px-5 py-3 text-sm font-bold text-white transition hover:bg-indigo-600 dark:bg-white dark:text-zinc-950 dark:hover:bg-indigo-500 dark:hover:text-white">
                    <flux:icon name="plus" class="size-4" />
                    Adicionar produto
                </button>
            </div>
        </div>

        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
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
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-sm dark:border-amber-500/20 dark:bg-amber-500/10">
                <p class="text-sm text-amber-700 dark:text-amber-400">Stock baixo</p>
                <p class="mt-2 text-2xl font-bold text-amber-800 dark:text-amber-300">{{ $lowStockProducts->count() }}</p>
            </div>
                    <div class="rounded-2xl border border-indigo-200 bg-indigo-50 p-5 shadow-sm dark:border-indigo-500/20 dark:bg-indigo-500/10">
                <p class="text-sm text-indigo-700 dark:text-indigo-400">Valor do stock</p>
                <p class="mt-2 text-2xl font-bold text-indigo-800 dark:text-indigo-300">€ {{ number_format($stockValue, 2, ',', '.') }}</p>
            </div>
        </div>

        <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap gap-2">
                <label class="inline-flex cursor-pointer items-center gap-2 rounded-xl border border-zinc-200 bg-white px-4 py-2.5 text-sm font-semibold hover:border-indigo-300 dark:border-zinc-700 dark:bg-zinc-900">
                    <flux:icon name="arrow-up-tray" class="size-4" /> Importar CSV
                    <input type="file" wire:model="importFile" accept=".csv,.txt" class="hidden">
                </label>
                <button type="button" wire:click="exportProducts" class="inline-flex items-center gap-2 rounded-xl border border-zinc-200 bg-white px-4 py-2.5 text-sm font-semibold hover:border-indigo-300 dark:border-zinc-700 dark:bg-zinc-900">
                    <flux:icon name="arrow-down-tray" class="size-4" /> Exportar CSV
                </button>
                <button type="button" wire:click="openCategoriesModal" class="inline-flex items-center gap-2 rounded-xl border border-zinc-200 bg-white px-4 py-2.5 text-sm font-semibold hover:border-indigo-300 dark:border-zinc-700 dark:bg-zinc-900">
                    <flux:icon name="tag" class="size-4" /> Gerir categorias
                </button>
            </div>
            @if(count($selectedProducts))
                <div class="flex items-center gap-2 rounded-xl border border-indigo-200 bg-indigo-50 px-3 py-2 text-sm dark:border-indigo-500/20 dark:bg-indigo-500/10">
                    <span class="font-semibold">{{ count($selectedProducts) }} seleccionados</span>
                    <button type="button" wire:click="bulkDelete" wire:confirm="Eliminar os produtos seleccionados que não tenham vendas?" class="rounded-lg px-3 py-1.5 font-semibold text-red-600 hover:bg-red-100 dark:hover:bg-red-500/10">Eliminar</button>
                </div>
            @endif
        </div>

        <div class="mt-4 rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
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
                    <option value="baixo">Stock baixo</option>
                    <option value="esgotado">Esgotado</option>
                </select>
            </div>
            <div class="mt-3 flex flex-wrap gap-3">
                <select wire:model.live="catalogFilter" class="rounded-xl border-zinc-200 bg-zinc-50 px-4 py-2.5 text-sm dark:border-zinc-700 dark:bg-zinc-950">
                    <option value="">Todos os produtos</option>
                    <option value="sem_imagem">Sem imagem</option>
                    <option value="sem_descricao">Sem descrição</option>
                </select>
                <select wire:model.live="sortBy" class="rounded-xl border-zinc-200 bg-zinc-50 px-4 py-2.5 text-sm dark:border-zinc-700 dark:bg-zinc-950">
                    <option value="name">Ordenar: Nome</option>
                    <option value="newest">Mais recentes</option>
                    <option value="price_asc">Preço mais baixo</option>
                    <option value="price_desc">Preço mais alto</option>
                    <option value="stock">Maior stock</option>
                    <option value="rating">Melhor avaliação</option>
                </select>
                <input wire:model.live="minPrice" type="number" min="0" step=".01" placeholder="Preço mín." class="w-full rounded-xl border-zinc-200 bg-zinc-50 px-4 py-2.5 text-sm dark:border-zinc-700 dark:bg-zinc-950 sm:w-36">
                <input wire:model.live="maxPrice" type="number" min="0" step=".01" placeholder="Preço máx." class="w-full rounded-xl border-zinc-200 bg-zinc-50 px-4 py-2.5 text-sm dark:border-zinc-700 dark:bg-zinc-950 sm:w-36">
                <select wire:model.live="minRating" class="rounded-xl border-zinc-200 bg-zinc-50 px-4 py-2.5 text-sm dark:border-zinc-700 dark:bg-zinc-950">
                    <option value="">Qualquer avaliação</option>
                    <option value="4">4+ estrelas</option>
                    <option value="3">3+ estrelas</option>
                </select>
                <button type="button" wire:click="resetFilters" class="inline-flex items-center gap-2 rounded-xl border border-zinc-200 bg-white px-4 py-2.5 text-sm font-semibold text-zinc-700 transition hover:border-indigo-300 hover:text-indigo-600 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:border-indigo-500 dark:hover:text-indigo-400" title="Repor todos os filtros">
                    <flux:icon name="arrow-path" class="size-4" />
                    Repor filtros
                </button>
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
                            <th class="w-12 px-5 py-4"><input type="checkbox" wire:model.live="selectAllProducts" class="rounded border-zinc-300"></th>
                            <th class="px-5 py-4">Produto</th>
                            <th class="px-5 py-4">Descrição</th>
                            <th class="px-5 py-4">Categoria</th>
                            <th class="px-5 py-4">Stock</th>
                            <th class="px-5 py-4">Preço</th>
                            <th class="px-5 py-4">
                                <span class="inline-flex items-center gap-1">
                                    Avaliação
                                    <flux:tooltip content="Avaliação feita pelos clientes no site." position="bottom">
                                        <flux:button variant="ghost" size="sm" icon="information-circle" aria-label="Informação sobre avaliação" class="!size-5 !p-0 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300" />
                                    </flux:tooltip>
                                </span>
                            </th>
                            <th class="px-5 py-4 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse($products as $product)
                            <tr wire:key="product-{{ $product->id }}" class="transition hover:bg-zinc-50 dark:hover:bg-zinc-800/40">
                                <td class="px-5 py-4"><input type="checkbox" value="{{ $product->id }}" wire:model.live="selectedProducts" class="rounded border-zinc-300"></td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex shrink-0 items-center -space-x-2">
                                            @php
                                                $productPhotos = $product->images ?: ($product->image_url ? [$product->image_url] : []);
                                            @endphp
                                            @forelse(array_slice($productPhotos, 0, 2) as $photoIndex => $photo)
                                                <button type="button" wire:click="openImageGallery({{ $product->id }}, {{ $photoIndex }})" class="relative shrink-0 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500" title="Ver fotografias em grande">
                                                    <img src="{{ asset('storage/'.$photo) }}" alt="{{ $product->name }}" class="size-11 rounded-xl border-2 border-white object-cover transition hover:scale-110 dark:border-zinc-900">
                                                </button>
                                            @empty
                                                <div class="flex size-11 items-center justify-center rounded-xl bg-zinc-100 dark:bg-zinc-800">
                                                    <flux:icon name="photo" class="size-5 text-zinc-400" />
                                                </div>
                                            @endforelse
                                            @if(count($productPhotos) > 2)
                                                <button type="button" wire:click="openImageGallery({{ $product->id }}, 0)" class="flex size-11 shrink-0 items-center justify-center rounded-xl border-2 border-white bg-zinc-100 text-xs font-bold text-zinc-700 transition hover:bg-zinc-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-zinc-900 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700" title="Ver todas as fotografias">
                                                    +{{ count($productPhotos) - 2 }}
                                                </button>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-semibold text-zinc-950 dark:text-white">{{ $product->name }}</p>
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
                                    @if($product->stock <= 0)
                                        <span class="inline-flex rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-semibold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">Esgotado</span>
                                    @elseif($product->stock <= $product->minimum_stock)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">
                                            <flux:icon name="exclamation-triangle" class="size-3.5" />
                                            {{ $product->stock }} — Stock baixo
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">{{ $product->stock }} em stock</span>
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
                                    <flux:dropdown position="bottom" align="end">
                                        <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" aria-label="Ações do produto" />

                                        <flux:menu>
                                            <flux:menu.item as="button" wire:click="openManageProductModal({{ $product->id }})" icon="pencil-square">
                                                Gerir produto
                                            </flux:menu.item>
                                            <flux:menu.item as="button" wire:click="openQuickStockModal({{ $product->id }})" icon="arrows-up-down">
                                                Alterar stock
                                            </flux:menu.item>
                                            <flux:menu.item as="button" wire:click="openStockHistory({{ $product->id }})" icon="clock">
                                                Histórico de stock
                                            </flux:menu.item>
                                            <flux:menu.item as="button" wire:click="duplicateProduct({{ $product->id }})" icon="square-2-stack">
                                                Duplicar
                                            </flux:menu.item>
                                            <flux:menu.separator />
                                            <flux:menu.item as="button" wire:click="deleteProduct({{ $product->id }})" wire:confirm="Eliminar este produto? Esta ação não pode ser anulada." icon="trash" variant="danger">
                                                Eliminar
                                            </flux:menu.item>
                                        </flux:menu>
                                    </flux:dropdown>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-16 text-center">
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
    @if($quickStockModalOpen)
        <div class="fixed inset-0 z-[90] flex items-center justify-center bg-zinc-950/60 p-4 backdrop-blur-sm" wire:click="closeQuickStockModal">
            <div class="w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl dark:bg-zinc-900" wire:click.stop>
                <div class="flex items-start justify-between">
                    <div><p class="text-sm font-semibold text-indigo-600">Stock</p><h2 class="mt-1 text-xl font-bold text-zinc-950 dark:text-white">Alterar stock</h2><p class="mt-1 text-sm text-zinc-500">{{ $stockProductName }}</p></div>
                    <button type="button" wire:click="closeQuickStockModal" class="rounded-xl p-2 text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800"><flux:icon name="x-mark" class="size-5"/></button>
                </div>
                <div class="mt-6 grid gap-4">
                    <div>
                        <label class="text-sm font-semibold">Tipo de movimento</label>
                        <div class="mt-2 grid grid-cols-2 gap-2">
                            <button type="button" wire:click="$set('stockType', 'entrada')" class="rounded-xl border px-4 py-3 text-sm font-bold transition {{ $stockType === 'entrada' ? 'border-emerald-500 bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400' : 'border-zinc-200 text-zinc-600 hover:border-emerald-300 dark:border-zinc-700 dark:text-zinc-300' }}">
                                <span class="block">Entrada de stock</span>
                                <span class="mt-1 block text-xs font-normal opacity-70">Adicionar unidades</span>
                            </button>
                            <button type="button" wire:click="$set('stockType', 'saida')" class="rounded-xl border px-4 py-3 text-sm font-bold transition {{ $stockType === 'saida' ? 'border-red-500 bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400' : 'border-zinc-200 text-zinc-600 hover:border-red-300 dark:border-zinc-700 dark:text-zinc-300' }}">
                                <span class="block">Saída de stock</span>
                                <span class="mt-1 block text-xs font-normal opacity-70">Retirar unidades</span>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="text-sm font-semibold">Quantidade</label>
                        <input wire:model="stockChange" type="number" min="1" class="mt-2 w-full rounded-xl border-zinc-200 bg-zinc-50 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-950" placeholder="Ex.: 10">
                        @error('stockChange')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div><label class="text-sm font-semibold">Motivo</label><input wire:model="stockReason" type="text" class="mt-2 w-full rounded-xl border-zinc-200 bg-zinc-50 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-950" placeholder="Reposição, venda, correcção..."></div>
                </div>
                <div class="mt-6 flex justify-end gap-2"><button type="button" wire:click="closeQuickStockModal" class="rounded-xl border px-4 py-2.5 text-sm font-semibold dark:border-zinc-700">Cancelar</button><button type="button" wire:click="saveQuickStock" class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white">Guardar</button></div>
            </div>
        </div>
    @endif
    @if($stockHistoryModalOpen)
        <div class="fixed inset-0 z-[90] flex items-center justify-center bg-zinc-950/60 p-4 backdrop-blur-sm" wire:click="closeStockHistory">
            <div class="w-full max-w-2xl rounded-3xl bg-white p-6 shadow-2xl dark:bg-zinc-900" wire:click.stop>
                <div class="flex items-start justify-between"><div><p class="text-sm font-semibold text-indigo-600">Inventário</p><h2 class="mt-1 text-xl font-bold text-zinc-950 dark:text-white">Histórico de stock</h2><p class="mt-1 text-sm text-zinc-500">{{ $stockProductName }}</p></div><button type="button" wire:click="closeStockHistory" class="rounded-xl p-2 text-zinc-400"><flux:icon name="x-mark" class="size-5"/></button></div>
                <div class="mt-5 overflow-hidden rounded-2xl border dark:border-zinc-800"><table class="w-full text-left text-sm"><thead class="bg-zinc-50 dark:bg-zinc-800"><tr><th class="px-4 py-3">Data</th><th class="px-4 py-3">Alteração</th><th class="px-4 py-3">Motivo</th><th class="px-4 py-3">Utilizador</th></tr></thead><tbody class="divide-y dark:divide-zinc-800">@forelse($stockHistory as $movement)<tr><td class="px-4 py-3 text-zinc-500">{{ $movement->created_at?->format('d/m/Y H:i') }}</td><td class="px-4 py-3 font-bold {{ $movement->quantity > 0 ? 'text-emerald-600' : 'text-red-600' }}">{{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }}</td><td class="px-4 py-3">{{ $movement->note ?: '—' }}</td><td class="px-4 py-3 text-zinc-500">{{ $movement->user?->name ?: 'Sistema' }}</td></tr>@empty<tr><td colspan="4" class="px-4 py-8 text-center text-zinc-500">Ainda não existem movimentos.</td></tr>@endforelse</tbody></table></div>
            </div>
        </div>
    @endif

    @if($imageGalleryOpen)
        <div class="fixed inset-0 z-[100] flex items-center justify-center bg-zinc-950/90 p-4 backdrop-blur-sm" wire:click="closeImageGallery">
            <div class="relative flex h-full w-full max-w-6xl flex-col items-center justify-center" wire:click.stop>
                <div class="absolute right-0 top-0 z-10 flex items-center gap-2">
                    <span class="rounded-full bg-white/10 px-3 py-1.5 text-xs font-semibold text-white">{{ $imageGalleryIndex + 1 }} / {{ count($imageGalleryPhotos) }}</span>
                    <button type="button" wire:click="closeImageGallery" class="rounded-xl bg-white/10 p-2 text-white transition hover:bg-white/20" aria-label="Fechar">
                        <flux:icon name="x-mark" class="size-6" />
                    </button>
                </div>

                <div class="mb-4 text-center">
                    <p class="text-lg font-bold text-white">{{ $imageGalleryName }}</p>
                    <p class="mt-1 text-sm text-zinc-300">Clica fora da imagem ou no X para fechar</p>
                </div>

                <div class="relative flex min-h-0 w-full flex-1 items-center justify-center">
                    <img src="{{ asset('storage/'.($imageGalleryPhotos[$imageGalleryIndex] ?? '')) }}" alt="{{ $imageGalleryName }}" class="max-h-[75vh] max-w-full rounded-2xl object-contain shadow-2xl">

                    @if(count($imageGalleryPhotos) > 1)
                        <button type="button" wire:click="previousImage" class="absolute left-2 rounded-full bg-white/15 p-3 text-white backdrop-blur transition hover:bg-white/25 sm:left-6" aria-label="Fotografia anterior">
                            <flux:icon name="chevron-left" class="size-6" />
                        </button>
                        <button type="button" wire:click="nextImage" class="absolute right-2 rounded-full bg-white/15 p-3 text-white backdrop-blur transition hover:bg-white/25 sm:right-6" aria-label="Fotografia seguinte">
                            <flux:icon name="chevron-right" class="size-6" />
                        </button>
                    @endif
                </div>

                @if(count($imageGalleryPhotos) > 1)
                    <div class="mt-4 flex max-w-full gap-2 overflow-x-auto pb-2">
                        @foreach($imageGalleryPhotos as $thumbIndex => $photo)
                            <div class="relative shrink-0">
                                <button type="button" wire:click="$set('imageGalleryIndex', {{ $thumbIndex }})" class="rounded-lg {{ $imageGalleryIndex === $thumbIndex ? 'ring-2 ring-white' : 'opacity-60 hover:opacity-100' }}">
                                    <img src="{{ asset('storage/'.$photo) }}" alt="" class="size-16 rounded-lg object-cover">
                                </button>
                                <button type="button" wire:click="setPrimaryImage({{ $stockProductId ?? 0 }}, {{ $thumbIndex }})" class="absolute bottom-1 left-1 rounded bg-zinc-950/70 px-1.5 py-0.5 text-[10px] text-white">Principal</button>
                                <button type="button" wire:click="deleteProductImage({{ $stockProductId ?? 0 }}, {{ $thumbIndex }})" class="absolute right-1 top-1 rounded bg-red-600/90 p-1 text-white" aria-label="Eliminar fotografia"><flux:icon name="trash" class="size-3"/></button>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if($categoriesModalOpen)
        <div class="fixed inset-0 z-[110] flex items-center justify-center bg-zinc-950/60 p-4 backdrop-blur-sm" wire:click="closeCategoriesModal">
            <div class="w-full max-w-lg overflow-hidden rounded-3xl bg-white shadow-2xl dark:bg-zinc-900" wire:click.stop>
                <div class="flex items-start justify-between border-b border-zinc-200 px-6 py-5 dark:border-zinc-800">
                    <div>
                        <p class="text-sm font-semibold text-indigo-600 dark:text-indigo-400">Produtos</p>
                        <h2 class="mt-1 text-xl font-bold text-zinc-950 dark:text-white">Gerir categorias</h2>
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Cria e gere as categorias deste website sem sair desta página.</p>
                    </div>
                    <button type="button" wire:click="closeCategoriesModal" class="rounded-xl p-2 text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800" aria-label="Fechar">
                        <flux:icon name="x-mark" class="size-5" />
                    </button>
                </div>
                <div class="p-6">
                    <form wire:submit="saveCategory" class="flex gap-2">
                        <input wire:model="newCategoryName" type="text" class="min-w-0 flex-1 rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-2.5 text-sm dark:border-zinc-700 dark:bg-zinc-950" placeholder="Nome da categoria">
                        <button type="submit" class="shrink-0 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-indigo-700">Adicionar</button>
                    </form>
                    @error('newCategoryName') <p class="mt-2 text-xs text-red-600">{{ $message }}</p> @enderror
                    @error('categoryDelete') <p class="mt-3 rounded-xl bg-red-50 px-3 py-2 text-xs font-semibold text-red-700 dark:bg-red-500/10 dark:text-red-400">{{ $message }}</p> @enderror
                    <div class="mt-5 max-h-80 space-y-2 overflow-y-auto">
                        @forelse($categories as $categoryItem)
                            <div class="flex items-center justify-between rounded-xl border border-zinc-200 px-4 py-3 dark:border-zinc-800">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-zinc-950 dark:text-white">{{ $categoryItem->name }}</p>
                                    <p class="text-xs text-zinc-400">{{ $categoryItem->products_count }} {{ $categoryItem->products_count === 1 ? 'produto' : 'produtos' }}</p>
                                </div>
                                @if($categoryItem->products_count === 0)
                                    <button type="button" wire:click="deleteCategory({{ $categoryItem->id }})" wire:confirm="Eliminar a categoria {{ $categoryItem->name }}?" class="rounded-lg p-2 text-zinc-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-500/10" aria-label="Eliminar categoria">
                                        <flux:icon name="trash" class="size-4" />
                                    </button>
                                @else
                                    <span class="text-xs text-zinc-400">Em uso</span>
                                @endif
                            </div>
                        @empty
                            <div class="rounded-xl border border-dashed border-zinc-200 px-4 py-8 text-center text-sm text-zinc-500 dark:border-zinc-800">Ainda não existem categorias.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($productModalOpen)
        <div class="fixed inset-0 z-[100] flex items-center justify-center bg-zinc-950/60 p-4 backdrop-blur-sm" wire:click="closeProductModal">
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
                            <div class="flex items-center gap-2">
                                <select wire:model="productCategoryId" class="min-w-0 flex-1 rounded-xl border-zinc-200 bg-zinc-50 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950">
                                    <option value="">Seleciona uma categoria</option>
                                    @foreach($categories as $item)<option value="{{ $item->id }}">{{ $item->name }}</option>@endforeach
                                </select>
                                <button type="button" wire:click="openCategoriesModal" class="shrink-0 rounded-xl border border-zinc-200 px-3 py-2.5 text-zinc-600 hover:border-indigo-300 hover:text-indigo-600 dark:border-zinc-700 dark:text-zinc-300" title="Adicionar nova categoria" aria-label="Adicionar nova categoria">
                                    <flux:icon name="plus" class="size-4" />
                                </button>
                            </div>
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
                            <input wire:model.live="productMinimumStock" type="number" min="0" class="w-full rounded-xl border-zinc-200 bg-zinc-50 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950 @error('productMinimumStock') border-red-500 ring-2 ring-red-500/10 @enderror">
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
                        <div>
                            <label class="mb-2 block text-sm font-semibold">Stock mínimo</label>
                            <input wire:model.live="productMinimumStock" type="number" min="0" class="w-full rounded-xl border-zinc-200 bg-zinc-50 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950 @error('productMinimumStock') border-red-500 ring-2 ring-red-500/10 @enderror">
                            @error('productMinimumStock')
                                <p class="mt-2 flex items-center gap-1.5 text-xs font-semibold text-red-600 dark:text-red-400">
                                    <flux:icon name="exclamation-circle" class="size-4" />
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>
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
