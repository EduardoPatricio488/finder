<div class="min-h-screen bg-zinc-50 p-6 dark:bg-zinc-950 lg:p-8"><div class="mx-auto max-w-7xl space-y-6">
<div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
    <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-8">
        <h1 class="text-3xl font-bold tracking-tight text-zinc-950 dark:text-white">Todas as encomendas</h1>
        <p class="mt-3 max-w-2xl text-sm text-zinc-500 dark:text-zinc-400">Gere as encomendas de todos os teus websites num único catálogo e acompanha o estado de cada pedido.</p>
    </div>
    <div class="rounded-2xl bg-white px-5 py-3 shadow-sm dark:bg-zinc-900">
        <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Total apresentado</p>
        <p class="mt-1 text-xl font-bold text-zinc-950 dark:text-white">{{ $orders->count() }} encomendas</p>
    </div>
</div>
<div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
    <div class="flex flex-col gap-3 lg:flex-row">
        <select wire:model.live="catalogSiteId" class="rounded-xl border-zinc-200 bg-zinc-50 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950 lg:w-72">
            <option value="">Todos os websites</option>
            @foreach($availableSites as $availableSite)
                <option value="{{ $availableSite->id }}">{{ $availableSite->name }}</option>
            @endforeach
        </select>
        <select wire:model.live="status" class="rounded-xl border-zinc-200 bg-zinc-50 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950 lg:w-56">
            <option value="">Todos os estados</option>
            <option value="pendente">Pendente</option><option value="pago">Pago</option><option value="em_preparacao">Em preparação</option><option value="enviado">Enviado</option><option value="entregue">Entregue</option><option value="cancelado">Cancelado</option>
        </select>
    </div>
</div><div class="space-y-4">@forelse($orders as $order)<article class="rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900"><div class="flex flex-col gap-3 border-b border-zinc-100 p-5 sm:flex-row sm:items-center sm:justify-between dark:border-zinc-800"><div><div><p class="font-semibold">#{{ $order->order_number }}</p><p class="mt-1 text-xs text-indigo-600">{{ $order->site?->name ?? 'Website desconhecido' }}</p></div><p class="mt-1 text-xs text-zinc-500">{{ optional($order->sold_at)->format('d/m/Y H:i') }}</p></div><div class="sm:text-right"><p class="font-semibold">€ {{ number_format((float)$order->total,2,',','.') }}</p><span class="text-xs text-zinc-500">{{ ucfirst(str_replace('_',' ',$order->status)) }}</span></div></div><div class="grid gap-2 p-5 md:grid-cols-5">@foreach(['criada'=>'Criada','pago'=>'Pago','em_preparacao'=>'Em preparação','enviado'=>'Enviado','entregue'=>'Entregue'] as $step=>$label)<div class="rounded-xl border p-3 {{ $this->isStepComplete($order,$step) ? 'border-emerald-200 bg-emerald-50' : 'border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800' }}"><p class="text-xs font-semibold {{ $this->isStepComplete($order,$step) ? 'text-emerald-700' : 'text-zinc-500' }}">{{ $this->isStepComplete($order,$step) ? '✓ ' : '' }}{{ $label }}</p></div>@endforeach</div><div class="border-t border-zinc-100 px-5 pb-5 dark:border-zinc-800"><div class="flex flex-wrap gap-2 pt-5">
                @foreach(['pendente'=>'Pendente','pago'=>'Pago','em_preparacao'=>'Em preparação','enviado'=>'Enviado','entregue'=>'Entregue'] as $statusValue=>$statusLabel)
                    @if($order->status !== $statusValue)
                        <button type="button" wire:click="setStatus({{ $order->id }}, '{{ $statusValue }}')" class="rounded-lg border border-zinc-200 px-3 py-2 text-xs font-semibold hover:border-indigo-400 dark:border-zinc-700">{{ $statusLabel }}</button>
                    @endif
                @endforeach
            </div><h2 class="pt-5 text-sm font-semibold">Artigos</h2><div class="divide-y divide-zinc-100 dark:divide-zinc-800">@foreach($order->items as $item)<div class="flex justify-between gap-4 py-3 text-sm"><span>{{ $item->product_name ?? $item->name ?? 'Produto' }} × {{ $item->quantity }}</span><span class="font-medium">€ {{ number_format((float)($item->total ?? $item->subtotal ?? 0),2,',','.') }}</span></div>@endforeach</div></div></article>@empty<div class="rounded-2xl border border-dashed border-zinc-300 bg-white p-12 text-center dark:border-zinc-700 dark:bg-zinc-900"><div class="text-3xl">📦</div><h2 class="mt-3 font-semibold">Ainda não existem encomendas</h2><p class="mt-1 text-sm text-zinc-500">As encomendas dos websites selecionados aparecerão aqui.</p></div>@endforelse</div></div></div>