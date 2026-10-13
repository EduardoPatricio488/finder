<div class="space-y-6">
    @if(session('status'))
        <div class="flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300">
            <flux:icon name="check-circle" class="mt-0.5 size-5 shrink-0" />
            <p>{{ session('status') }}</p>
        </div>
    @endif

    <div class="overflow-hidden rounded-3xl border border-emerald-100 bg-gradient-to-br from-emerald-50 via-white to-teal-50 dark:border-emerald-500/20 dark:from-emerald-500/10 dark:via-zinc-900 dark:to-teal-500/10">
        <div class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
            <div class="flex items-start gap-4">
                <div class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-600 text-white shadow-lg shadow-emerald-600/20">
                    <flux:icon name="archive-box" class="size-6" />
                </div>
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700 dark:text-emerald-300">Inventário da loja</p>
                    <h3 class="mt-1 text-xl font-bold tracking-tight text-zinc-950 dark:text-white">Movimentos de stock</h3>
                    <p class="mt-1 max-w-xl text-sm leading-6 text-zinc-600 dark:text-zinc-300">Regista entradas, saídas e ajustes e acompanha o histórico do website selecionado.</p>
                </div>
            </div>
            <div class="rounded-2xl border border-white/80 bg-white/80 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900/70">
                <p class="text-xs text-zinc-500 dark:text-zinc-400">Registos recentes</p>
                <p class="mt-1 text-xl font-bold text-zinc-950 dark:text-white">{{ $movements->count() }} <span class="text-xs font-medium text-zinc-500">/ 50 movimentos</span></p>
            </div>
        </div>
    </div>

    <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,0.82fr)_minmax(0,1.18fr)]">
        <section class="overflow-hidden rounded-3xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="border-b border-zinc-100 px-5 py-4 dark:border-zinc-800 sm:px-6">
                <div class="flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">
                        <flux:icon name="plus-circle" class="size-5" />
                    </div>
                    <div>
                        <h4 class="font-bold text-zinc-950 dark:text-white">Novo movimento</h4>
                        <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">Atualiza as quantidades do inventário</p>
                    </div>
                </div>
            </div>
            <form wire:submit="save" class="space-y-5 p-5 sm:p-6">
                <div>
                    <label for="stock-product" class="mb-1.5 block text-sm font-semibold text-zinc-700 dark:text-zinc-200">Produto</label>
                    <select id="stock-product" wire:model="productId" class="w-full rounded-xl border-zinc-200 bg-zinc-50 px-3 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                        <option value="">Selecionar produto</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }} — {{ $product->stock }} em stock</option>
                        @endforeach
                    </select>
                    @error('productId')<p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-zinc-700 dark:text-zinc-200">Tipo de movimento</label>
                    <div class="grid grid-cols-3 gap-2">
                        <label class="cursor-pointer">
                            <input type="radio" wire:model.live="type" value="entrada" class="peer sr-only">
                            <span class="flex flex-col items-center gap-1 rounded-xl border border-zinc-200 px-2 py-3 text-center text-sm font-semibold text-zinc-600 transition hover:bg-emerald-50 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-emerald-500/10 dark:peer-checked:border-emerald-500 dark:peer-checked:bg-emerald-500/10 dark:peer-checked:text-emerald-300">
                                <flux:icon name="arrow-down-left" class="size-5" /> Entrada
                            </span>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" wire:model.live="type" value="saida" class="peer sr-only">
                            <span class="flex flex-col items-center gap-1 rounded-xl border border-zinc-200 px-2 py-3 text-center text-sm font-semibold text-zinc-600 transition hover:bg-red-50 peer-checked:border-red-500 peer-checked:bg-red-50 peer-checked:text-red-700 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-red-500/10 dark:peer-checked:border-red-500 dark:peer-checked:bg-red-500/10 dark:peer-checked:text-red-300">
                                <flux:icon name="arrow-up-right" class="size-5" /> Saída
                            </span>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" wire:model.live="type" value="ajuste" class="peer sr-only">
                            <span class="flex flex-col items-center gap-1 rounded-xl border border-zinc-200 px-2 py-3 text-center text-sm font-semibold text-zinc-600 transition hover:bg-amber-50 peer-checked:border-amber-500 peer-checked:bg-amber-50 peer-checked:text-amber-800 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-amber-500/10 dark:peer-checked:border-amber-500 dark:peer-checked:bg-amber-500/10 dark:peer-checked:text-amber-300">
                                <flux:icon name="adjustments-horizontal" class="size-5" /> Ajuste
                            </span>
                        </label>
                    </div>
                </div>

                <div>
                    <label for="stock-quantity" class="mb-1.5 block text-sm font-semibold text-zinc-700 dark:text-zinc-200">Quantidade</label>
                    <input id="stock-quantity" wire:model="quantity" type="number" min="1" placeholder="Ex.: 5" class="w-full rounded-xl border-zinc-200 bg-zinc-50 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Indica o número de unidades a movimentar.</p>
                    @error('quantity')<p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="stock-note" class="mb-1.5 block text-sm font-semibold text-zinc-700 dark:text-zinc-200">Nota <span class="font-normal text-zinc-400">(opcional)</span></label>
                    <textarea id="stock-note" wire:model="note" rows="3" placeholder="Ex.: Reposição de mercadoria, produto danificado..." class="w-full resize-y rounded-xl border-zinc-200 bg-zinc-50 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950 dark:text-white"></textarea>
                    @error('note')<p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                </div>

                <button type="submit" wire:loading.attr="disabled" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700 disabled:cursor-wait disabled:opacity-60">
                    <flux:icon name="check-circle" class="size-4" />
                    <span wire:loading.remove>Registar movimento</span>
                    <span wire:loading>A registar...</span>
                </button>
            </form>
        </section>

        <section class="overflow-hidden rounded-3xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex flex-col gap-3 border-b border-zinc-100 px-5 py-4 dark:border-zinc-800 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div class="flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-xl bg-sky-50 text-sky-700 dark:bg-sky-500/10 dark:text-sky-300">
                        <flux:icon name="clock" class="size-5" />
                    </div>
                    <div>
                        <h4 class="font-bold text-zinc-950 dark:text-white">Histórico recente</h4>
                        <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">Últimos 50 movimentos</p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1.5 self-start rounded-full bg-zinc-100 px-3 py-1.5 text-xs font-semibold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 sm:self-auto">
                    <flux:icon name="list-bullet" class="size-3.5" /> {{ $movements->count() }} registos
                </span>
            </div>

            <div class="max-h-[34rem] overflow-auto">
                <table class="w-full min-w-[570px] text-left text-sm">
                    <thead class="sticky top-0 z-10 bg-zinc-50 text-[11px] font-bold uppercase tracking-wider text-zinc-500 dark:bg-zinc-800">
                        <tr>
                            <th class="px-4 py-3.5">Produto</th>
                            <th class="px-4 py-3.5">Tipo</th>
                            <th class="px-4 py-3.5">Quantidade</th>
                            <th class="px-4 py-3.5">Utilizador</th>
                            <th class="px-4 py-3.5">Data</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse($movements as $movement)
                            @php($exit = $movement->type === 'saida' || (int)$movement->quantity < 0)
                            <tr wire:key="stock-movement-{{ $movement->id }}" class="transition hover:bg-zinc-50 dark:hover:bg-zinc-800/40">
                                <td class="px-4 py-3.5">
                                    <p class="font-semibold text-zinc-900 dark:text-white">{{ $movement->product?->name ?? 'Produto removido' }}</p>
                                    @if($movement->note)<p class="mt-1 max-w-44 truncate text-xs text-zinc-500" title="{{ $movement->note }}">{{ $movement->note }}</p>@endif
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-bold {{ $movement->type === 'entrada' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300' : ($movement->type === 'saida' ? 'bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-300' : 'bg-amber-50 text-amber-800 dark:bg-amber-500/10 dark:text-amber-300') }}">
                                        <flux:icon name="{{ $movement->type === 'entrada' ? 'arrow-down-left' : ($movement->type === 'saida' ? 'arrow-up-right' : 'adjustments-horizontal') }}" class="size-3.5" />
                                        {{ ucfirst($movement->type) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 font-bold {{ $exit ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400' }}">{{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }}</td>
                                <td class="px-4 py-3.5 text-zinc-600 dark:text-zinc-300">{{ $movement->user?->name ?? 'Sistema' }}</td>
                                <td class="whitespace-nowrap px-4 py-3.5 text-xs text-zinc-500 dark:text-zinc-400">{{ $movement->created_at?->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-12 text-center">
                                    <div class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-zinc-100 text-zinc-400 dark:bg-zinc-800">
                                        <flux:icon name="archive-box" class="size-6" />
                                    </div>
                                    <p class="mt-3 font-semibold text-zinc-800 dark:text-zinc-200">Ainda não existem movimentos de stock</p>
                                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Os movimentos registados aparecerão aqui.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>
