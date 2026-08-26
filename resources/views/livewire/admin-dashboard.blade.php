<div class="mx-auto max-w-7xl space-y-8 px-4 py-6 sm:px-6 lg:px-8">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.18em] text-amber-700">Visão geral</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-stone-950 dark:text-white">{{ $site->name }}</h1>
            <p class="mt-2 max-w-2xl text-sm text-stone-600 dark:text-zinc-400">Acompanhe o catálogo, a comunidade e o movimento deste site num só lugar.</p>
        </div>
        <a href="{{ route('admin.site.products', $site) }}" wire:navigate class="inline-flex items-center justify-center rounded-lg bg-stone-950 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-stone-700">Adicionar produto</a>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <a href="{{ route('admin.site.products', $site) }}" wire:navigate class="rounded-xl border border-stone-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-amber-300 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-center justify-between"><span class="text-sm text-stone-500 dark:text-zinc-400">Produtos ativos</span><span class="text-lg text-amber-600">+</span></div>
            <p class="mt-4 text-3xl font-semibold text-stone-950 dark:text-white">{{ $activeProducts }}</p>
            <p class="mt-1 text-xs text-stone-500">de {{ $totalProducts }} no catálogo</p>
        </a>
        <a href="{{ route('admin.site.users', $site) }}" wire:navigate class="rounded-xl border border-stone-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-300 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-center justify-between"><span class="text-sm text-stone-500 dark:text-zinc-400">Utilizadores</span><span class="text-lg text-emerald-600">◉</span></div>
            <p class="mt-4 text-3xl font-semibold text-stone-950 dark:text-white">{{ $totalUsers }}</p>
            <p class="mt-1 text-xs text-stone-500">contas registadas</p>
        </a>
        <div class="rounded-xl border border-stone-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-center justify-between"><span class="text-sm text-stone-500 dark:text-zinc-400">Vendas hoje</span><span class="text-lg text-sky-600">↗</span></div>
            <p class="mt-4 text-3xl font-semibold text-stone-950 dark:text-white">€ {{ number_format($revenue, 2, ',', '.') }}</p>
            <p class="mt-1 text-xs text-stone-500">{{ $sales }} venda(s) hoje</p>
        </div>
        <div class="rounded-xl border border-stone-200 bg-stone-950 p-5 text-white shadow-sm">
            <div class="flex items-center justify-between"><span class="text-sm text-stone-300">Estado da loja</span><span class="size-2 rounded-full bg-emerald-400"></span></div>
            <p class="mt-4 text-3xl font-semibold">Online</p>
            <p class="mt-1 text-xs text-stone-400">catálogo disponível</p>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[1.4fr_1fr]">
        <section class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-start justify-between gap-4"><div><h2 class="text-lg font-semibold text-stone-950 dark:text-white">Vendas</h2><p class="mt-1 text-sm text-stone-500">O histórico aparecerá aqui assim que a primeira venda for registada.</p></div><span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-medium text-stone-600 dark:bg-zinc-700 dark:text-zinc-300">Sem dados</span></div>
            <div class="mt-8 flex min-h-40 flex-col items-center justify-center border-t border-dashed border-stone-200 text-center dark:border-zinc-700"><span class="text-3xl text-stone-300">⌁</span><p class="mt-3 text-sm font-medium text-stone-600 dark:text-zinc-300">Ainda não há vendas para analisar</p><p class="mt-1 text-xs text-stone-400">Quando existirem pedidos, o desempenho aparecerá neste espaço.</p></div>
        </section>
        <section class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-start justify-between"><div><h2 class="text-lg font-semibold text-stone-950 dark:text-white">Equipa do site</h2><p class="mt-1 text-sm text-stone-500">Os últimos membros associados.</p></div><a href="{{ route('admin.site.users', $site) }}" wire:navigate class="text-sm font-semibold text-amber-700 hover:text-amber-800">Ver todos</a></div>
            <div class="mt-5 divide-y divide-stone-100 dark:divide-zinc-700">
                @forelse ($recentUsers as $user)
                    <div class="flex items-center gap-3 py-3"><div class="flex size-9 shrink-0 items-center justify-center rounded-full bg-amber-100 text-xs font-semibold text-amber-800">{{ $user->initials() }}</div><div class="min-w-0"><p class="truncate text-sm font-medium text-stone-800 dark:text-zinc-200">{{ $user->name }}</p><p class="truncate text-xs text-stone-500">{{ $user->email }}</p></div></div>
                @empty
                    <p class="py-8 text-center text-sm text-stone-500">Nenhum utilizador registado.</p>
                @endforelse
            </div>
        </section>
    </div>
    <section class="rounded-xl border border-red-200 bg-red-50 p-6 dark:border-red-900 dark:bg-red-950/30">
        <div class="flex items-center justify-between"><div><h2 class="text-lg font-semibold text-red-950 dark:text-red-200">Stock baixo</h2><p class="mt-1 text-sm text-red-800/70 dark:text-red-300/70">Produtos que precisam de reposição.</p></div><span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-800">{{ $lowStockProducts->count() }}</span></div>
        <div class="mt-4 grid gap-2 sm:grid-cols-2 lg:grid-cols-5">@forelse ($lowStockProducts as $product)<div class="rounded-lg border border-red-200 bg-white px-3 py-3 dark:border-red-900 dark:bg-zinc-900"><p class="truncate text-sm font-medium text-stone-800 dark:text-zinc-200">{{ $product->name }}</p><p class="mt-1 text-xs text-red-700">{{ $product->stock }} em stock / mínimo {{ $product->minimum_stock }}</p></div>@empty<p class="text-sm text-red-800/70">Nenhum produto com stock baixo.</p>@endforelse</div>
    </section>
</div>
