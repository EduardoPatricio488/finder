<div class="mx-auto max-w-7xl space-y-8 px-6 py-10 lg:px-12">

    {{-- 1. BANNER DE UPGRADE (SÓ APARECE SE FOR PLANO FREE) --}}
    @if($site->plan && $site->plan->name === 'Free')
        <div class="relative overflow-hidden rounded-[2rem] bg-gradient-to-r from-amber-500 to-orange-600 p-0.5 shadow-lg shadow-amber-500/20 animate-in fade-in slide-in-from-top-4">
            <div class="relative flex flex-col items-center justify-between gap-4 rounded-[1.9rem] bg-white dark:bg-zinc-900 px-8 py-5 sm:flex-row">
                <div class="flex items-center gap-5">
                    <div class="flex size-12 items-center justify-center rounded-2xl bg-amber-100 text-amber-600 shadow-inner">
                        <flux:icon name="sparkles" variant="solid" class="size-7" />
                    </div>
                    <div>
                        <h4 class="text-sm font-black uppercase italic tracking-tighter text-stone-900 dark:text-white">Finder Free: Desbloqueie o seu potencial</h4>
                        <p class="text-xs font-medium text-stone-500">O seu limite é de 5 produtos. Atualize para o **Plano Pro** e ganhe IA e Relatórios.</p>
                    </div>
                </div>
                <flux:button href="{{ route('admin.site.upgrade', $site) }}" variant="primary" class="!rounded-xl !bg-stone-950 !px-6 shadow-lg shadow-stone-950/20">
                    Fazer Upgrade Agora
                </flux:button>

                {{-- Decoração abstrata --}}
                <div class="absolute -right-10 -top-10 size-32 rounded-full bg-amber-500/5 blur-2xl"></div>
            </div>
        </div>
    @endif

    {{-- 2. CABEÇALHO PRINCIPAL --}}
    <header class="flex flex-col justify-between gap-6 md:flex-row md:items-end">
        <div>
            <div class="flex items-center gap-3">
                <span class="text-[10px] font-black uppercase tracking-[0.3em] text-amber-700">Painel de Gestão</span>
                <span class="h-px w-8 bg-amber-200"></span>
                <span class="text-[10px] font-black uppercase tracking-widest text-stone-400">Plano {{ $site->plan->name ?? 'N/A' }}</span>
            </div>
            <h1 class="mt-3 text-5xl font-black italic tracking-tighter text-stone-950 dark:text-white">
                {{ $site->name }}
            </h1>
            <p class="mt-3 max-w-xl text-sm font-medium leading-relaxed text-stone-500">
                Resumo operacional da sua loja online. Monitorize o catálogo, vendas e equipa em tempo real.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <flux:button href="{{ $site->destinationUrl() }}" target="_blank" variant="ghost" icon="eye" class="!rounded-xl">Ver Loja</flux:button>
            <flux:button href="{{ route('admin.site.products', $site) }}" variant="primary" icon="plus" class="!rounded-xl !bg-stone-950">Adicionar Artigo</flux:button>
        </div>
    </header>

    {{-- 3. CARTÕES DE MÉTRICAS (KPIs) --}}
    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        {{-- Produtos --}}
        <div class="group relative overflow-hidden rounded-[2rem] border border-stone-200 bg-white p-7 shadow-sm transition-all hover:border-amber-300 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-black uppercase tracking-widest text-stone-400">Produtos Ativos</span>
                <flux:icon name="archive-box" class="size-4 text-amber-500" />
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-4xl font-black tracking-tighter text-stone-950 dark:text-white">{{ $activeProducts }}</span>
                <span class="text-xs font-bold text-stone-400">/ {{ $site->plan->product_limit ?? '∞' }}</span>
            </div>
            {{-- Barra de progresso visual do limite do plano --}}
            <div class="mt-4 h-1 w-full rounded-full bg-stone-100 dark:bg-zinc-800">
                <div class="h-full rounded-full bg-amber-500 transition-all duration-1000" style="width: {{ ($activeProducts / ($site->plan->product_limit ?? 100)) * 100 }}%"></div>
            </div>
        </div>

        {{-- Utilizadores --}}
        <div class="rounded-[2rem] border border-stone-200 bg-white p-7 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-black uppercase tracking-widest text-stone-400">Utilizadores</span>
                <flux:icon name="users" class="size-4 text-emerald-500" />
            </div>
            <p class="mt-4 text-4xl font-black tracking-tighter text-stone-950 dark:text-white">{{ $totalUsers }}</p>
            <p class="mt-2 text-[10px] font-bold text-stone-400 uppercase tracking-widest">Contas registadas</p>
        </div>

        {{-- Receita --}}
        <div class="rounded-[2rem] border border-stone-200 bg-white p-7 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-black uppercase tracking-widest text-stone-400">Receita Hoje</span>
                <flux:icon name="currency-euro" class="size-4 text-sky-500" />
            </div>
            <p class="mt-4 text-3xl font-black tracking-tighter text-stone-950 dark:text-white">€{{ number_format($revenue, 2, ',', '.') }}</p>
            <p class="mt-2 text-[10px] font-bold text-stone-400 uppercase tracking-widest">{{ $sales }} vendas efetuadas</p>
        </div>

        {{-- Estado --}}
        <div @class([
            'rounded-[2rem] p-7 text-white shadow-xl flex flex-col justify-between',
            'bg-stone-950' => $site->status === 'online',
            'bg-red-950' => $site->status !== 'online',
        ])>
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-black uppercase tracking-widest text-white/50">Estado</span>
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-400"></span>
                </span>
            </div>
            <div>
                <p class="text-3xl font-black italic tracking-tighter">{{ $site->statusLabel() }}</p>
                <p class="mt-1 text-[10px] font-bold uppercase tracking-widest text-white/40">Visibilidade pública</p>
            </div>
        </div>
    </div>

    {{-- 4. ÁREA CENTRAL (VENDAS E EQUIPA) --}}
    <div class="grid gap-8 lg:grid-cols-3">
        {{-- Gráfico / Vendas --}}
        <section class="rounded-[2.5rem] border border-stone-200 bg-white p-8 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 lg:col-span-2">
            <div class="flex items-center justify-between border-b border-stone-50 pb-6 dark:border-zinc-800">
                <div>
                    <h2 class="text-lg font-black uppercase italic tracking-tighter dark:text-white">Histórico de Performance</h2>
                    <p class="text-xs font-medium text-stone-400">Dados consolidados das últimas 24h.</p>
                </div>
                @can('access-reports', $site)
                    <flux:button href="{{ route('admin.site.reports', $site) }}" variant="ghost" size="sm" icon="chart-bar">Ver Detalhes</flux:button>
                @else
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1 text-[9px] font-black uppercase text-amber-700">
                        <flux:icon name="lock-closed" size="xs" /> Premium
                    </span>
                @endcan
            </div>

            {{-- Placeholder para quando não há dados --}}
            <div class="mt-12 flex min-h-[240px] flex-col items-center justify-center rounded-3xl border-2 border-dashed border-stone-100 bg-stone-50/30 text-center dark:border-zinc-800">
                <div class="flex size-14 items-center justify-center rounded-full bg-white text-stone-200 shadow-sm">
                    <flux:icon name="presentation-chart-line" variant="outline" class="size-7" />
                </div>
                <p class="mt-5 text-sm font-bold text-stone-900 dark:text-white">A aguardar transações...</p>
                <p class="mt-1 max-w-[200px] text-[10px] font-medium leading-relaxed text-stone-400">Assim que a sua primeira venda for registada, os gráficos de desempenho aparecerão aqui.</p>
            </div>
        </section>

        {{-- Equipa --}}
        <section class="rounded-[2.5rem] border border-stone-200 bg-white p-8 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="mb-8 flex items-center justify-between">
                <h2 class="text-lg font-black uppercase italic tracking-tighter dark:text-white">Equipa do Site</h2>
                <flux:button variant="ghost" size="sm" icon="users" href="{{ route('admin.site.users', $site) }}" />
            </div>

            <div class="space-y-4">
                @forelse ($recentUsers as $user)
                    <div class="group flex items-center gap-4 rounded-2xl p-2 transition-colors hover:bg-stone-50 dark:hover:bg-zinc-800">
                        <div class="flex size-11 shrink-0 items-center justify-center rounded-2xl bg-amber-100 text-xs font-black text-amber-800 shadow-sm">
                            {{ substr($user->name, 0, 2) }}
                        </div>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-black dark:text-white">{{ $user->name }}</p>
                            <p class="truncate text-[10px] font-bold uppercase tracking-widest text-stone-400">{{ $user->email }}</p>
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center text-xs font-bold uppercase tracking-widest text-stone-300">Nenhum membro</div>
                @endforelse
            </div>

            <flux:button href="{{ route('admin.site.users', $site) }}" class="mt-8 w-full !rounded-xl">Gerir Acessos</flux:button>
        </section>
    </div>

    {{-- 5. STOCK BAIXO (ALERTA CRÍTICO) --}}
    @if($lowStockProducts->count() > 0)
        <section class="rounded-[2.5rem] bg-red-50 p-8 shadow-xl shadow-red-500/5 dark:bg-red-950/20 border border-red-100 dark:border-red-900/50">
            <div class="flex items-center justify-between border-b border-red-100 pb-6 dark:border-red-900/30">
                <div class="flex items-center gap-4">
                    <div class="flex size-10 items-center justify-center rounded-xl bg-red-500 text-white shadow-lg shadow-red-500/20">
                        <flux:icon name="exclamation-triangle" variant="solid" class="size-6" />
                    </div>
                    <div>
                        <h2 class="text-lg font-black uppercase italic tracking-tighter text-red-950 dark:text-red-200">Risco de Ruptura de Stock</h2>
                        <p class="text-xs font-medium text-red-700/70 dark:text-red-300/70">Estes artigos precisam de reposição imediata para evitar perda de vendas.</p>
                    </div>
                </div>
                <span class="rounded-full bg-red-500 px-4 py-1 text-xs font-black text-white shadow-md">
                    {{ $lowStockProducts->count() }} Alertas
                </span>
            </div>

            <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($lowStockProducts as $product)
                    <div class="relative overflow-hidden rounded-2xl bg-white p-5 shadow-sm dark:bg-zinc-900 border border-transparent hover:border-red-200 transition-all">
                        <p class="truncate text-sm font-black text-stone-900 dark:text-white">{{ $product->name }}</p>
                        <div class="mt-3 flex items-center justify-between">
                            <span class="text-[10px] font-bold text-red-600 uppercase tracking-widest">Stock: {{ $product->stock }}</span>
                            <span class="text-[10px] font-bold text-stone-400 uppercase tracking-widest">Mínimo: {{ $product->minimum_stock }}</span>
                        </div>
                        {{-- Barra de perigo --}}
                        <div class="mt-3 h-1.5 w-full rounded-full bg-red-100 dark:bg-zinc-800">
                            <div class="h-full rounded-full bg-red-500" style="width: {{ ($product->stock / $product->minimum_stock) * 100 }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
</div>
