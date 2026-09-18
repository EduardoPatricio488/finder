<div class="mx-auto max-w-7xl space-y-8 px-6 py-10 lg:px-12">
    <div class="max-w-xs">
        <x-site-workspace-selector :current-site="$this->currentSite()" route-name="stock" />
    </div>

    <header class="flex flex-col justify-between gap-6 md:flex-row md:items-end">
        <div>
            <div class="flex flex-wrap items-center gap-3">
                <span class="text-[10px] font-black uppercase tracking-[0.3em] text-red-700">Gestão de Stock</span>
                <span class="h-px w-8 bg-red-200"></span>
                <span class="text-[10px] font-black uppercase tracking-widest text-stone-400">{{ $this->currentSite()->name }}</span>
            </div>
            <h1 class="mt-3 text-4xl font-black italic tracking-tighter text-stone-950 dark:text-white">Movimentos de stock</h1>
            <p class="mt-3 max-w-2xl text-sm font-medium leading-relaxed text-stone-500">Regista entradas, saídas e ajustes de stock e acompanha o histórico recente.</p>
        </div>
    </header>

    <div class="grid gap-8 lg:grid-cols-[minmax(0,0.8fr)_minmax(0,1.2fr)]">
        <section class="rounded-[2rem] border border-stone-200 bg-white p-7 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center gap-3">
                <div class="flex size-11 items-center justify-center rounded-2xl bg-red-50 text-red-600 dark:bg-red-950/30 dark:text-red-400">
                    <flux:icon name="arrows-right-left" class="size-5" />
                </div>
                <div>
                    <h2 class="text-lg font-black text-stone-950 dark:text-white">Novo movimento</h2>
                    <p class="text-xs text-stone-500">Atualiza o stock de um produto.</p>
                </div>
            </div>

            <form wire:submit="save" class="mt-7 space-y-5">
                <flux:select wire:model="productId" label="Produto" placeholder="Selecionar produto">
                    @foreach($products as $product)
                        <flux:select.option value="{{ $product->id }}">{{ $product->name }} — {{ $product->stock }} em stock</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select wire:model="type" label="Tipo">
                    <flux:select.option value="entrada">Entrada</flux:select.option>
                    <flux:select.option value="saida">Saída</flux:select.option>
                    <flux:select.option value="ajuste">Ajuste</flux:select.option>
                </flux:select>

                <flux:input wire:model="quantity" type="number" min="1" label="Quantidade" />

                <flux:textarea wire:model="note" label="Nota" placeholder="Opcional: indica o motivo do movimento..." rows="4" />

                <div class="flex justify-end pt-2">
                    <flux:button type="submit" variant="primary" icon="plus" class="!rounded-xl !bg-stone-950">
                        Registar movimento
                    </flux:button>
                </div>
            </form>
        </section>

        <section class="rounded-[2rem] border border-stone-200 bg-white p-7 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-black text-stone-950 dark:text-white">Histórico recente</h2>
                    <p class="mt-1 text-sm text-stone-500">Últimos 50 movimentos deste website.</p>
                </div>
                <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-bold text-stone-600 dark:bg-zinc-800 dark:text-zinc-300">{{ $movements->count() }}</span>
            </div>

            <div class="mt-6 overflow-x-auto">
                <table class="w-full min-w-[620px] text-left">
                    <thead>
                        <tr class="border-b border-stone-200 text-[10px] font-black uppercase tracking-widest text-stone-400 dark:border-zinc-800">
                            <th class="px-3 py-3">Produto</th>
                            <th class="px-3 py-3">Tipo</th>
                            <th class="px-3 py-3">Quantidade</th>
                            <th class="px-3 py-3">Utilizador</th>
                            <th class="px-3 py-3">Data</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100 dark:divide-zinc-800">
                        @forelse($movements as $movement)
                            @php
                                $isExit = $movement->type === 'saida' || (int) $movement->quantity < 0;
                                $isAdjustment = $movement->type === 'ajuste';
                            @endphp
                            <tr class="text-sm">
                                <td class="px-3 py-4">
                                    <div class="font-semibold text-stone-900 dark:text-white">{{ $movement->product?->name ?? 'Produto removido' }}</div>
                                    @if($movement->note)
                                        <div class="mt-1 max-w-xs truncate text-xs text-stone-500">{{ $movement->note }}</div>
                                    @endif
                                </td>
                                <td class="px-3 py-4">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold {{ $isAdjustment ? 'bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300' : ($isExit ? 'bg-red-100 text-red-700 dark:bg-red-950/40 dark:text-red-300' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300') }}">
                                        {{ $isAdjustment ? 'Ajuste' : ($isExit ? 'Saída' : 'Entrada') }}
                                    </span>
                                </td>
                                <td class="px-3 py-4 font-black {{ $isExit ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                    {{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }}
                                </td>
                                <td class="px-3 py-4 text-stone-500 dark:text-zinc-400">{{ $movement->user?->name ?? 'Sistema' }}</td>
                                <td class="px-3 py-4 whitespace-nowrap text-xs text-stone-500 dark:text-zinc-400">{{ $movement->created_at?->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-3 py-12 text-center text-sm text-stone-500">Ainda não existem movimentos de stock.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>