<div class="mx-auto max-w-7xl space-y-7 px-6 py-8 lg:px-10">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <div class="text-xs font-bold uppercase tracking-[0.22em] text-indigo-600 dark:text-indigo-400">Resumo do negócio</div>
            <h1 class="mt-2 text-3xl font-black tracking-tight text-zinc-950 dark:text-white">Visão geral</h1>
            <p class="mt-2 text-sm text-zinc-500">Vendas, pagamentos, produtos e atividade num único painel. Apenas consulta, sem ações de edição.</p>
            <p class="mt-1 text-xs font-semibold text-zinc-400">{{ $site->name }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <flux:button href="{{ route('site.manage.dashboard') }}" variant="ghost" icon="squares-2x2">Painel de gestão</flux:button>
            <flux:button href="{{ route('site.public', ['site' => $site, 'preview' => 1]) }}" target="_blank" variant="outline" icon="eye">Ver website</flux:button>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between text-sm font-medium text-zinc-500"><span>Receita deste mês</span><span class="rounded-xl bg-emerald-50 p-2 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300"><flux:icon name="banknotes" class="size-5" /></span></div>
            <div class="mt-4 text-3xl font-black tracking-tight text-zinc-950 dark:text-white">{{ number_format($monthRevenue, 2, ',', '.') }} €</div>
            <div class="mt-2 text-xs text-zinc-500">{{ $monthSales }} encomenda(s) este mês</div>
        </div>
        <div class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between text-sm font-medium text-zinc-500"><span>Receita total</span><span class="rounded-xl bg-sky-50 p-2 text-sky-700 dark:bg-sky-950 dark:text-sky-300"><flux:icon name="chart-bar" class="size-5" /></span></div>
            <div class="mt-4 text-3xl font-black tracking-tight text-zinc-950 dark:text-white">{{ number_format($allRevenue, 2, ',', '.') }} €</div>
            <div class="mt-2 text-xs text-zinc-500">Encomendas com pagamento confirmado</div>
        </div>
        <div class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between text-sm font-medium text-zinc-500"><span>Encomendas</span><span class="rounded-xl bg-violet-50 p-2 text-violet-700 dark:bg-violet-950 dark:text-violet-300"><flux:icon name="shopping-bag" class="size-5" /></span></div>
            <div class="mt-4 text-3xl font-black tracking-tight text-zinc-950 dark:text-white">{{ number_format($totalOrders, 0, ',', '.') }}</div>
            <div class="mt-2 text-xs text-zinc-500">{{ $pendingOrders }} pendente(s) ou em preparação</div>
        </div>
        <div class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between text-sm font-medium text-zinc-500"><span>Produtos ativos</span><span class="rounded-xl bg-amber-50 p-2 text-amber-700 dark:bg-amber-950 dark:text-amber-300"><flux:icon name="archive-box" class="size-5" /></span></div>
            <div class="mt-4 text-3xl font-black tracking-tight text-zinc-950 dark:text-white">{{ number_format($activeProducts, 0, ',', '.') }} <span class="text-sm font-semibold text-zinc-400">/ {{ $totalProducts }}</span></div>
            <div class="mt-2 text-xs {{ $lowStockProducts > 0 ? 'font-semibold text-amber-600' : 'text-zinc-500' }}">{{ $lowStockProducts }} produto(s) com stock baixo</div>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        <section class="rounded-2xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900 xl:col-span-2">
            <div class="flex items-start justify-between gap-3"><div><h2 class="text-lg font-bold text-zinc-950 dark:text-white">Vendas ao longo do tempo</h2><p class="mt-1 text-sm text-zinc-500">Receita por mês, nos últimos seis meses.</p></div><flux:icon name="chart-bar" class="size-5 text-zinc-400" /></div>
            <div class="mt-7 grid grid-cols-6 items-end gap-3">
                @foreach($monthlySales as $month)
                    <div class="flex min-w-0 flex-col items-center gap-2">
                        <div class="text-center text-[10px] font-semibold text-zinc-500">{{ number_format($month['total'], 0, ',', '.') }} €</div>
                        <div class="flex h-36 w-full items-end overflow-hidden rounded-lg bg-zinc-100 dark:bg-zinc-800">
                            <div class="w-full rounded-lg bg-indigo-500 transition-all" style="height: {{ $month['total'] > 0 ? max(5, ($month['total'] / $maxMonthlySales) * 100) : 0 }}%"></div>
                        </div>
                        <div class="text-xs font-semibold capitalize text-zinc-500">{{ $month['label'] }}</div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="rounded-2xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
            <h2 class="text-lg font-bold text-zinc-950 dark:text-white">Estado dos pagamentos</h2>
            <p class="mt-1 text-sm text-zinc-500">Resumo dos registos de pagamento.</p>
            <div class="mt-5 space-y-4">
                @foreach($paymentStatusCounts as $label => $count)
                    <div>
                        <div class="mb-2 flex justify-between text-sm"><span class="text-zinc-600 dark:text-zinc-300">{{ $label }}</span><span class="font-bold text-zinc-950 dark:text-white">{{ $count }}</span></div>
                        <div class="h-2 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800"><div class="h-full rounded-full {{ $label === 'Recebidos' ? 'bg-emerald-500' : ($label === 'Pendentes' ? 'bg-amber-500' : 'bg-rose-500') }}" style="width: {{ $totalPayments > 0 ? ($count / $totalPayments) * 100 : 0 }}%"></div></div>
                    </div>
                @endforeach
                @if($totalPayments === 0)<p class="rounded-xl bg-zinc-50 p-3 text-sm text-zinc-500 dark:bg-zinc-800">Ainda não existem pagamentos registados.</p>@endif
            </div>
            <div class="mt-6 border-t border-zinc-100 pt-4 dark:border-zinc-800"><div class="text-xs text-zinc-500">Receita de hoje</div><div class="mt-1 text-2xl font-black text-zinc-950 dark:text-white">{{ number_format($todayRevenue, 2, ',', '.') }} €</div><div class="mt-1 text-xs text-zinc-500">{{ $todaySales }} encomenda(s) hoje</div></div>
        </section>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <section class="overflow-hidden rounded-2xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between border-b border-zinc-100 px-6 py-5 dark:border-zinc-800"><div><h2 class="text-lg font-bold text-zinc-950 dark:text-white">Encomendas recentes</h2><p class="mt-1 text-sm text-zinc-500">Últimas seis encomendas registadas.</p></div><flux:icon name="receipt" class="size-5 text-zinc-400" /></div>
            @if($recentOrders->isEmpty())
                <p class="p-6 text-sm text-zinc-500">Ainda não existem encomendas para apresentar.</p>
            @else
                <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @foreach($recentOrders as $order)
                        <div class="flex items-center justify-between gap-4 px-6 py-4">
                            <div class="min-w-0"><div class="truncate text-sm font-bold text-zinc-900 dark:text-white">#{{ $order->order_number }} · {{ $order->customer?->name ?? 'Cliente sem nome' }}</div><div class="mt-1 text-xs text-zinc-500">{{ $order->sold_at?->format('d/m/Y H:i') ?? 'Sem data' }} · {{ str_replace('_', ' ', ucfirst($order->status)) }}</div></div>
                            <div class="shrink-0 text-sm font-bold text-zinc-950 dark:text-white">{{ number_format((float) $order->total, 2, ',', '.') }} €</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="overflow-hidden rounded-2xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between border-b border-zinc-100 px-6 py-5 dark:border-zinc-800"><div><h2 class="text-lg font-bold text-zinc-950 dark:text-white">Produtos em destaque</h2><p class="mt-1 text-sm text-zinc-500">Produtos com mais unidades associadas a encomendas.</p></div><flux:icon name="star" class="size-5 text-zinc-400" /></div>
            @if($topProducts->isEmpty())
                <p class="p-6 text-sm text-zinc-500">Ainda não existem produtos para apresentar.</p>
            @else
                <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @foreach($topProducts as $index => $product)
                        <div class="flex items-center gap-3 px-6 py-4">
                            <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-zinc-100 text-sm font-black text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">{{ $index + 1 }}</div>
                            <div class="min-w-0 flex-1"><div class="truncate text-sm font-bold text-zinc-900 dark:text-white">{{ $product->name }}</div><div class="mt-1 text-xs text-zinc-500">{{ number_format((float) $product->price, 2, ',', '.') }} € · {{ $product->units_sold }} unidade(s)</div></div>
                            <div class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $product->is_active ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-zinc-100 text-zinc-500 dark:bg-zinc-800' }}">{{ $product->is_active ? 'Ativo' : 'Inativo' }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>

    <p class="text-center text-xs text-zinc-400">Os valores apresentados correspondem aos registos existentes neste website. Este painel é apenas de consulta.</p>
</div>
