<div class="min-h-screen bg-zinc-50 dark:bg-zinc-950">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-8">
                <h1 class="text-3xl font-bold tracking-tight text-zinc-950 dark:text-white">Todas as vendas</h1>
                <p class="mt-3 max-w-2xl text-sm text-zinc-500 dark:text-zinc-400">Gere as vendas de todos os teus websites num único catálogo, acompanha as encomendas e mantém tudo organizado.</p>
            </div>
            <div class="flex flex-col items-stretch gap-3 sm:items-end">
                @auth
                    <flux:modal.trigger name="sales-payments-modal">
                        <flux:button variant="primary" icon="credit-card" class="!rounded-xl !bg-indigo-600 hover:!bg-indigo-700">
                            Pagamentos
                        </flux:button>
                    </flux:modal.trigger>
                @endauth
                <div class="rounded-2xl border border-zinc-200 bg-white px-5 py-3 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Total apresentado</p>
                    <p class="mt-1 text-xl font-bold text-zinc-950 dark:text-white">€ {{ number_format($sales->sum(fn($sale) => (float) $sale->total), 2, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-3">
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500">Vendas apresentadas</p>
                <p class="mt-2 text-2xl font-bold text-zinc-950 dark:text-white">{{ $sales->count() }}</p>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500">Em processamento</p>
                <p class="mt-2 text-2xl font-bold text-zinc-950 dark:text-white">{{ $sales->whereIn('status', ['pendente','pago','em_preparacao'])->count() }}</p>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500">Concluídas</p>
                <p class="mt-2 text-2xl font-bold text-zinc-950 dark:text-white">{{ $sales->whereIn('status', ['entregue'])->count() }}</p>
            </div>
        </div>

        <div class="mt-6 rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex flex-col gap-3 lg:flex-row">
                <div class="relative flex-1">
                    <flux:icon name="magnifying-glass" class="pointer-events-none absolute left-3.5 top-1/2 size-5 -translate-y-1/2 text-zinc-400" />
                    <input wire:model.live.debounce.300ms="search" type="search" placeholder="Pesquisar por número ou cliente..." class="w-full rounded-xl border-zinc-200 bg-zinc-50 py-3 pl-11 pr-4 text-sm dark:border-zinc-700 dark:bg-zinc-950">
                </div>
                <select wire:model.live="catalogSiteId" class="rounded-xl border-zinc-200 bg-zinc-50 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950 lg:w-56">
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
        </div>

        <div class="mt-6 overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                <div><h2 class="font-bold text-zinc-950 dark:text-white">Registo de vendas</h2><p class="mt-1 text-xs text-zinc-500">Últimas vendas e respetivo estado.</p></div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-zinc-50 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:bg-zinc-800/50">
                        <tr><th class="px-5 py-4">Encomenda</th><th class="px-5 py-4">Cliente</th><th class="px-5 py-4">Website</th><th class="px-5 py-4">Artigos</th><th class="px-5 py-4">Estado</th><th class="px-5 py-4 text-right">Total</th></tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse($sales as $sale)
                            <tr class="transition hover:bg-zinc-50 dark:hover:bg-zinc-800/40">
                                <td class="px-5 py-4"><p class="font-semibold text-zinc-950 dark:text-white">{{ $sale->order_number }}</p><p class="mt-1 text-xs text-zinc-500">{{ optional($sale->sold_at)->format('d/m/Y H:i') }}</p></td>
                                <td class="px-5 py-4 text-zinc-700 dark:text-zinc-300">{{ $sale->customer?->name ?? '—' }}</td>
                                <td class="px-5 py-4 text-zinc-700 dark:text-zinc-300">{{ $sale->site?->name ?? '—' }}</td>
                                <td class="px-5 py-4">{{ $sale->items->sum('quantity') }}</td>
                                <td class="px-5 py-4"><span class="inline-flex rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-semibold dark:bg-zinc-800">{{ ucfirst(str_replace('_',' ',$sale->status)) }}</span></td>
                                <td class="px-5 py-4 text-right font-bold">€ {{ number_format((float)$sale->total,2,',','.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-16 text-center"><flux:icon name="chart-bar" class="mx-auto size-10 text-zinc-300 dark:text-zinc-600" /><p class="mt-3 font-semibold text-zinc-900 dark:text-white">Ainda não existem vendas</p><p class="mt-1 text-sm text-zinc-500">As vendas da empresa aparecerão aqui.</p></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @auth
            <flux:modal name="sales-payments-modal" class="w-full max-w-6xl">
                <div class="flex max-h-[90vh] flex-col overflow-hidden rounded-2xl">
                    <div class="relative overflow-hidden rounded-t-2xl bg-gradient-to-r from-indigo-700 via-indigo-600 to-violet-600 px-6 py-6 text-white sm:px-8">
                        <div class="absolute -right-8 -top-12 size-40 rounded-full bg-white/10"></div>
                        <div class="absolute -bottom-16 right-24 size-44 rounded-full bg-white/5"></div>
                        <div class="relative flex items-start gap-4">
                            <div class="flex size-12 shrink-0 items-center justify-center rounded-2xl border border-white/20 bg-white/15 shadow-inner">
                                <flux:icon name="credit-card" class="size-6" />
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-bold uppercase tracking-[0.18em] text-indigo-100">Gestão financeira</p>
                                <flux:heading size="xl" class="mt-1 !text-white">Pagamentos</flux:heading>
                                <p class="mt-2 max-w-2xl text-sm leading-6 text-indigo-100">Consulta os pagamentos das encomendas, acompanha os estados e confirma os valores recebidos num único local.</p>
                            </div>
                        </div>
                    </div>
                    <div class="min-h-0 flex-1 overflow-y-auto bg-zinc-50 p-4 dark:bg-zinc-950 sm:p-6">
                        <livewire:payment-manager />
                    </div>
                </div>
            </flux:modal>
        @endauth
    </div>
