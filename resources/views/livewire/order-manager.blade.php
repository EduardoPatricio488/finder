<div class="min-h-screen bg-transparent px-4 py-6 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl space-y-7">
        <header class="relative isolate overflow-hidden rounded-2xl border border-zinc-200 bg-transparent px-5 py-6 dark:border-zinc-800 sm:px-7 sm:py-7">
            
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="mb-2 inline-flex items-center gap-2 rounded-full border border-zinc-200 bg-transparent px-3 py-1 text-xs font-semibold text-indigo-700 dark:border-zinc-700 dark:text-indigo-300"><span class="h-1.5 w-1.5 rounded-full bg-indigo-500"></span> Gestão de vendas</div>
                    <h1 class="text-2xl font-bold tracking-tight text-zinc-950 dark:text-white sm:text-3xl">Encomendas</h1>
                    <p class="mt-2 max-w-xl text-sm leading-6 text-zinc-600 dark:text-zinc-400">Acompanha os pedidos, consulta os artigos e mantém cada encomenda atualizada num só lugar.</p>
                </div>
                <div class="flex items-center gap-3 rounded-xl border border-zinc-200 bg-transparent px-4 py-3 dark:border-zinc-700">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-100 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16v13H4zM8 7V5a4 4 0 0 1 8 0v2"/></svg></span>
                    <div><p class="text-xs text-zinc-500 dark:text-zinc-400">Resultados visíveis</p><p class="text-xl font-bold tabular-nums text-zinc-900 dark:text-white">{{ $orders->count() }}</p></div>
                </div>
            </div>
        </header>

        <section class="grid grid-cols-2 gap-3 xl:grid-cols-4">
            <div class="group rounded-xl border border-zinc-200 bg-transparent px-4 py-4 transition duration-200 hover:-translate-y-0.5 hover:border-indigo-200 dark:border-zinc-800 dark:hover:border-indigo-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Total de encomendas</p>
                <p class="mt-2 text-2xl font-semibold tabular-nums text-zinc-900 dark:text-white">{{ $orders->count() }}</p>
                <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">Pedidos no resultado atual</p>
            </div>
            <div class="group rounded-xl border border-zinc-200 bg-white px-4 py-4 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-indigo-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Pendentes</p>
                <p class="mt-2 text-2xl font-semibold tabular-nums text-zinc-900 dark:text-white">{{ $orders->where('status', 'pendente')->count() }}</p>
                <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">A aguardar tratamento</p>
            </div>
            <div class="group rounded-xl border border-zinc-200 bg-white px-4 py-4 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-indigo-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Em preparação</p>
                <p class="mt-2 text-2xl font-semibold tabular-nums text-zinc-900 dark:text-white">{{ $orders->where('status', 'em_preparacao')->count() }}</p>
                <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">A ser preparado ou expedido</p>
            </div>
            <div class="group rounded-xl border border-zinc-200 bg-white px-4 py-4 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-indigo-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Entregues</p>
                <p class="mt-2 text-2xl font-semibold tabular-nums text-zinc-900 dark:text-white">{{ $orders->where('status', 'entregue')->count() }}</p>
                <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">Pedidos concluídos</p>
            </div>
        </section>

        <section class="space-y-4 rounded-2xl border border-zinc-200/90 bg-transparent p-4 dark:border-zinc-800 sm:p-5">
            <div class="flex flex-wrap items-end justify-between gap-3"><div><div class="mb-1 flex items-center gap-2"><span class="h-5 w-1 rounded-full bg-indigo-500"></span><h2 class="text-xl font-bold tracking-tight text-zinc-950 dark:text-white">Lista de encomendas</h2></div><p class="text-sm text-zinc-500 dark:text-zinc-400">Consulta os artigos, acompanha o progresso e atualiza o estado de cada pedido.</p></div><span class="inline-flex items-center gap-2 rounded-full bg-zinc-100 px-3 py-1.5 text-xs font-semibold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300"><span class="h-1.5 w-1.5 rounded-full bg-indigo-500"></span>{{ $orders->count() }} resultado(s)</span></div>
            <div class="rounded-xl border border-zinc-200 bg-transparent p-4 dark:border-zinc-800">
                <div class="mb-2 flex items-center justify-between gap-3">
                    <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">Filtrar encomendas</h3>
                    <span class="text-xs text-zinc-400 dark:text-zinc-500">Refinar resultados</span>
                </div>
                <div class="grid gap-3 md:grid-cols-2">
                    <label class="block"><span class="mb-1.5 block text-xs text-zinc-500 dark:text-zinc-400">Website</span><select wire:model.live="catalogSiteId" class="w-full rounded-lg border-zinc-200 bg-white px-3 py-2.5 text-sm text-zinc-800 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"><option value="">Todos os websites</option>@foreach($availableSites as $availableSite)<option value="{{ $availableSite->id }}">{{ $availableSite->name }}</option>@endforeach</select></label>
                    <label class="block"><span class="mb-1.5 block text-xs text-zinc-500 dark:text-zinc-400">Estado</span><select wire:model.live="status" class="w-full rounded-lg border-zinc-200 bg-white px-3 py-2.5 text-sm text-zinc-800 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"><option value="">Todos os estados</option><option value="pendente">Pendente</option><option value="pago">Pago</option><option value="em_preparacao">Em preparação</option><option value="enviado">Enviado</option><option value="entregue">Entregue</option><option value="cancelado">Cancelado</option></select></label>
                </div>
            </div>
            @forelse($orders as $order)
                @php
                    $statusStyles = [
                        'pendente' => 'bg-amber-50 text-amber-800 ring-amber-600/20 dark:bg-amber-500/10 dark:text-amber-300',
                        'pago' => 'bg-blue-50 text-blue-800 ring-blue-600/20 dark:bg-blue-500/10 dark:text-blue-300',
                        'em_preparacao' => 'bg-violet-50 text-violet-800 ring-violet-600/20 dark:bg-violet-500/10 dark:text-violet-300',
                        'enviado' => 'bg-sky-50 text-sky-800 ring-sky-600/20 dark:bg-sky-500/10 dark:text-sky-300',
                        'entregue' => 'bg-emerald-50 text-emerald-800 ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-300',
                        'cancelado' => 'bg-rose-50 text-rose-800 ring-rose-600/20 dark:bg-rose-500/10 dark:text-rose-300',
                    ];
                    $statusLabels = ['pendente' => 'Pendente', 'pago' => 'Pago', 'em_preparacao' => 'Em preparação', 'enviado' => 'Enviado', 'entregue' => 'Entregue', 'cancelado' => 'Cancelado'];
                @endphp
                <article wire:key="order-{{ $order->id }}" x-data="{ expanded: false, confirmStatus: null, confirmStatusLabel: '' }" class="group/order overflow-hidden rounded-2xl border border-zinc-200 bg-transparent transition duration-200 hover:-translate-y-0.5 hover:border-indigo-300 dark:border-zinc-800 dark:hover:border-indigo-800">
                    <button type="button" x-on:click="expanded = !expanded" x-bind:aria-expanded="expanded.toString()" class="flex w-full flex-col gap-4 border-b border-zinc-100 bg-zinc-50/70 p-5 text-left transition hover:bg-zinc-100/70 dark:border-zinc-800 dark:bg-zinc-900/70 dark:hover:bg-zinc-800/50 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                        <div class="flex items-start gap-3">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16v13H4zM8 7V5a4 4 0 0 1 8 0v2"/></svg></div>
                            <div><div class="flex flex-wrap items-center gap-2"><h3 class="font-bold text-zinc-950 dark:text-white">Encomenda #{{ $order->order_number }}</h3><span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $statusStyles[$order->status] ?? 'bg-zinc-100 text-zinc-700 ring-zinc-500/20 dark:bg-zinc-800 dark:text-zinc-300' }}">{{ $statusLabels[$order->status] ?? ucfirst(str_replace('_', ' ', $order->status)) }}</span></div><p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $order->site?->name ?? 'Website desconhecido' }}</p><p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">{{ optional($order->sold_at)->format('d/m/Y · H:i') ?? 'Data não disponível' }}</p></div>
                        </div>
                        <div class="flex items-center justify-between gap-4 sm:justify-end sm:gap-5">
                            <div class="sm:text-right"><p class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Total da encomenda</p><p class="mt-1 text-2xl font-bold tracking-tight text-zinc-950 dark:text-white">€ {{ number_format((float) $order->total, 2, ',', '.') }}</p></div>
                            <svg x-bind:class="expanded ? 'rotate-180' : ''" class="h-5 w-5 shrink-0 text-zinc-500 transition-transform duration-200 dark:text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                        </div>
                    </button>
                    <div x-cloak x-show="expanded" class="p-5 sm:p-6">
                        <div class="rounded-xl border border-zinc-200 bg-zinc-50/70 p-4 dark:border-zinc-800 dark:bg-zinc-950/40 sm:p-5">
                            <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                                <h4 class="text-sm font-semibold text-zinc-900 dark:text-white">Progresso e estado da encomenda</h4>
                                <span class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-800 dark:bg-indigo-500/15 dark:text-indigo-200">Estado atual: {{ $statusLabels[$order->status] ?? ucfirst(str_replace('_', ' ', $order->status)) }}</span>
                            </div>
                            <p class="mb-4 text-sm text-zinc-600 dark:text-zinc-400">Seleciona uma etapa para atualizar o estado da encomenda. As etapas concluídas aparecem a verde.</p>
                            <div wire:loading.flex wire:target="setStatus" role="status" aria-live="polite" class="mb-4 items-center gap-2 rounded-lg border border-indigo-200 px-3 py-2.5 text-sm font-medium text-indigo-700 dark:border-indigo-800 dark:text-indigo-300">
                                <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"></path></svg>
                                <span>A alterar o estado da encomenda...</span>
                            </div>
                            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-5">
                                @foreach(['criada' => 'Criada', 'pendente' => 'Pendente', 'pago' => 'Pago', 'em_preparacao' => 'Em preparação', 'enviado' => 'Enviado', 'entregue' => 'Entregue'] as $step => $label)
                                    @php
                                        $stepStatus = $step === 'criada' ? null : $step;
                                        $isCurrentStatus = $stepStatus !== null && $order->status === $stepStatus;
                                        $isComplete = $this->isStepComplete($order, $step);
                                    @endphp
                                    @if($step === 'criada')
                                        <div class="flex min-h-12 items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2.5 dark:border-emerald-900 dark:bg-emerald-500/10">
                                            <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-600 text-xs font-bold text-white">✓</span>
                                            <span class="text-sm font-semibold text-emerald-800 dark:text-emerald-300">{{ $label }}</span>
                                        </div>
                                    @elseif($isCurrentStatus)
                                        <div aria-current="step" class="flex min-h-12 items-center gap-2 rounded-lg border border-indigo-300 bg-indigo-50 px-3 py-2.5 dark:border-indigo-700 dark:bg-indigo-500/10">
                                            <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-xs font-bold text-white dark:bg-indigo-400 dark:text-zinc-950">✓</span>
                                            <span class="min-w-0 flex-1 text-sm font-semibold text-indigo-800 dark:text-indigo-200">{{ $label }}</span>
                                            <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-700 dark:text-indigo-300">Atual</span>
                                        </div>
                                    @else
                                        <button type="button" x-on:click="confirmStatus = '{{ $stepStatus }}'; confirmStatusLabel = '{{ $label }}'" class="group flex min-h-12 items-center gap-2 rounded-lg border px-3 py-2.5 text-left transition disabled:cursor-wait disabled:opacity-50 {{ $isComplete ? 'border-emerald-200 bg-emerald-50 hover:border-indigo-300 hover:bg-indigo-50 dark:border-emerald-900 dark:bg-emerald-500/10 dark:hover:border-indigo-700 dark:hover:bg-indigo-500/10' : 'border-zinc-200 bg-white hover:border-indigo-300 hover:bg-indigo-50 dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-indigo-700 dark:hover:bg-indigo-500/10' }}">
                                            <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border text-xs {{ $isComplete ? 'border-emerald-600 bg-emerald-600 text-white' : 'border-zinc-300 text-zinc-400 group-hover:border-indigo-400 group-hover:text-indigo-600 dark:border-zinc-600' }}">{{ $isComplete ? '✓' : '→' }}</span>
                                            <span class="min-w-0 flex-1 text-sm font-medium {{ $isComplete ? 'text-emerald-800 dark:text-emerald-300' : 'text-zinc-700 dark:text-zinc-200' }}">{{ $label }}</span>
                                        </button>
                                    @endif
                                @endforeach
                            </div>
                            @if($order->status !== 'pendente')
                                <div class="mt-3 flex justify-end">
                                    <button type="button" x-on:click="confirmStatus = 'pendente'; confirmStatusLabel = 'Pendente'" class="rounded-lg px-3 py-2 text-xs font-semibold text-zinc-500 transition hover:bg-white hover:text-indigo-700 disabled:opacity-50 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-indigo-300">Repor estado para Pendente</button>
                                </div>
                            @endif
                        </div>
                        <div class="mt-6 grid gap-5 border-t border-zinc-100 pt-5 dark:border-zinc-800 lg:grid-cols-[minmax(0,1fr)_minmax(280px,0.8fr)]">
                            <div>
                                <div class="mb-3 flex items-center justify-between gap-3"><h4 class="text-sm font-semibold text-zinc-900 dark:text-white">Artigos encomendados</h4><span class="text-xs text-zinc-500">{{ $order->items->count() }} artigo(s)</span></div>
                                <div class="divide-y divide-zinc-100 rounded-xl border border-zinc-100 px-3 dark:divide-zinc-800 dark:border-zinc-800">
                                    @forelse($order->items as $item)
                                        <div class="flex items-center justify-between gap-4 py-3 text-sm"><div class="min-w-0"><p class="truncate font-medium text-zinc-800 dark:text-zinc-200">{{ $item->product_name ?? $item->name ?? 'Produto' }}</p><p class="mt-0.5 text-xs text-zinc-500">Quantidade: {{ $item->quantity }}</p></div><span class="shrink-0 font-semibold tabular-nums text-zinc-900 dark:text-white">€ {{ number_format((float) ($item->total ?? $item->subtotal ?? 0), 2, ',', '.') }}</span></div>
                                    @empty
                                        <p class="py-4 text-sm text-zinc-500">Não existem artigos associados a esta encomenda.</p>
                                    @endforelse
                                </div>
                            </div>

                        </div>
                    </div>
                    <div x-cloak x-show="confirmStatus !== null" x-transition.opacity class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 p-4" role="dialog" aria-modal="true" aria-labelledby="order-status-confirm-title" x-on:keydown.escape.window="confirmStatus = null; confirmStatusLabel = ''">
                        <div x-on:click.outside="confirmStatus = null; confirmStatusLabel = ''" x-transition class="w-full max-w-md rounded-2xl border border-zinc-200 bg-white p-6 shadow-2xl dark:border-zinc-700 dark:bg-zinc-900">
                            <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-indigo-50 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-300">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.9 1.9 18a2 2 0 0 0 1.7 3h16.8a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/></svg>
                            </div>
                            <h3 id="order-status-confirm-title" class="text-lg font-bold text-zinc-950 dark:text-white">Confirmar alteração do estado</h3>
                            <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-300">Estás prestes a alterar o estado da encomenda <strong>#{{ $order->order_number }}</strong> para <strong x-text="confirmStatusLabel"></strong>. Queres continuar?</p>
                            <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                                <button type="button" x-on:click="confirmStatus = null; confirmStatusLabel = ''" class="rounded-xl border border-zinc-200 px-4 py-2.5 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">Cancelar</button>
                                <button type="button" x-on:click="$wire.setStatus({{ $order->id }}, confirmStatus).then(() => { confirmStatus = null; confirmStatusLabel = '' })" wire:loading.attr="disabled" wire:target="setStatus" class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:cursor-wait disabled:opacity-60">
                                    <span wire:loading.remove wire:target="setStatus">Confirmar alteração</span>
                                    <span wire:loading.flex wire:target="setStatus" class="items-center gap-2"><svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"></path></svg>A alterar...</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-zinc-300 bg-white px-6 py-16 text-center dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300"><svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16v13H4zM8 7V5a4 4 0 0 1 8 0v2"/></svg></div>
                    <h3 class="mt-5 text-lg font-bold text-zinc-900 dark:text-white">Não foram encontradas encomendas</h3>
                    <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-zinc-500 dark:text-zinc-400">Não existem encomendas para os filtros selecionados. Experimenta escolher outro website ou selecionar todos os estados.</p>
                    <div class="mt-5 flex flex-wrap justify-center gap-3"><button type="button" wire:click="$set('catalogSiteId', '')" class="rounded-xl border border-zinc-200 px-4 py-2.5 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">Todos os websites</button><button type="button" wire:click="$set('status', '')" class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700">Limpar estado</button></div>
                </div>
            @endforelse
        </section>
    </div>
</div>