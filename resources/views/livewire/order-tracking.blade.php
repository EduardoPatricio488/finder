<div class="min-h-full bg-stone-50">
    <div class="mx-auto max-w-5xl px-5 py-8 sm:px-8 lg:py-12">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-amber-700">Encomendas</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight text-stone-950 sm:text-4xl">Acompanhamento</h1>
                <p class="mt-2 text-sm text-stone-600">Consulta o estado e os detalhes das tuas encomendas.</p>
            </div>
            <a href="{{ route('dashboard') }}" wire:navigate class="inline-flex w-fit rounded-lg border border-stone-200 bg-white px-4 py-2.5 text-sm font-semibold text-stone-700 hover:bg-stone-100">← Dashboard</a>
        </div>

        <div class="mt-8 space-y-5">
            @forelse ($orders as $order)
                <article class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
                    <div class="flex flex-col gap-4 border-b border-stone-100 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
                        <div>
                            <p class="text-sm font-semibold text-stone-950">Encomenda #{{ $order->order_number }}</p>
                            <p class="mt-1 text-xs text-stone-500">{{ optional($order->sold_at)->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="text-left sm:text-right">
                            <p class="text-lg font-semibold text-stone-950">€ {{ number_format((float) $order->total, 2, ',', '.') }}</p>
                            <span class="mt-1 inline-flex rounded-full bg-stone-100 px-2.5 py-1 text-xs font-semibold text-stone-700">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span>
                        </div>
                    </div>

                    <div class="p-5 sm:p-6">
                        <div class="grid gap-3 sm:grid-cols-5">
                            @foreach (['criada' => 'Criada', 'pago' => 'Pago', 'em_preparacao' => 'Em preparação', 'enviado' => 'Enviado', 'entregue' => 'Entregue'] as $step => $label)
                                <div class="rounded-xl border {{ $this->isStepComplete($order, $step) ? 'border-emerald-200 bg-emerald-50' : 'border-stone-200 bg-stone-50' }} p-3">
                                    <div class="flex items-center gap-2">
                                        <span class="flex size-7 items-center justify-center rounded-full {{ $this->isStepComplete($order, $step) ? 'bg-emerald-600 text-white' : 'bg-stone-200 text-stone-500' }}">
                                            {{ $this->isStepComplete($order, $step) ? '✓' : '•' }}
                                        </span>
                                        <span class="text-xs font-semibold {{ $this->isStepComplete($order, $step) ? 'text-emerald-800' : 'text-stone-500' }}">{{ $label }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if ($order->items->isNotEmpty())
                            <div class="mt-6 border-t border-stone-100 pt-5">
                                <h2 class="text-sm font-semibold text-stone-950">Artigos</h2>
                                <div class="mt-3 divide-y divide-stone-100">
                                    @foreach ($order->items as $item)
                                        <div class="flex items-center justify-between gap-4 py-3 text-sm">
                                            <div class="min-w-0">
                                                <p class="truncate font-medium text-stone-800">{{ $item->product_name ?? $item->name ?? 'Produto' }}</p>
                                                <p class="text-xs text-stone-500">Quantidade: {{ $item->quantity }}</p>
                                            </div>
                                            <span class="shrink-0 font-semibold text-stone-700">€ {{ number_format((float) ($item->total ?? $item->subtotal ?? 0), 2, ',', '.') }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-stone-300 bg-white px-6 py-16 text-center">
                    <div class="mx-auto flex size-14 items-center justify-center rounded-full bg-stone-100 text-2xl">📦</div>
                    <h2 class="mt-4 text-lg font-semibold text-stone-950">Ainda não tens encomendas</h2>
                    <p class="mt-2 text-sm text-stone-500">Quando fizeres uma encomenda, poderás acompanhar aqui o respetivo estado.</p>
                    <a href="{{ route('products') }}" wire:navigate class="mt-6 inline-flex rounded-lg bg-stone-950 px-4 py-2.5 text-sm font-semibold text-white">Explorar produtos</a>
                </div>
            @endforelse
        </div>
    </div>
</div>