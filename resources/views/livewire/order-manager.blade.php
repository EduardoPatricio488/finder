<div class="min-h-screen bg-zinc-50 px-4 py-6 dark:bg-zinc-950 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl space-y-7">
        <header class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-700 via-violet-700 to-purple-800 p-6 text-white shadow-lg shadow-indigo-950/10 sm:p-8">
            <div class="pointer-events-none absolute -right-10 -top-16 h-56 w-56 rounded-full bg-white/10 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-24 right-1/3 h-48 w-48 rounded-full bg-fuchsia-400/20 blur-3xl"></div>
            <div class="relative flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl border border-white/20 bg-white/10 shadow-inner">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5 12 3l9 4.5v9L12 21l-9-4.5v-9Z"/><path stroke-linecap="round" stroke-linejoin="round" d="m3.3 7.7 8.7 4.5 8.7-4.5M12 21v-8.8M7.5 5.2l9 4.6"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-indigo-100">Gestão da loja</p>
                        <h1 class="mt-2 text-3xl font-bold tracking-tight sm:text-4xl">Encomendas</h1>
                        <p class="mt-2 max-w-2xl text-sm leading-6 text-indigo-100 sm:text-base">Acompanha os pedidos, atualiza os estados e gere as encomendas de todos os teus websites num só lugar.</p>
                    </div>
                </div>
                <div class="rounded-2xl border border-white/20 bg-white/10 px-5 py-4 backdrop-blur-sm sm:min-w-44">
                    <p class="text-xs font-semibold uppercase tracking-wider text-indigo-100">Encomendas apresentadas</p>
                    <p class="mt-1 text-3xl font-bold tabular-nums">{{ $orders->count() }}</p>
                    <p class="mt-1 text-xs text-indigo-100">De acordo com os filtros</p>
                </div>
            </div>
        </header>

        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center justify-between gap-3"><p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Total de encomendas</p><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16v13H4zM8 7V5a4 4 0 0 1 8 0v2"/></svg></span></div>
                <p class="mt-4 text-3xl font-bold tracking-tight text-zinc-950 dark:text-white">{{ $orders->count() }}</p><p class="mt-1 text-xs text-zinc-500">Pedidos no resultado atual</p>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center justify-between gap-3"><p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Pendentes</p><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M12 7v5l3 2"/></svg></span></div>
                <p class="mt-4 text-3xl font-bold tracking-tight text-zinc-950 dark:text-white">{{ $orders->where('status', 'pendente')->count() }}</p><p class="mt-1 text-xs text-zinc-500">A aguardar tratamento</p>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center justify-between gap-3"><p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Em preparação</p><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-sky-50 text-sky-700 dark:bg-sky-500/10 dark:text-sky-300"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 8h16v12H4zM8 8V5h8v3M8 12h8M8 16h5"/></svg></span></div>
                <p class="mt-4 text-3xl font-bold tracking-tight text-zinc-950 dark:text-white">{{ $orders->where('status', 'em_preparacao')->count() }}</p><p class="mt-1 text-xs text-zinc-500">A ser preparado ou expedido</p>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center justify-between gap-3"><p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Entregues</p><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/></svg></span></div>
                <p class="mt-4 text-3xl font-bold tracking-tight text-zinc-950 dark:text-white">{{ $orders->where('status', 'entregue')->count() }}</p><p class="mt-1 text-xs text-zinc-500">Pedidos concluídos</p>
            </div>
        </section>

        <section class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-6">
            <div class="mb-4 flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M7 12h10m-7 6h4"/></svg></span>
                <div><h2 class="font-semibold text-zinc-950 dark:text-white">Filtrar encomendas</h2><p class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">Escolhe o website e o estado que pretendes consultar.</p></div>
            </div>
            <div class="grid gap-3 md:grid-cols-2">
                <label class="block"><span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-zinc-500">Website</span><select wire:model.live="catalogSiteId" class="w-full rounded-xl border-zinc-200 bg-zinc-50 px-4 py-3 text-sm text-zinc-800 shadow-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100"><option value="">Todos os websites</option>@foreach($availableSites as $availableSite)<option value="{{ $availableSite->id }}">{{ $availableSite->name }}</option>@endforeach</select></label>
                <label class="block"><span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-zinc-500">Estado</span><select wire:model.live="status" class="w-full rounded-xl border-zinc-200 bg-zinc-50 px-4 py-3 text-sm text-zinc-800 shadow-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100"><option value="">Todos os estados</option><option value="pendente">Pendente</option><option value="pago">Pago</option><option value="em_preparacao">Em preparação</option><option value="enviado">Enviado</option><option value="entregue">Entregue</option><option value="cancelado">Cancelado</option></select></label>
            </div>
        </section>

        <section class="space-y-4">
            <div class="flex flex-wrap items-end justify-between gap-2"><div><h2 class="text-xl font-bold tracking-tight text-zinc-950 dark:text-white">Lista de encomendas</h2><p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Consulta os artigos, acompanha o progresso e atualiza o estado de cada pedido.</p></div><p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ $orders->count() }} resultado(s)</p></div>
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
                <article wire:key="order-{{ $order->id }}" class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm transition hover:border-indigo-200 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-indigo-900">
                    <div class="flex flex-col gap-4 border-b border-zinc-100 bg-zinc-50/70 p-5 dark:border-zinc-800 dark:bg-zinc-900/70 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                        <div class="flex items-start gap-3">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16v13H4zM8 7V5a4 4 0 0 1 8 0v2"/></svg></div>
                            <div><div class="flex flex-wrap items-center gap-2"><h3 class="font-bold text-zinc-950 dark:text-white">Encomenda #{{ $order->order_number }}</h3><span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $statusStyles[$order->status] ?? 'bg-zinc-100 text-zinc-700 ring-zinc-500/20 dark:bg-zinc-800 dark:text-zinc-300' }}">{{ $statusLabels[$order->status] ?? ucfirst(str_replace('_', ' ', $order->status)) }}</span></div><p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $order->site?->name ?? 'Website desconhecido' }}</p><p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">{{ optional($order->sold_at)->format('d/m/Y · H:i') ?? 'Data não disponível' }}</p></div>
                        </div>
                        <div class="sm:text-right"><p class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Total da encomenda</p><p class="mt-1 text-2xl font-bold tracking-tight text-zinc-950 dark:text-white">€ {{ number_format((float) $order->total, 2, ',', '.') }}</p></div>
                    </div>
                    <div class="p-5 sm:p-6">
                        <div class="mb-3 flex items-center justify-between gap-3"><h4 class="text-sm font-semibold text-zinc-800 dark:text-zinc-200">Progresso da encomenda</h4><span class="text-xs text-zinc-500 dark:text-zinc-400">Estado atual: {{ $statusLabels[$order->status] ?? ucfirst(str_replace('_', ' ', $order->status)) }}</span></div>
                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-5">
                            @foreach(['criada' => 'Criada', 'pago' => 'Pago', 'em_preparacao' => 'Em preparação', 'enviado' => 'Enviado', 'entregue' => 'Entregue'] as $step => $label)
                                <div class="flex min-h-12 items-center gap-2 rounded-xl border px-3 py-2 {{ $this->isStepComplete($order, $step) ? 'border-emerald-200 bg-emerald-50/80 dark:border-emerald-900 dark:bg-emerald-500/10' : 'border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-950' }}">
                                    <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full text-xs font-bold {{ $this->isStepComplete($order, $step) ? 'bg-emerald-600 text-white' : 'bg-zinc-100 text-zinc-400 dark:bg-zinc-800' }}">{{ $this->isStepComplete($order, $step) ? '✓' : '·' }}</span><span class="text-xs font-semibold {{ $this->isStepComplete($order, $step) ? 'text-emerald-800 dark:text-emerald-300' : 'text-zinc-500 dark:text-zinc-400' }}">{{ $label }}</span>
                                </div>
                            @endforeach
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
                            <div>
                                <h4 class="mb-3 text-sm font-semibold text-zinc-900 dark:text-white">Atualizar estado</h4>
                                <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-950">
                                    <p class="mb-3 text-xs leading-5 text-zinc-500 dark:text-zinc-400">Seleciona o próximo estado ou corrige o estado atual da encomenda.</p>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach(['pendente' => 'Pendente', 'pago' => 'Pago', 'em_preparacao' => 'Em preparação', 'enviado' => 'Enviado', 'entregue' => 'Entregue'] as $statusValue => $statusLabel)
                                            @if($order->status !== $statusValue)
                                                <button type="button" wire:click="setStatus({{ $order->id }}, '{{ $statusValue }}')" wire:loading.attr="disabled" wire:target="setStatus({{ $order->id }}, '{{ $statusValue }}')" class="inline-flex items-center justify-center rounded-lg border border-zinc-200 bg-white px-3 py-2 text-xs font-semibold text-zinc-700 shadow-sm transition hover:border-indigo-300 hover:bg-indigo-50 hover:text-indigo-700 disabled:cursor-wait disabled:opacity-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:border-indigo-800 dark:hover:bg-indigo-500/10">{{ $statusLabel }}</button>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
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