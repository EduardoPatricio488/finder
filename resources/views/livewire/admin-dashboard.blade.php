<div class="mx-auto max-w-7xl space-y-8 px-6 py-10 lg:px-12">
    @if($site->plan && $site->plan->name === 'Free')
        <div class="relative overflow-hidden rounded-[2rem] bg-gradient-to-r from-amber-500 to-orange-600 p-0.5 shadow-lg shadow-amber-500/20">
            <div class="relative flex flex-col items-center justify-between gap-4 rounded-[1.9rem] bg-white px-8 py-5 dark:bg-zinc-900 sm:flex-row">
                <div><h4 class="text-sm font-black uppercase italic tracking-tighter text-stone-900 dark:text-white">Finder Free: Desbloqueia o teu potencial</h4><p class="text-xs font-medium text-stone-500">O teu limite é de 5 produtos. Atualiza para o Plano Pro.</p></div>
                @if(Route::has('admin.site.upgrade')) <flux:button href="{{ route('admin.site.upgrade', $site) }}" variant="primary">Fazer Upgrade</flux:button> @endif
            </div>
        </div>
    @endif

    <header class="flex flex-col justify-between gap-6 md:flex-row md:items-end">
        <div>
            <div class="flex flex-wrap items-center gap-3"><span class="text-[10px] font-black uppercase tracking-[0.3em] text-amber-700">Painel de Gestão</span><span class="text-[10px] font-black uppercase tracking-widest text-stone-400">Plano {{ $site->plan->name ?? 'N/A' }}</span></div>
            <div class="mt-3 flex flex-wrap items-center gap-3"><h1 class="text-5xl font-black italic tracking-tighter text-stone-950 dark:text-white">{{ $site->name }}</h1><span class="rounded-full border border-indigo-200 bg-indigo-50 px-3 py-1.5 text-xs font-black text-indigo-700 dark:border-indigo-900 dark:bg-indigo-950/50 dark:text-indigo-300">{{ $modelProfile['label'] ?? ($site->category_label ?? 'Website') }}</span></div>
            <p class="mt-3 max-w-2xl text-sm font-medium leading-relaxed text-stone-500">{{ $modelProfile['description'] ?? 'Configura o teu website e preenche os conteúdos específicos do modelo escolhido.' }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <flux:button href="{{ route('site.public', ['site' => $site, 'preview' => 1]) }}" target="_blank" variant="ghost" icon="eye">Pré-visualizar</flux:button>
            @if(Route::has('builder.edit')) <flux:button href="{{ route('builder.edit', $site) }}" variant="primary" icon="pencil-square">Abrir editor</flux:button> @endif
        </div>
    </header>

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <a href="{{ route('admin.site.products', $site) }}" class="group block rounded-[2rem] border border-stone-200 bg-white p-7 shadow-sm hover:border-amber-300 dark:border-zinc-800 dark:bg-zinc-900"><span class="text-[10px] font-black uppercase tracking-widest text-stone-400">Produtos Ativos</span><div class="mt-4 text-4xl font-black">{{ $activeProducts }}</div><p class="mt-4 text-xs font-bold text-amber-700">Abrir produtos →</p></a>
        <a href="{{ route('admin.site.users', $site) }}" class="group block rounded-[2rem] border border-stone-200 bg-white p-7 shadow-sm hover:border-emerald-300 dark:border-zinc-800 dark:bg-zinc-900"><span class="text-[10px] font-black uppercase tracking-widest text-stone-400">Utilizadores</span><div class="mt-4 text-4xl font-black">{{ $totalUsers }}</div><p class="mt-4 text-xs font-bold text-emerald-700">Gerir utilizadores →</p></a>
        <a href="{{ route('admin.site.sales', $site) }}" class="group block rounded-[2rem] border border-stone-200 bg-white p-7 shadow-sm hover:border-sky-300 dark:border-zinc-800 dark:bg-zinc-900"><span class="text-[10px] font-black uppercase tracking-widest text-stone-400">Receita Hoje</span><div class="mt-4 text-4xl font-black">{{ number_format($revenue, 2, ',', '.') }} €</div><p class="mt-2 text-xs text-stone-400">{{ $sales }} venda(s) hoje</p><p class="mt-4 text-xs font-bold text-sky-700">Abrir vendas →</p></a>
        <a href="{{ route('admin.site.stock', $site) }}" class="group block rounded-[2rem] border border-stone-200 bg-white p-7 shadow-sm hover:border-red-300 dark:border-zinc-800 dark:bg-zinc-900"><span class="text-[10px] font-black uppercase tracking-widest text-stone-400">Stock baixo</span><div class="mt-4 text-4xl font-black">{{ $lowStockProducts->count() }}</div><p class="mt-2 text-xs text-stone-400">Artigos a verificar</p><p class="mt-4 text-xs font-bold text-red-700">Ver stock →</p></a>
    </div>

    <div class="grid gap-8 lg:grid-cols-2">
        <section class="rounded-[2rem] border border-stone-200 bg-white p-7 shadow-sm dark:border-zinc-800 dark:bg-zinc-900"><div class="flex items-center justify-between"><div><h2 class="text-lg font-black">Utilizadores recentes</h2><p class="mt-1 text-sm text-stone-500">Últimos membros associados ao website.</p></div><flux:button href="{{ route('admin.site.users', $site) }}" variant="ghost" size="sm">Ver todos</flux:button></div><div class="mt-6 space-y-3">@forelse($recentUsers as $user)<div class="rounded-2xl bg-stone-50 px-4 py-3 dark:bg-zinc-800/60"><p class="text-sm font-semibold">{{ $user->name }}</p><p class="text-xs text-stone-500">{{ $user->email }}</p></div>@empty<p class="rounded-2xl bg-stone-50 p-5 text-sm text-stone-500">Ainda não existem utilizadores adicionais.</p>@endforelse</div></section>
        <section class="rounded-[2rem] border border-stone-200 bg-white p-7 shadow-sm dark:border-zinc-800 dark:bg-zinc-900"><div class="flex items-center justify-between"><div><h2 class="text-lg font-black">Stock a verificar</h2><p class="mt-1 text-sm text-stone-500">Produtos que atingiram o stock mínimo.</p></div><flux:button href="{{ route('admin.site.products', $site) }}" variant="ghost" size="sm">Ver produtos</flux:button></div><div class="mt-6 space-y-3">@forelse($lowStockProducts as $product)<div class="flex items-center justify-between rounded-2xl bg-stone-50 px-4 py-3 dark:bg-zinc-800/60"><div><p class="text-sm font-semibold">{{ $product->name }}</p><p class="text-xs text-stone-500">Mínimo: {{ $product->minimum_stock }}</p></div><span class="rounded-full bg-red-100 px-3 py-1 text-xs font-bold text-red-700">{{ $product->stock }}</span></div>@empty<p class="rounded-2xl bg-stone-50 p-5 text-sm text-stone-500">O stock está controlado.</p>@endforelse</div></section>
    </div>
</div>
