<div class="space-y-6">
    @if(session('status'))
        <div class="flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300">
            <flux:icon name="check-circle" class="mt-0.5 size-5 shrink-0" />
            <p>{{ session('status') }}</p>
        </div>
    @endif

    <div class="overflow-hidden rounded-3xl border border-indigo-100 bg-gradient-to-br from-indigo-50 via-white to-violet-50 dark:border-indigo-500/20 dark:from-indigo-500/10 dark:via-zinc-900 dark:to-violet-500/10">
        <div class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
            <div class="flex items-start gap-4">
                <div class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-indigo-600 text-white shadow-lg shadow-indigo-600/20">
                    <flux:icon name="ticket" class="size-6" />
                </div>
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-indigo-600 dark:text-indigo-300">Marketing da loja</p>
                    <h3 class="mt-1 text-xl font-bold tracking-tight text-zinc-950 dark:text-white">Promoções e descontos</h3>
                    <p class="mt-1 max-w-xl text-sm leading-6 text-zinc-600 dark:text-zinc-300">Cria campanhas, escolhe os produtos abrangidos e define quando os descontos estão disponíveis.</p>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-2 sm:min-w-48">
                <div class="rounded-2xl border border-white/80 bg-white/80 px-3 py-3 dark:border-zinc-700 dark:bg-zinc-900/70">
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Campanhas</p>
                    <p class="mt-1 text-xl font-bold text-zinc-950 dark:text-white">{{ $promotions->count() }}</p>
                </div>
                <div class="rounded-2xl border border-white/80 bg-white/80 px-3 py-3 dark:border-zinc-700 dark:bg-zinc-900/70">
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Produtos</p>
                    <p class="mt-1 text-xl font-bold text-zinc-950 dark:text-white">{{ $products->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1.05fr)_minmax(0,0.95fr)]">
        <section class="overflow-hidden rounded-3xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="border-b border-zinc-100 px-5 py-4 dark:border-zinc-800 sm:px-6">
                <div class="flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-xl {{ $editingPromotionId ? 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300' : 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300' }}">
                        <flux:icon name="{{ $editingPromotionId ? 'pencil-square' : 'plus' }}" class="size-5" />
                    </div>
                    <div>
                        <h3 class="font-bold text-zinc-950 dark:text-white">{{ $editingPromotionId ? 'Editar promoção' : 'Nova promoção' }}</h3>
                        <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">Define os detalhes da campanha</p>
                    </div>
                </div>
            </div>

            <form wire:submit="save" class="space-y-5 p-5 sm:p-6">
                <div>
                    <label for="promotion-name" class="mb-1.5 block text-sm font-semibold text-zinc-700 dark:text-zinc-200">Nome da promoção</label>
                    <input id="promotion-name" wire:model="name" type="text" placeholder="Ex.: Desconto de outono" class="w-full rounded-xl border-zinc-200 bg-zinc-50 px-4 py-3 text-sm transition focus:border-indigo-500 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                    @error('name')<p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="promotion-discount-type" class="mb-1.5 block text-sm font-semibold text-zinc-700 dark:text-zinc-200">Tipo de desconto</label>
                        <select id="promotion-discount-type" wire:model="discountType" class="w-full rounded-xl border-zinc-200 bg-zinc-50 px-3 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                            <option value="percentagem">Percentagem (%)</option>
                            <option value="fixo">Valor fixo (€)</option>
                        </select>
                    </div>
                    <div>
                        <label for="promotion-discount-value" class="mb-1.5 block text-sm font-semibold text-zinc-700 dark:text-zinc-200">Valor do desconto</label>
                        <div class="relative">
                            <input id="promotion-discount-value" wire:model="discountValue" type="number" min="0.01" step="0.01" placeholder="10" class="w-full rounded-xl border-zinc-200 bg-zinc-50 px-4 py-3 pr-12 text-sm dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                            <span class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-sm font-semibold text-zinc-400">{{ $discountType === 'percentagem' ? '%' : '€' }}</span>
                        </div>
                        @error('discountValue')<p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="promotion-starts-at" class="mb-1.5 block text-sm font-semibold text-zinc-700 dark:text-zinc-200">Data de início</label>
                        <input id="promotion-starts-at" wire:model="startsAt" type="datetime-local" class="w-full min-w-0 rounded-xl border-zinc-200 bg-zinc-50 px-3 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                        @error('startsAt')<p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="promotion-ends-at" class="mb-1.5 block text-sm font-semibold text-zinc-700 dark:text-zinc-200">Data de fim</label>
                        <input id="promotion-ends-at" wire:model="endsAt" type="datetime-local" class="w-full min-w-0 rounded-xl border-zinc-200 bg-zinc-50 px-3 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                        @error('endsAt')<p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="overflow-hidden rounded-2xl border border-zinc-200 dark:border-zinc-700">
                    <div class="flex items-center justify-between gap-3 border-b border-zinc-100 bg-zinc-50 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-800/60">
                        <div>
                            <p class="text-sm font-bold text-zinc-900 dark:text-white">Produtos abrangidos</p>
                            <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">Seleciona um ou mais produtos</p>
                        </div>
                        <span class="rounded-full bg-indigo-100 px-2.5 py-1 text-xs font-bold text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300">{{ count($productIds) }} selecionados</span>
                    </div>
                    <div class="max-h-56 space-y-1 overflow-y-auto p-2">
                        @forelse($products as $product)
                            <label wire:key="promotion-product-{{ $product->id }}" class="group flex cursor-pointer items-center gap-3 rounded-xl border border-transparent px-3 py-3 transition hover:border-indigo-100 hover:bg-indigo-50/70 has-[:checked]:border-indigo-200 has-[:checked]:bg-indigo-50 dark:hover:border-indigo-500/20 dark:hover:bg-indigo-500/5 dark:has-[:checked]:border-indigo-500/30 dark:has-[:checked]:bg-indigo-500/10">
                                <input type="checkbox" wire:model="productIds" value="{{ $product->id }}" class="size-4 rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-sm font-semibold text-zinc-800 dark:text-zinc-100">{{ $product->name }}</span>
                                    <span class="mt-0.5 block text-xs text-zinc-500">{{ $product->category?->name ?? 'Sem categoria' }}</span>
                                </span>
                                <span class="shrink-0 text-sm font-bold text-zinc-900 dark:text-white">€ {{ number_format((float)$product->price, 2, ',', '.') }}</span>
                            </label>
                        @empty
                            <div class="px-4 py-8 text-center">
                                <flux:icon name="cube" class="mx-auto size-8 text-zinc-300 dark:text-zinc-600" />
                                <p class="mt-2 text-sm font-semibold text-zinc-700 dark:text-zinc-200">Sem produtos disponíveis</p>
                                <p class="mt-1 text-xs text-zinc-500">Adiciona produtos ao catálogo para os poderes incluir numa promoção.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
                @error('productIds')<p class="-mt-3 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                @error('productIds.*')<p class="-mt-3 text-xs font-medium text-red-600">{{ $message }}</p>@enderror

                <div class="flex flex-col-reverse gap-2 border-t border-zinc-100 pt-4 dark:border-zinc-800 sm:flex-row sm:justify-end">
                    @if($editingPromotionId)
                        <button type="button" wire:click="resetForm" class="inline-flex items-center justify-center gap-2 rounded-xl border border-zinc-200 px-4 py-3 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">
                            <flux:icon name="x-mark" class="size-4" /> Cancelar edição
                        </button>
                    @endif
                    <button type="submit" wire:loading.attr="disabled" class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-indigo-700 disabled:cursor-wait disabled:opacity-60">
                        <flux:icon name="{{ $editingPromotionId ? 'check' : 'plus' }}" class="size-4" />
                        <span wire:loading.remove>{{ $editingPromotionId ? 'Guardar alterações' : 'Criar promoção' }}</span>
                        <span wire:loading>A guardar...</span>
                    </button>
                </div>
            </form>
        </section>

        <section class="overflow-hidden rounded-3xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between gap-3 border-b border-zinc-100 px-5 py-4 dark:border-zinc-800 sm:px-6">
                <div class="flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-xl bg-violet-50 text-violet-700 dark:bg-violet-500/10 dark:text-violet-300">
                        <flux:icon name="squares-2x2" class="size-5" />
                    </div>
                    <div>
                        <h3 class="font-bold text-zinc-950 dark:text-white">Promoções criadas</h3>
                        <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">Consulta e gere as campanhas</p>
                    </div>
                </div>
                <span class="inline-flex size-9 items-center justify-center rounded-xl bg-zinc-100 text-sm font-bold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">{{ $promotions->count() }}</span>
            </div>

            <div class="max-h-[34rem] space-y-3 overflow-y-auto p-4 sm:p-5">
                @forelse($promotions as $promotion)
                    @php
                        $active = $promotion->starts_at <= now() && $promotion->ends_at >= now();
                        $ended = $promotion->ends_at < now();
                    @endphp
                    <article wire:key="promotion-card-{{ $promotion->id }}" class="rounded-2xl border border-zinc-200 p-4 transition hover:border-indigo-200 hover:shadow-sm dark:border-zinc-700 dark:hover:border-indigo-500/40">
                        <div class="flex items-start gap-3">
                            <div class="flex size-11 shrink-0 items-center justify-center rounded-xl {{ $active ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300' : 'bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-300' }}">
                                <flux:icon name="ticket" class="size-5" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h4 class="break-words font-bold text-zinc-950 dark:text-white">{{ $promotion->name }}</h4>
                                    <span class="rounded-full px-2.5 py-1 text-[11px] font-bold {{ $active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300' : ($ended ? 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-500/15 dark:text-amber-300') }}">{{ $active ? 'Ativa' : ($ended ? 'Terminada' : 'Agendada') }}</span>
                                </div>
                                <p class="mt-2 text-2xl font-extrabold tracking-tight text-indigo-600 dark:text-indigo-300">{{ $promotion->discount_type === 'percentagem' ? number_format((float)$promotion->discount_value, 2, ',', '.') . '%' : '€ ' . number_format((float)$promotion->discount_value, 2, ',', '.') }} <span class="text-xs font-semibold text-zinc-500">de desconto</span></p>
                            </div>
                        </div>
                        <div class="mt-4 grid grid-cols-2 gap-2">
                            <div class="rounded-xl bg-zinc-50 px-3 py-2.5 dark:bg-zinc-800/60">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Início</p>
                                <p class="mt-1 text-xs font-semibold text-zinc-800 dark:text-zinc-200">{{ $promotion->starts_at?->format('d/m/Y H:i') ?? '—' }}</p>
                            </div>
                            <div class="rounded-xl bg-zinc-50 px-3 py-2.5 dark:bg-zinc-800/60">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Fim</p>
                                <p class="mt-1 text-xs font-semibold text-zinc-800 dark:text-zinc-200">{{ $promotion->ends_at?->format('d/m/Y H:i') ?? '—' }}</p>
                            </div>
                        </div>
                        <div class="mt-3 flex items-center justify-between gap-3">
                            <span class="inline-flex items-center gap-1.5 text-xs text-zinc-500 dark:text-zinc-400">
                                <flux:icon name="cube" class="size-4" /> {{ $promotion->products_count }} {{ $promotion->products_count === 1 ? 'produto abrangido' : 'produtos abrangidos' }}
                            </span>
                            <div class="flex shrink-0 items-center gap-1.5">
                                <button type="button" wire:click="edit({{ $promotion->id }})" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-200 px-3 py-2 text-xs font-bold text-zinc-700 transition hover:border-indigo-300 hover:bg-indigo-50 hover:text-indigo-700 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-indigo-500/10" aria-label="Editar {{ $promotion->name }}">
                                    <flux:icon name="pencil-square" class="size-3.5" /> Editar
                                </button>
                                <button type="button" wire:click="delete({{ $promotion->id }})" wire:confirm="Tens a certeza de que queres eliminar esta promoção?" class="inline-flex items-center justify-center rounded-lg border border-red-200 p-2 text-red-600 transition hover:bg-red-50 dark:border-red-500/20 dark:text-red-400 dark:hover:bg-red-500/10" aria-label="Eliminar {{ $promotion->name }}">
                                    <flux:icon name="trash" class="size-4" />
                                </button>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="flex min-h-64 flex-col items-center justify-center rounded-2xl border border-dashed border-zinc-300 bg-zinc-50/70 px-6 py-10 text-center dark:border-zinc-700 dark:bg-zinc-950/40">
                        <div class="flex size-14 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300">
                            <flux:icon name="ticket" class="size-7" />
                        </div>
                        <h4 class="mt-4 font-bold text-zinc-900 dark:text-white">Ainda não existem promoções</h4>
                        <p class="mt-1 max-w-xs text-sm leading-6 text-zinc-500 dark:text-zinc-400">As tuas campanhas vão aparecer aqui. Preenche o formulário e cria a primeira promoção.</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</div>
