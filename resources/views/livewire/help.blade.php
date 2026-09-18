<div class="min-h-full bg-zinc-50/70 dark:bg-zinc-950">
    <div class="mx-auto max-w-7xl space-y-8 p-6 lg:p-10">
        <div class="relative overflow-hidden rounded-3xl border border-zinc-200 bg-white px-6 py-8 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 lg:px-10 lg:py-10">
            <div class="absolute -right-20 -top-24 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl"></div>
            <div class="absolute -bottom-24 left-1/3 h-48 w-48 rounded-full bg-violet-500/10 blur-3xl"></div>
            <div class="relative">
                <div class="inline-flex items-center gap-2 rounded-full bg-indigo-50 px-3 py-1.5 text-xs font-bold uppercase tracking-wider text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300">
                    <flux:icon name="lifebuoy" class="size-4" />
                    Centro de ajuda
                </div>
                <h1 class="mt-5 text-4xl font-black tracking-tight text-zinc-950 dark:text-white lg:text-5xl">Como podemos ajudar?</h1>
                <p class="mt-3 max-w-2xl text-base leading-7 text-zinc-600 dark:text-zinc-300">
                    Tudo o que precisas para criar, configurar, gerir e publicar os teus websites no Finder.
                </p>
                <div class="mt-7 flex flex-wrap gap-3">
                    @if($hasDashboard)
                        <a href="{{ route('dashboard') }}" wire:navigate class="inline-flex items-center gap-2 rounded-xl bg-zinc-950 px-4 py-2.5 text-sm font-bold text-white transition hover:-translate-y-0.5 hover:bg-zinc-800 dark:bg-white dark:text-zinc-950 dark:hover:bg-zinc-200">
                            <flux:icon name="squares-2x2" class="size-4" />
                            Meus Websites
                        </a>
                    @endif
                    @if($hasBuilder && $currentSite)
                        <a href="{{ route('builder.edit', $currentSite) }}" wire:navigate class="inline-flex items-center gap-2 rounded-xl border border-zinc-200 bg-white px-4 py-2.5 text-sm font-bold text-zinc-800 transition hover:border-indigo-300 hover:text-indigo-700 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100 dark:hover:border-indigo-500 dark:hover:text-indigo-300">
                            <flux:icon name="pencil-square" class="size-4" />
                            Abrir Builder
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div>
            <div class="mb-4">
                <h2 class="text-xl font-black text-zinc-950 dark:text-white">Começa por aqui</h2>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">As áreas mais importantes para trabalhares no Finder.</p>
            </div>
            <div class="grid gap-4 md:grid-cols-3">
                @if($hasDashboard)
                    <a href="{{ route('dashboard') }}" wire:navigate class="group rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:border-indigo-300 hover:shadow-lg dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-indigo-500">
                        <div class="flex items-center justify-between">
                            <div class="flex size-11 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300"><flux:icon name="squares-2x2" class="size-5" /></div>
                            <flux:icon name="arrow-up-right" class="size-5 text-zinc-300 transition group-hover:text-indigo-500" />
                        </div>
                        <h3 class="mt-5 font-bold text-zinc-950 dark:text-white">Meus Websites</h3>
                        <p class="mt-2 text-sm leading-6 text-zinc-500 dark:text-zinc-400">Escolhe o website que queres gerir e acede rapidamente às áreas principais.</p>
                    </a>
                @endif
                @if($hasSiteCreate)
                    <a href="{{ route('site.create') }}" wire:navigate class="group rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:border-indigo-300 hover:shadow-lg dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-indigo-500">
                        <div class="flex items-center justify-between">
                            <div class="flex size-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300"><flux:icon name="plus" class="size-5" /></div>
                            <flux:icon name="arrow-up-right" class="size-5 text-zinc-300 transition group-hover:text-emerald-500" />
                        </div>
                        <h3 class="mt-5 font-bold text-zinc-950 dark:text-white">Criar website</h3>
                        <p class="mt-2 text-sm leading-6 text-zinc-500 dark:text-zinc-400">Cria um novo projeto e define a base para começares a construir.</p>
                    </a>
                @endif
                @if($hasBuilder && $currentSite)
                    <a href="{{ route('builder.edit', $currentSite) }}" wire:navigate class="group rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:border-indigo-300 hover:shadow-lg dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-indigo-500">
                        <div class="flex items-center justify-between">
                            <div class="flex size-11 items-center justify-center rounded-xl bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-300"><flux:icon name="pencil-square" class="size-5" /></div>
                            <flux:icon name="arrow-up-right" class="size-5 text-zinc-300 transition group-hover:text-violet-500" />
                        </div>
                        <h3 class="mt-5 font-bold text-zinc-950 dark:text-white">Website Builder</h3>
                        <p class="mt-2 text-sm leading-6 text-zinc-500 dark:text-zinc-400">Edita páginas, conteúdo e estrutura do website atualmente selecionado.</p>
                    </a>
                @endif
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-[1.25fr_.75fr]">
            <section class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 lg:p-8">
                <div class="flex items-start gap-4">
                    <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200"><flux:icon name="map" class="size-5" /></div>
                    <div>
                        <h2 class="text-xl font-black text-zinc-950 dark:text-white">Como funciona o Finder?</h2>
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Um fluxo simples para manter cada website organizado.</p>
                    </div>
                </div>
                <div class="mt-7 space-y-5">
                    <div class="flex gap-4">
                        <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-xs font-black text-white">1</span>
                        <div><h3 class="font-bold text-zinc-900 dark:text-white">Seleciona o website</h3><p class="mt-1 text-sm leading-6 text-zinc-500 dark:text-zinc-400">Escolhe o website no seletor de workspace. As áreas de gestão passam a trabalhar sobre esse contexto.</p></div>
                    </div>
                    <div class="flex gap-4">
                        <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-xs font-black text-white">2</span>
                        <div><h3 class="font-bold text-zinc-900 dark:text-white">Constrói e configura</h3><p class="mt-1 text-sm leading-6 text-zinc-500 dark:text-zinc-400">Usa o Builder, a configuração, os menus e a biblioteca de media para preparar o website.</p></div>
                    </div>
                    <div class="flex gap-4">
                        <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-xs font-black text-white">3</span>
                        <div><h3 class="font-bold text-zinc-900 dark:text-white">Gere a operação</h3><p class="mt-1 text-sm leading-6 text-zinc-500 dark:text-zinc-400">Se o website tiver loja, podes trabalhar com produtos, vendas, encomendas e stock.</p></div>
                    </div>
                    <div class="flex gap-4">
                        <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-xs font-black text-white">4</span>
                        <div><h3 class="font-bold text-zinc-900 dark:text-white">Verifica e publica</h3><p class="mt-1 text-sm leading-6 text-zinc-500 dark:text-zinc-400">Abre a versão pública para confirmares o resultado final e validares as alterações.</p></div>
                    </div>
                </div>
            </section>

            <section class="rounded-3xl border border-zinc-200 bg-zinc-950 p-6 text-white shadow-sm dark:border-zinc-700 lg:p-8">
                <div class="flex size-11 items-center justify-center rounded-xl bg-white/10 text-white"><flux:icon name="light-bulb" class="size-5" /></div>
                <h2 class="mt-5 text-xl font-black">Dica rápida</h2>
                <p class="mt-3 text-sm leading-6 text-zinc-300">Se estiveres a gerir vários websites, confirma sempre o website selecionado antes de alterar produtos, media, menus ou definições.</p>
                <div class="mt-6 rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="text-xs font-bold uppercase tracking-wider text-zinc-400">Contexto atual</p>
                    <p class="mt-1 font-semibold">{{ $currentSite?->name ?? 'Nenhum website selecionado' }}</p>
                </div>
            </section>
        </div>

        <section class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 lg:p-8">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-black text-zinc-950 dark:text-white">Áreas do Finder</h2>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Uma visão rápida do que encontras na plataforma.</p>
                </div>
            </div>
            <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @foreach([
                    ['title' => 'Website', 'text' => 'Builder, páginas e apresentação pública.'],
                    ['title' => 'Configuração', 'text' => 'Identidade, definições e informação do website.'],
                    ['title' => 'Media', 'text' => 'Imagens e outros recursos usados no website.'],
                    ['title' => 'Loja', 'text' => 'Produtos, vendas, encomendas e stock quando disponível.'],
                ] as $area)
                    <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-950">
                        <p class="font-bold text-zinc-900 dark:text-white">{{ $area['title'] }}</p>
                        <p class="mt-1 text-sm leading-5 text-zinc-500 dark:text-zinc-400">{{ $area['text'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
</div>