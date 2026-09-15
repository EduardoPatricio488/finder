<div
    x-data="{ mobileOpen: false, scrolled: false, faqOpen: null }"
    x-on:scroll.window="scrolled = window.scrollY > 16"
    class="min-h-screen overflow-x-clip bg-white text-zinc-950 dark:bg-zinc-950 dark:text-white"
>
    {{-- ============================= HEADER ============================= --}}
    <header
        class="fixed inset-x-0 top-0 z-50 transition-all duration-300"
        :class="scrolled
            ? 'border-b border-zinc-200/80 bg-white/90 shadow-sm backdrop-blur-xl dark:border-zinc-800/80 dark:bg-zinc-950/90'
            : 'bg-transparent'"
    >
        <div class="mx-auto flex h-[68px] max-w-7xl items-center justify-between px-5 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5" aria-label="Finder — página inicial">
                <span class="finder-mark" aria-hidden="true"><span></span></span>
                <span class="text-[17px] font-bold tracking-[-0.04em]">Finder</span>
            </a>

            <nav class="hidden items-center gap-8 text-[13px] font-medium text-zinc-600 lg:flex dark:text-zinc-300" aria-label="Navegação principal">
                <a href="#funcionalidades" class="finder-nav-link">Funcionalidades</a>
                <a href="#como-funciona" class="finder-nav-link">Como funciona</a>
                <a href="#solucoes" class="finder-nav-link">Soluções</a>
                <a href="#precos" class="finder-nav-link">Preços</a>
                <a href="#faq" class="finder-nav-link">Perguntas frequentes</a>
            </nav>

            <div class="hidden items-center gap-2.5 sm:flex">
                @auth
                    <a href="{{ route('dashboard') }}" class="finder-button finder-button-ghost">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="finder-button finder-button-ghost">Entrar</a>
                    <a href="{{ route('register') }}" class="finder-button finder-button-primary">
                        Começar agora <span aria-hidden="true">→</span>
                    </a>
                @endauth
            </div>

            <button
                type="button"
                class="inline-flex size-10 items-center justify-center rounded-xl border border-zinc-200 bg-white/80 text-zinc-900 sm:hidden dark:border-zinc-800 dark:bg-zinc-900/80 dark:text-white"
                aria-label="Abrir menu"
                :aria-expanded="mobileOpen.toString()"
                x-on:click="mobileOpen = !mobileOpen"
            >
                <svg x-show="!mobileOpen" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/></svg>
                <svg x-cloak x-show="mobileOpen" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" d="m6 6 12 12M18 6 6 18"/></svg>
            </button>
        </div>

        <div
            x-cloak
            x-show="mobileOpen"
            x-transition.opacity.duration.200ms
            class="border-t border-zinc-200 bg-white px-5 py-5 shadow-xl sm:hidden dark:border-zinc-800 dark:bg-zinc-950"
        >
            <nav class="flex flex-col gap-1" aria-label="Navegação móvel">
                @foreach ([['#funcionalidades', 'Funcionalidades'], ['#como-funciona', 'Como funciona'], ['#solucoes', 'Soluções'], ['#precos', 'Preços'], ['#faq', 'Perguntas frequentes']] as $link)
                    <a href="{{ $link[0] }}" x-on:click="mobileOpen = false" class="rounded-xl px-4 py-3 text-sm font-medium hover:bg-zinc-100 dark:hover:bg-zinc-900">{{ $link[1] }}</a>
                @endforeach
                <div class="mt-3 grid grid-cols-2 gap-2 border-t border-zinc-100 pt-4 dark:border-zinc-800">
                    @auth
                        <a href="{{ route('dashboard') }}" class="finder-button finder-button-ghost col-span-2 justify-center">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="finder-button finder-button-ghost justify-center">Entrar</a>
                        <a href="{{ route('register') }}" class="finder-button finder-button-primary justify-center">Começar</a>
                    @endauth
                </div>
            </nav>
        </div>
    </header>

    <main>
        {{-- ============================= HERO ============================= --}}
        <section class="relative isolate overflow-hidden px-5 pb-20 pt-32 sm:px-6 sm:pb-28 sm:pt-40 lg:px-8 lg:pb-36 lg:pt-44">
            <div class="finder-grid finder-grid-hero" aria-hidden="true"></div>
            <div class="finder-orb finder-orb-one" aria-hidden="true"></div>
            <div class="finder-orb finder-orb-two" aria-hidden="true"></div>

            <div class="relative mx-auto max-w-7xl">
                <div class="mx-auto max-w-4xl text-center">
                    <div class="finder-reveal inline-flex items-center gap-2 rounded-full border border-zinc-200 bg-white/75 px-3.5 py-1.5 text-xs font-semibold text-zinc-700 shadow-sm backdrop-blur dark:border-zinc-800 dark:bg-zinc-900/75 dark:text-zinc-200">
                        <span class="size-1.5 rounded-full bg-emerald-500"></span>
                        A tua presença online, sem depender de código
                    </div>

                    <h1 class="finder-reveal finder-delay-1 mt-7 text-balance text-5xl font-bold leading-[0.98] tracking-[-0.055em] sm:text-7xl lg:text-[90px]">
                        Cria o teu website.<br>
                        <span class="finder-gradient-text">Sem complicações.</span>
                    </h1>

                    <p class="finder-reveal finder-delay-2 mx-auto mt-7 max-w-2xl text-pretty text-base leading-7 text-zinc-600 sm:text-lg sm:leading-8 dark:text-zinc-300">
                        O Finder junta editor, temas, media e publicação numa única plataforma — para criares, ajustares e colocares o teu website online ao teu ritmo.
                    </p>

                    <div class="finder-reveal finder-delay-3 mt-9 flex flex-col justify-center gap-3 sm:flex-row">
                        <a
                            href="{{ auth()->check() ? route('site.create') : route('register') }}"
                            class="finder-button finder-button-primary finder-button-lg justify-center"
                        >
                            Começar agora <span aria-hidden="true">→</span>
                        </a>
                        <a href="#como-funciona" class="finder-button finder-button-secondary finder-button-lg justify-center">
                            Ver como funciona
                        </a>
                    </div>

                    <p class="finder-reveal finder-delay-4 mt-5 text-xs font-medium text-zinc-500 dark:text-zinc-400">
                        Começa com uma estrutura pronta a usar. Ajusta cada detalhe depois.
                    </p>
                </div>

                {{-- Hero visual: mockup do editor --}}
                <div class="finder-reveal finder-delay-4 relative mx-auto mt-16 max-w-6xl sm:mt-20">
                    <div class="finder-browser-shadow rounded-[24px] border border-zinc-200/90 bg-white/90 p-2.5 backdrop-blur dark:border-zinc-800 dark:bg-zinc-900/90">
                        <div class="overflow-hidden rounded-[18px] border border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950">
                            <div class="flex h-11 items-center gap-3 border-b border-zinc-200 bg-white px-4 dark:border-zinc-800 dark:bg-zinc-900">
                                <div class="flex gap-1.5">
                                    <span class="size-2.5 rounded-full bg-zinc-300 dark:bg-zinc-700"></span>
                                    <span class="size-2.5 rounded-full bg-zinc-300 dark:bg-zinc-700"></span>
                                    <span class="size-2.5 rounded-full bg-zinc-300 dark:bg-zinc-700"></span>
                                </div>
                                <div class="mx-auto hidden h-6 w-64 rounded-md bg-zinc-100 sm:block dark:bg-zinc-800"></div>
                            </div>

                            <div class="grid min-h-[390px] grid-cols-1 md:grid-cols-[190px_minmax(0,1fr)_230px]">
                                <aside class="hidden border-r border-zinc-200 bg-zinc-50 p-4 md:block dark:border-zinc-800 dark:bg-zinc-900/70">
                                    <div class="flex items-center gap-2">
                                        <span class="finder-mark finder-mark-small"><span></span></span>
                                        <span class="text-xs font-semibold">My website</span>
                                    </div>
                                    <div class="mt-6 space-y-1.5">
                                        @foreach (['Páginas', 'Secções', 'Media', 'Menus', 'Definições'] as $item)
                                            <div class="rounded-lg px-2.5 py-2 text-[11px] font-medium {{ $loop->first ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-950' : 'text-zinc-500' }}">
                                                {{ $item }}
                                            </div>
                                        @endforeach
                                    </div>
                                </aside>

                                <div class="min-w-0 bg-white p-4 sm:p-6 dark:bg-zinc-950">
                                    <div class="flex items-center justify-between border-b border-zinc-100 pb-4 dark:border-zinc-900">
                                        <div>
                                            <div class="h-3 w-28 rounded bg-zinc-200 dark:bg-zinc-800"></div>
                                            <div class="mt-2 h-2 w-20 rounded bg-zinc-100 dark:bg-zinc-900"></div>
                                        </div>
                                        <div class="hidden h-7 w-20 rounded-lg bg-zinc-950 sm:block dark:bg-white"></div>
                                    </div>

                                    <div class="mt-6 rounded-2xl border border-indigo-100 bg-gradient-to-br from-indigo-50 via-white to-white p-6 sm:p-9 dark:border-indigo-500/10 dark:from-indigo-500/10 dark:via-zinc-950 dark:to-zinc-950">
                                        <div class="max-w-md">
                                            <div class="h-4 w-20 rounded bg-indigo-200 dark:bg-indigo-500/30"></div>
                                            <div class="mt-4 h-8 w-full max-w-sm rounded-lg bg-zinc-900 dark:bg-white"></div>
                                            <div class="mt-3 h-3 w-3/4 rounded bg-zinc-200 dark:bg-zinc-800"></div>
                                            <div class="mt-6 flex gap-2">
                                                <div class="h-8 w-24 rounded-lg bg-zinc-900 dark:bg-white"></div>
                                                <div class="h-8 w-20 rounded-lg border border-zinc-200 dark:border-zinc-800"></div>
                                            </div>
                                        </div>
                                        <div class="mt-10 grid grid-cols-3 gap-2 sm:gap-3">
                                            <div class="h-16 rounded-xl bg-white/90 shadow-sm ring-1 ring-zinc-100 dark:bg-zinc-900 dark:ring-zinc-800"></div>
                                            <div class="h-16 rounded-xl bg-white/90 shadow-sm ring-1 ring-zinc-100 dark:bg-zinc-900 dark:ring-zinc-800"></div>
                                            <div class="h-16 rounded-xl bg-white/90 shadow-sm ring-1 ring-zinc-100 dark:bg-zinc-900 dark:ring-zinc-800"></div>
                                        </div>
                                    </div>
                                </div>

                                <aside class="hidden border-l border-zinc-200 bg-zinc-50 p-4 lg:block dark:border-zinc-800 dark:bg-zinc-900/70">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[11px] font-semibold">Propriedades</span>
                                        <span class="size-5 rounded bg-zinc-200 dark:bg-zinc-800"></span>
                                    </div>
                                    <div class="mt-5 space-y-3">
                                        @foreach (['Tipografia', 'Cores', 'Espaçamento', 'Botões'] as $property)
                                            <div class="rounded-xl border border-zinc-200 bg-white p-3 dark:border-zinc-800 dark:bg-zinc-900">
                                                <div class="text-[10px] font-medium text-zinc-500">{{ $property }}</div>
                                                <div class="mt-2 h-2 rounded bg-zinc-100 dark:bg-zinc-800"></div>
                                            </div>
                                        @endforeach
                                    </div>
                                </aside>
                            </div>
                        </div>
                    </div>

                    {{-- elementos flutuantes --}}
                    <div class="finder-float-card absolute -bottom-5 -left-3 hidden rounded-2xl border border-zinc-200 bg-white p-3 shadow-xl sm:flex sm:items-center sm:gap-3 dark:border-zinc-800 dark:bg-zinc-900">
                        <span class="flex size-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400" aria-hidden="true">
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        </span>
                        <div>
                            <p class="text-[11px] font-semibold">Website guardado</p>
                            <p class="text-[10px] text-zinc-500">Pronto para publicar</p>
                        </div>
                    </div>

                    <div class="finder-float-card absolute -right-3 -top-5 hidden rounded-2xl border border-zinc-200 bg-white p-3 shadow-xl sm:flex sm:items-center sm:gap-3 dark:border-zinc-800 dark:bg-zinc-900" style="animation-delay: .4s">
                        <span class="flex size-9 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400" aria-hidden="true">
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 3v18M3 9h18"/></svg>
                        </span>
                        <div>
                            <p class="text-[11px] font-semibold">Nova página</p>
                            <p class="text-[10px] text-zinc-500">Adicionada ao site</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ===================== FUNCIONALIDADES / CAPACIDADES ===================== --}}
        <section id="funcionalidades" class="border-y border-zinc-200/80 bg-zinc-50/70 px-5 py-24 sm:px-6 lg:px-8 lg:py-28 dark:border-zinc-900 dark:bg-zinc-900/30">
            <div class="mx-auto max-w-7xl">
                <div class="grid gap-12 lg:grid-cols-[0.8fr_1.2fr] lg:items-end">
                    <div>
                        <p class="finder-kicker">Uma plataforma, todo o fluxo</p>
                        <h2 class="mt-3 text-3xl font-bold tracking-[-0.035em] sm:text-5xl">
                            Mais do que um construtor de websites.
                        </h2>
                    </div>
                    <p class="max-w-xl text-base leading-7 text-zinc-600 lg:justify-self-end dark:text-zinc-400">
                        Do primeiro rascunho à publicação, o Finder centraliza criação, personalização e gestão numa experiência coerente.
                    </p>
                </div>

                <div class="mt-12 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ([
                        ['icon' => 'M4 6h16M4 12h16M4 18h7', 'title' => 'Editor visual', 'text' => 'Cria e organiza páginas e secções através de uma experiência pensada para iterar rapidamente.'],
                        ['icon' => 'M4 5h16v4H4zM4 13h10v6H4zM16 13h4v6h-4z', 'title' => 'Templates', 'text' => 'Começa com uma estrutura profissional em vez de partir de uma página em branco.'],
                        ['icon' => 'M12 3l2.5 5 5.5.8-4 4 1 5.5-5-2.6-5 2.6 1-5.5-4-4 5.5-.8z', 'title' => 'Personalização', 'text' => 'Controla tema, conteúdo e identidade visual a partir de um único painel.'],
                        ['icon' => 'M4 5h16v12H4zM8 21h8', 'title' => 'Media', 'text' => 'Gere as imagens e recursos visuais que compõem o teu website.'],
                        ['icon' => 'M4 6h16M4 12h10M4 18h16', 'title' => 'Menus', 'text' => 'Cria e organiza a navegação do teu site sem tocar em código.'],
                        ['icon' => 'M5 13l4 4L19 7', 'title' => 'Preview e publicação', 'text' => 'Confirma o resultado em modo de pré-visualização antes de colocares o site online.'],
                    ] as $feature)
                        <article class="finder-card group rounded-2xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-950">
                            <div class="flex items-start justify-between">
                                <span class="flex size-10 items-center justify-center rounded-xl bg-zinc-950 text-white dark:bg-white dark:text-zinc-950" aria-hidden="true">
                                    <svg class="size-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $feature['icon'] }}"/></svg>
                                </span>
                                <span class="text-zinc-300 transition group-hover:-translate-y-1 group-hover:translate-x-1 dark:text-zinc-700" aria-hidden="true">↗</span>
                            </div>
                            <h3 class="mt-8 text-base font-semibold">{{ $feature['title'] }}</h3>
                            <p class="mt-2 text-sm leading-6 text-zinc-500 dark:text-zinc-400">{{ $feature['text'] }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ============================= COMO FUNCIONA ============================= --}}
        <section id="como-funciona" class="px-5 py-24 sm:px-6 lg:px-8 lg:py-32">
            <div class="mx-auto max-w-7xl">
                <div class="max-w-2xl">
                    <p class="finder-kicker">Como funciona</p>
                    <h2 class="mt-3 text-3xl font-bold tracking-[-0.035em] sm:text-5xl">
                        Da ideia ao website publicado, sem complicar.
                    </h2>
                </div>

                <div class="relative mt-14">
                    {{-- linha de progresso: horizontal em desktop, vertical em mobile --}}
                    <div class="pointer-events-none absolute left-6 top-0 bottom-0 w-px bg-zinc-200 md:left-0 md:right-0 md:top-6 md:h-px md:w-auto md:bottom-auto dark:bg-zinc-800" aria-hidden="true"></div>

                    <div class="grid gap-8 md:grid-cols-4 md:gap-6">
                        @foreach ([
                            ['01', 'Cria', 'Cria a tua conta e inicia o teu primeiro website.'],
                            ['02', 'Escolhe', 'Seleciona a estrutura mais adequada ao teu objetivo.'],
                            ['03', 'Personaliza', 'Edita páginas, secções, conteúdo, cores e componentes.'],
                            ['04', 'Publica', 'Faz preview, guarda as alterações e publica quando estiveres pronto.'],
                        ] as $step)
                            <div class="relative flex gap-4 md:block md:gap-0">
                                <span class="relative z-10 flex size-12 shrink-0 items-center justify-center rounded-full border-4 border-white bg-zinc-950 text-xs font-bold text-white dark:border-zinc-950 dark:bg-white dark:text-zinc-950">
                                    {{ $step[0] }}
                                </span>
                                <div class="md:mt-6">
                                    <h3 class="text-lg font-semibold">{{ $step[1] }}</h3>
                                    <p class="mt-2 max-w-[26ch] text-sm leading-6 text-zinc-500 dark:text-zinc-400">{{ $step[2] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        {{-- ============================= SOLUÇÕES ============================= --}}
        <section id="solucoes" class="px-5 pb-24 sm:px-6 lg:px-8 lg:pb-32">
            <div class="mx-auto max-w-7xl rounded-[32px] bg-zinc-950 px-6 py-12 text-white sm:px-10 sm:py-16 lg:px-16">
                <div class="grid gap-12 lg:grid-cols-[0.9fr_1.1fr] lg:items-center">
                    <div>
                        <p class="text-sm font-semibold text-indigo-300">Feito para diferentes projetos</p>
                        <h2 class="mt-3 text-3xl font-bold tracking-[-0.035em] sm:text-5xl">
                            Uma base que acompanha aquilo que queres construir.
                        </h2>
                        <p class="mt-5 max-w-xl leading-7 text-zinc-400">
                            Começa com uma estrutura adequada ao teu objetivo e adapta páginas, conteúdo, identidade visual e gestão ao longo do tempo.
                        </p>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        @foreach (['Negócios', 'Portfólios', 'Lojas', 'Restaurantes', 'Serviços', 'Landing pages'] as $solution)
                            <div class="rounded-2xl border border-white/10 bg-white/[0.04] p-5 transition hover:bg-white/[0.08]">
                                <span class="text-sm font-semibold">{{ $solution }}</span>
                                <span class="mt-8 block text-xs text-zinc-500">Estrutura adaptável</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        {{-- ============================= PREÇOS ============================= --}}
        <section id="precos" class="border-y border-zinc-200 bg-zinc-50/70 px-5 py-24 sm:px-6 lg:px-8 lg:py-32 dark:border-zinc-900 dark:bg-zinc-900/20">
            <div class="mx-auto max-w-7xl">
                <div class="mx-auto max-w-2xl text-center">
                    <p class="finder-kicker">Preços</p>
                    <h2 class="mt-3 text-3xl font-bold tracking-[-0.035em] sm:text-5xl">
                        Escolhe o plano que faz sentido para ti.
                    </h2>
                    <p class="mt-4 text-base leading-7 text-zinc-600 dark:text-zinc-400">
                        Os valores apresentados são carregados dos planos configurados no Finder.
                    </p>
                </div>

                <div class="mx-auto mt-12 grid max-w-5xl gap-5 md:grid-cols-2">
                    @forelse ($plans as $plan)
                        <article class="relative flex flex-col rounded-3xl border {{ $plan->name === 'Pro' ? 'border-indigo-500 bg-white shadow-xl shadow-indigo-500/10 dark:bg-zinc-950' : 'border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-950' }} p-7 sm:p-8">
                            @if ($plan->name === 'Pro')
                                <span class="absolute right-6 top-6 rounded-full bg-indigo-50 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300">
                                    Recomendado
                                </span>
                            @endif

                            <h3 class="text-lg font-semibold">{{ $plan->name }}</h3>

                            <div class="mt-5 flex items-end gap-1">
                                <span class="text-4xl font-bold tracking-tight">
                                    {{ number_format($plan->price_monthly / 100, $plan->price_monthly % 100 === 0 ? 0 : 2, ',', '.') }}€
                                </span>
                                <span class="pb-1 text-sm text-zinc-500">/mês</span>
                            </div>

                            <ul class="mt-7 space-y-3 text-sm text-zinc-600 dark:text-zinc-400">
                                <li class="flex gap-2"><span class="text-emerald-500">✓</span>Até {{ $plan->product_limit }} produtos</li>
                                <li class="flex gap-2"><span class="text-emerald-500">✓</span>{{ $plan->has_ai ? 'IA disponível' : 'Base essencial' }}</li>
                                <li class="flex gap-2"><span class="text-emerald-500">✓</span>{{ $plan->has_reports ? 'Relatórios incluídos' : 'Gestão simples' }}</li>
                            </ul>

                            <a
                                href="{{ auth()->check() ? route('saas.upgrade') : route('register') }}"
                                class="{{ $plan->name === 'Pro' ? 'finder-button-primary' : 'finder-button-secondary' }} finder-button mt-8 w-full justify-center"
                            >
                                {{ $plan->name === 'Free' ? 'Começar gratuitamente' : 'Ver plano' }}
                            </a>
                        </article>
                    @empty
                        <div class="rounded-3xl border border-zinc-200 bg-white p-8 text-center text-sm text-zinc-500 md:col-span-2 dark:border-zinc-800 dark:bg-zinc-950">
                            Os planos serão apresentados aqui quando estiverem configurados.
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        {{-- ============================= FAQ ============================= --}}
        <section id="faq" class="px-5 py-24 sm:px-6 lg:px-8 lg:py-32">
            <div class="mx-auto max-w-3xl">
                <div class="text-center">
                    <p class="finder-kicker">Perguntas frequentes</p>
                    <h2 class="mt-3 text-3xl font-bold tracking-[-0.035em] sm:text-5xl">Tudo o que precisas de saber.</h2>
                </div>

                <div class="mt-12 divide-y divide-zinc-200 border-y border-zinc-200 dark:divide-zinc-800 dark:border-zinc-800">
                    @foreach ([
                        ['Preciso de saber programar?', 'Não. O editor visual do Finder foi pensado para criares e ajustares o teu website sem escrever código.'],
                        ['Posso personalizar o meu website?', 'Sim. Podes ajustar tema, cores, conteúdo e componentes a partir do painel de personalização.'],
                        ['Posso criar várias páginas?', 'Sim. O editor permite criar e organizar páginas e secções conforme a estrutura do teu website.'],
                        ['Posso ver o website antes de publicar?', 'Sim. Tens uma pré-visualização disponível antes de qualquer publicação.'],
                        ['Como funciona a publicação?', 'Depois de guardares as alterações, podes publicar o website quando estiveres satisfeito com o resultado.'],
                    ] as $index => $faq)
                        <div>
                            <button
                                type="button"
                                class="flex w-full items-center justify-between gap-4 py-5 text-left text-sm font-semibold sm:text-base"
                                x-on:click="faqOpen = faqOpen === {{ $index }} ? null : {{ $index }}"
                                :aria-expanded="(faqOpen === {{ $index }}).toString()"
                                aria-controls="faq-panel-{{ $index }}"
                            >
                                <span>{{ $faq[0] }}</span>
                                <svg class="size-4 shrink-0 transition-transform" :class="faqOpen === {{ $index }} ? 'rotate-45' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" d="M12 5v14M5 12h14"/>
                                </svg>
                            </button>
                            <div
                                id="faq-panel-{{ $index }}"
                                x-cloak
                                x-show="faqOpen === {{ $index }}"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 -translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                class="pb-5 pr-8 text-sm leading-6 text-zinc-500 dark:text-zinc-400"
                            >
                                {{ $faq[1] }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ============================= CTA FINAL ============================= --}}
        <section class="px-5 pb-24 sm:px-6 lg:px-8 lg:pb-32">
            <div class="finder-cta relative mx-auto max-w-6xl overflow-hidden rounded-[32px] px-6 py-14 text-center sm:px-10 sm:py-20">
                <div class="relative">
                    <p class="text-sm font-semibold text-indigo-600 dark:text-indigo-400">Começa agora</p>
                    <h2 class="mx-auto mt-3 max-w-3xl text-4xl font-bold tracking-[-0.045em] sm:text-6xl">
                        O teu próximo website pode começar hoje.
                    </h2>
                    <p class="mx-auto mt-5 max-w-xl leading-7 text-zinc-600 dark:text-zinc-400">
                        Cria o teu primeiro website no Finder e transforma a tua ideia numa presença digital profissional.
                    </p>
                    <a
                        href="{{ auth()->check() ? route('site.create') : route('register') }}"
                        class="finder-button finder-button-primary finder-button-lg mt-8"
                    >
                        Começar agora <span aria-hidden="true">→</span>
                    </a>
                </div>
            </div>
        </section>
    </main>

    {{-- ============================= FOOTER ============================= --}}
    <footer class="border-t border-zinc-200 px-5 py-12 sm:px-6 lg:px-8 dark:border-zinc-900">
        <div class="mx-auto max-w-7xl">
            <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                        <span class="finder-mark finder-mark-small"><span></span></span>
                        <span class="font-bold">Finder</span>
                    </a>
                    <p class="mt-4 max-w-xs text-sm leading-6 text-zinc-500">
                        Cria, personaliza e publica websites profissionais a partir de uma única plataforma.
                    </p>
                </div>

                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-900 dark:text-white">Produto</h3>
                    <div class="mt-4 space-y-3 text-sm text-zinc-500">
                        <a href="#funcionalidades" class="block hover:text-zinc-950 dark:hover:text-white">Funcionalidades</a>
                        <a href="#como-funciona" class="block hover:text-zinc-950 dark:hover:text-white">Como funciona</a>
                        <a href="#precos" class="block hover:text-zinc-950 dark:hover:text-white">Preços</a>
                    </div>
                </div>

                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-900 dark:text-white">Conta</h3>
                    <div class="mt-4 space-y-3 text-sm text-zinc-500">
                        @auth
                            <a href="{{ route('dashboard') }}" class="block hover:text-zinc-950 dark:hover:text-white">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="block hover:text-zinc-950 dark:hover:text-white">Entrar</a>
                            <a href="{{ route('register') }}" class="block hover:text-zinc-950 dark:hover:text-white">Criar conta</a>
                        @endauth
                    </div>
                </div>

                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-900 dark:text-white">Finder</h3>
                    <div class="mt-4 space-y-3 text-sm text-zinc-500">
                        <a href="#solucoes" class="block hover:text-zinc-950 dark:hover:text-white">Soluções</a>
                        <a href="#faq" class="block hover:text-zinc-950 dark:hover:text-white">Perguntas frequentes</a>
                    </div>
                </div>
            </div>

            <div class="mt-12 flex flex-col gap-3 border-t border-zinc-200 pt-6 text-xs text-zinc-500 sm:flex-row sm:items-center sm:justify-between dark:border-zinc-900">
                <span>© {{ date('Y') }} Finder. Todos os direitos reservados.</span>
                <span>Cria. Personaliza. Publica.</span>
            </div>
        </div>
    </footer>
</div>
