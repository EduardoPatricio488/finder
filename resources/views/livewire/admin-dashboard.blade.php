<div class="mx-auto max-w-7xl space-y-8 px-6 py-10 lg:px-12">

    @if($site->plan && $site->plan->name === 'Free')
        <div class="relative overflow-hidden rounded-[2rem] bg-gradient-to-r from-amber-500 to-orange-600 p-0.5 shadow-lg shadow-amber-500/20 animate-in fade-in slide-in-from-top-4">
            <div class="relative flex flex-col items-center justify-between gap-4 rounded-[1.9rem] bg-white px-8 py-5 dark:bg-zinc-900 sm:flex-row">
                <div class="flex items-center gap-5">
                    <div class="flex size-12 items-center justify-center rounded-2xl bg-amber-100 text-amber-600 shadow-inner">
                        <flux:icon name="sparkles" variant="solid" class="size-7" />
                    </div>
                    <div>
                        <h4 class="text-sm font-black uppercase italic tracking-tighter text-stone-900 dark:text-white">Finder Free: Desbloqueia o teu potencial</h4>
                        <p class="text-xs font-medium text-stone-500">O teu limite é de 5 produtos. Atualiza para o Plano Pro e ganha IA e Relatórios.</p>
                    </div>
                </div>
                <flux:button href="{{ route('admin.site.upgrade', $site) }}" variant="primary" class="!rounded-xl !bg-stone-950 !px-6 shadow-lg shadow-stone-950/20">
                    Fazer Upgrade Agora
                </flux:button>
            </div>
        </div>
    @endif

    <header class="flex flex-col justify-between gap-6 md:flex-row md:items-end">
        <div>
            <div class="flex flex-wrap items-center gap-3">
                <span class="text-[10px] font-black uppercase tracking-[0.3em] text-amber-700">Painel de Gestão</span>
                <span class="h-px w-8 bg-amber-200"></span>
                <span class="text-[10px] font-black uppercase tracking-widest text-stone-400">Plano {{ $site->plan->name ?? 'N/A' }}</span>
            </div>
            <div class="mt-3 flex flex-wrap items-center gap-3">
                <h1 class="text-5xl font-black italic tracking-tighter text-stone-950 dark:text-white">{{ $site->name }}</h1>
                <span class="inline-flex items-center gap-2 rounded-full border border-indigo-200 bg-indigo-50 px-3 py-1.5 text-xs font-black text-indigo-700 dark:border-indigo-900 dark:bg-indigo-950/50 dark:text-indigo-300">
                    <flux:icon :name="$modelProfile['icon'] ?? 'globe-alt'" class="size-3.5" />
                    {{ $modelProfile['label'] ?? ($site->category_label ?? 'Website') }}
                </span>
            </div>
            <p class="mt-3 max-w-2xl text-sm font-medium leading-relaxed text-stone-500">
                {{ $modelProfile['description'] ?? 'Configura o teu website e preenche os conteúdos específicos do modelo escolhido.' }}
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <flux:button href="{{ route('site.public', ['site' => $site, 'preview' => 1]) }}" target="_blank" variant="ghost" icon="eye" class="!rounded-xl">Pré-visualizar</flux:button>
            <flux:button href="{{ route('builder.edit', $site) }}" variant="primary" icon="pencil-square" class="!rounded-xl !bg-stone-950">Abrir editor</flux:button>
        </div>
    </header>

    {{-- Conteúdo específico do modelo --}}
    <section id="model-content" class="scroll-mt-8 rounded-[2rem] border border-indigo-200 bg-white p-7 shadow-sm dark:border-indigo-900/60 dark:bg-zinc-900">
        <div class="flex flex-col justify-between gap-5 lg:flex-row lg:items-end">
            <div>
                <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.25em] text-indigo-600 dark:text-indigo-400">
                    <span>Configuração do modelo</span>
                    <span class="rounded-full bg-indigo-100 px-2 py-1 text-[9px] dark:bg-indigo-950/60">{{ $modelFilled }}/{{ $modelTotal }} preenchidos</span>
                </div>
                <h2 class="mt-2 text-2xl font-black tracking-tight text-stone-950 dark:text-white">{{ $modelProfile['label'] ?? 'Conteúdo do website' }}</h2>
                <p class="mt-1 max-w-3xl text-sm leading-6 text-stone-500">Estes campos são específicos deste tipo de website. Ao mudares de website, esta área e a sidebar são automaticamente adaptadas ao modelo desse site.</p>
            </div>
            @if(session('model-content-saved'))
                <span class="rounded-xl bg-emerald-50 px-4 py-2 text-xs font-bold text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300">{{ session('model-content-saved') }}</span>
            @endif
        </div>

        @if($modelTotal > 0)
            <form wire:submit="saveModelContent" class="mt-7">
                <div class="grid gap-5 md:grid-cols-2">
                    @foreach($modelFields as $field)
                        @php
                            $fieldKey = $field['key'];
                            $fieldType = $field['type'] ?? 'text';
                            $fieldId = 'model-'.$fieldKey;
                        @endphp
                        <div id="{{ $fieldId }}" class="{{ in_array($fieldType, ['textarea'], true) ? 'md:col-span-2' : '' }} scroll-mt-8">
                            <label for="{{ $fieldId }}-input" class="mb-2 block text-sm font-bold text-stone-900 dark:text-white">
                                {{ $field['label'] }}
                                @if($field['required'] ?? false)<span class="text-red-500">*</span>@endif
                            </label>

                            @if($fieldType === 'textarea')
                                <textarea id="{{ $fieldId }}-input" wire:model="modelContent.{{ $fieldKey }}" rows="5" placeholder="{{ $field['placeholder'] ?? '' }}" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:focus:border-indigo-500 dark:focus:ring-indigo-950"></textarea>
                            @else
                                <input id="{{ $fieldId }}-input" type="{{ $fieldType === 'email' ? 'email' : ($fieldType === 'url' ? 'url' : 'text') }}" wire:model="modelContent.{{ $fieldKey }}" placeholder="{{ $field['placeholder'] ?? '' }}" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:focus:border-indigo-500 dark:focus:ring-indigo-950" />
                            @endif

                            @error("modelContent.{$fieldKey}")
                                <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endforeach
                </div>

                <div class="mt-6 flex flex-wrap items-center justify-between gap-3 border-t border-stone-100 pt-5 dark:border-zinc-800">
                    <p class="text-xs text-stone-400">Os dados ficam guardados neste website e são isolados dos restantes sites.</p>
                    <flux:button type="submit" variant="primary" icon="check" class="!rounded-xl !bg-indigo-600">Guardar conteúdo</flux:button>
                </div>
            </form>
        @else
            <div class="mt-6 rounded-2xl bg-stone-50 p-6 text-sm text-stone-500 dark:bg-zinc-800/60">
                Este modelo ainda não tem campos configurados.
            </div>
        @endif
    </section>

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <a href="{{ route('admin.site.products', $site) }}" class="group relative block overflow-hidden rounded-[2rem] border border-stone-200 bg-white p-7 shadow-sm transition-all duration-200 hover:-translate-y-1 hover:border-amber-300 hover:shadow-lg dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between"><span class="text-[10px] font-black uppercase tracking-widest text-stone-400">Produtos Ativos</span><flux:icon name="archive-box" class="size-4 text-amber-500" /></div>
            <div class="mt-4 flex items-baseline gap-2"><span class="text-4xl font-black tracking-tighter text-stone-950 dark:text-white">{{ $activeProducts }}</span><span class="text-xs font-bold text-stone-400">/ {{ $site->plan->product_limit ?? '∞' }}</span></div>
            <div class="mt-4 h-1 w-full rounded-full bg-stone-100 dark:bg-zinc-800"><div class="h-full rounded-full bg-amber-500 transition-all duration-1000" style="width: {{ min(100, (($activeProducts / max(1, ($site->plan->product_limit ?? 100))) * 100)) }}%"></div></div>
            <p class="mt-4 text-xs font-bold text-amber-700 transition group-hover:translate-x-1 dark:text-amber-400">Abrir produtos →</p>
        </a>

        <a href="{{ route('admin.site.users', $site) }}" class="group relative block overflow-hidden rounded-[2rem] border border-stone-200 bg-white p-7 shadow-sm transition-all duration-200 hover:-translate-y-1 hover:border-emerald-300 hover:shadow-lg dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between"><span class="text-[10px] font-black uppercase tracking-widest text-stone-400">Utilizadores</span><flux:icon name="users" class="size-4 text-emerald-500" /></div>
            <p class="mt-4 text-4xl font-black tracking-tighter text-stone-950 dark:text-white">{{ $totalUsers }}</p>
            <p class="mt-2 text-[10px] font-bold text-stone-400 uppercase tracking-widest">Contas registadas</p>
            <p class="mt-4 text-xs font-bold text-emerald-700 transition group-hover:translate-x-1 dark:text-emerald-400">Gerir utilizadores →</p>
        </a>

        <a href="{{ route('admin.site.sales', $site) }}" class="group relative block overflow-hidden rounded-[2rem] border border-stone-200 bg-white p-7 shadow-sm transition-all duration-200 hover:-translate-y-1 hover:border-sky-300 hover:shadow-lg dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between"><span class="text-[10px] font-black uppercase tracking-widest text-stone-400">Receita Hoje</span><flux:icon name="currency-euro" class="size-4 text-sky-500" /></div>
            <p class="mt-4 text-4xl font-black tracking-tighter text-stone-950 dark:text-white">{{ number_format($revenue, 2, ',', '.') }} €</p>
            <p class="mt-2 text-[10px] font-bold text-stone-400 uppercase tracking-widest">{{ $sales }} venda(s) hoje</p>
            <p class="mt-4 text-xs font-bold text-sky-700 transition group-hover:translate-x-1 dark:text-sky-400">Abrir vendas →</p>
        </a>

        <a href="{{ route('admin.site.stock', $site) }}" class="group relative block overflow-hidden rounded-[2rem] border border-stone-200 bg-white p-7 shadow-sm transition-all duration-200 hover:-translate-y-1 hover:border-red-300 hover:shadow-lg dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between"><span class="text-[10px] font-black uppercase tracking-widest text-stone-400">Stock baixo</span><flux:icon name="exclamation-triangle" class="size-4 text-red-500" /></div>
            <p class="mt-4 text-4xl font-black tracking-tighter text-stone-950 dark:text-white">{{ $lowStockProducts->count() }}</p>
            <p class="mt-2 text-[10px] font-bold text-stone-400 uppercase tracking-widest">Artigos a verificar</p>
            <p class="mt-4 text-xs font-bold text-red-700 transition group-hover:translate-x-1 dark:text-red-400">Ver stock →</p>
        </a>
    </div>

    <div class="grid gap-8 lg:grid-cols-2">
        <section class="rounded-[2rem] border border-stone-200 bg-white p-7 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between"><div><h2 class="text-lg font-black text-stone-950 dark:text-white">Utilizadores recentes</h2><p class="mt-1 text-sm text-stone-500">Últimos membros associados ao website.</p></div><flux:button href="{{ route('admin.site.users', $site) }}" variant="ghost" size="sm">Ver todos</flux:button></div>
            <div class="mt-6 space-y-3">
                @forelse($recentUsers as $user)
                    <div class="flex items-center justify-between rounded-2xl bg-stone-50 px-4 py-3 dark:bg-zinc-800/60"><div><p class="text-sm font-semibold text-stone-900 dark:text-white">{{ $user->name }}</p><p class="text-xs text-stone-500">{{ $user->email }}</p></div></div>
                @empty
                    <p class="rounded-2xl bg-stone-50 p-5 text-sm text-stone-500 dark:bg-zinc-800/60">Ainda não existem utilizadores adicionais.</p>
                @endforelse
            </div>
        </section>

        <section class="rounded-[2rem] border border-stone-200 bg-white p-7 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between"><div><h2 class="text-lg font-black text-stone-950 dark:text-white">Stock a verificar</h2><p class="mt-1 text-sm text-stone-500">Produtos que atingiram o stock mínimo.</p></div><flux:button href="{{ route('admin.site.products', $site) }}" variant="ghost" size="sm">Ver produtos</flux:button></div>
            <div class="mt-6 space-y-3">
                @forelse($lowStockProducts as $product)
                    <div class="flex items-center justify-between rounded-2xl bg-stone-50 px-4 py-3 dark:bg-zinc-800/60"><div><p class="text-sm font-semibold text-stone-900 dark:text-white">{{ $product->name }}</p><p class="text-xs text-stone-500">Mínimo: {{ $product->minimum_stock }}</p></div><span class="rounded-full bg-red-100 px-3 py-1 text-xs font-bold text-red-700 dark:bg-red-950/40 dark:text-red-300">{{ $product->stock }}</span></div>
                @empty
                    <p class="rounded-2xl bg-stone-50 p-5 text-sm text-stone-500 dark:bg-zinc-800/60">O stock está controlado.</p>
                @endforelse
            </div>
        </section>
    </div>
</div>
