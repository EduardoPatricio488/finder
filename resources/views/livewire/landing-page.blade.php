<div
    x-data="{ mobileOpen: false, scrolled: false, faqOpen: null }"
    x-on:scroll.window="scrolled = window.scrollY > 16"
    class="min-h-screen overflow-x-clip bg-white text-zinc-950 dark:bg-zinc-950 dark:text-white"
>
    <header class="fixed inset-x-0 top-0 z-50 border-b border-zinc-200/80 bg-white/90 shadow-sm backdrop-blur-xl dark:border-zinc-800/80 dark:bg-zinc-950/90">
        <div class="mx-auto flex h-[68px] max-w-7xl items-center justify-between px-5 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                <span class="finder-mark" aria-hidden="true"><span></span></span>
                <span class="text-[17px] font-bold tracking-[-0.04em]">Finder</span>
            </a>
            <nav class="hidden items-center gap-8 text-[13px] font-medium text-zinc-600 lg:flex dark:text-zinc-300">
                <a href="#funcionalidades">Funcionalidades</a>
                <a href="#como-funciona">Como funciona</a>
                <a href="#solucoes">Soluções</a>
                <a href="#precos">Preços</a>
                <a href="#faq">Perguntas frequentes</a>
            </nav>
            <div class="hidden items-center gap-2.5 sm:flex">
                @auth
                    <a href="{{ route('dashboard') }}" class="finder-button finder-button-ghost">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="finder-button finder-button-ghost">Entrar</a>
                    <a href="{{ route('register') }}" class="finder-button finder-button-primary">Começar agora →</a>
                @endauth
            </div>
            <button type="button" class="inline-flex size-10 items-center justify-center rounded-xl border border-zinc-200 sm:hidden" x-on:click="mobileOpen = !mobileOpen" aria-label="Abrir menu">
                <span class="text-xl">☰</span>
            </button>
        </div>
        <div x-cloak x-show="mobileOpen" class="border-t border-zinc-200 bg-white px-5 py-5 sm:hidden dark:border-zinc-800 dark:bg-zinc-950">
            <nav class="flex flex-col gap-1">
                @foreach ([['#funcionalidades', 'Funcionalidades'], ['#como-funciona', 'Como funciona'], ['#solucoes', 'Soluções'], ['#precos', 'Preços'], ['#faq', 'Perguntas frequentes']] as $link)
                    <a href="{{ $link[0] }}" x-on:click="mobileOpen = false" class="rounded-xl px-4 py-3 text-sm font-medium hover:bg-zinc-100">{{ $link[1] }}</a>
                @endforeach
            </nav>
        </div>
    </header>

    <main>
        <section class="relative overflow-hidden px-5 pb-20 pt-32 sm:px-6 sm:pb-28 sm:pt-40 lg:px-8 lg:pb-36 lg:pt-44">
            <div class="finder-grid finder-grid-hero" aria-hidden="true"></div>
            <div class="finder-orb finder-orb-one" aria-hidden="true"></div>
            <div class="finder-orb finder-orb-two" aria-hidden="true"></div>
            <div class="relative mx-auto max-w-7xl">
                <div class="mx-auto max-w-4xl text-center">
                    <div class="inline-flex items-center gap-2 rounded-full border border-zinc-200 bg-white/75 px-3.5 py-1.5 text-xs font-semibold text-zinc-700 shadow-sm backdrop-blur">
                        <span class="size-1.5 rounded-full bg-emerald-500"></span>
                        A tua presença online, sem depender de código
                    </div>
                    <h1 class="mt-7 text-balance text-5xl font-bold leading-[0.98] tracking-[-0.055em] sm:text-7xl lg:text-[90px]">
                        Cria o teu website.<br><span class="finder-gradient-text">Sem complicações.</span>
                    </h1>
                    <p class="mx-auto mt-7 max-w-2xl text-base leading-7 text-zinc-600 sm:text-lg sm:leading-8 dark:text-zinc-300">
                        O Finder junta editor, temas, media e publicação numa única plataforma — para criares, ajustares e colocares o teu website online ao teu ritmo.
                    </p>
                    <div class="mt-9 flex flex-col justify-center gap-3 sm:flex-row">
                        <a href="{{ auth()->check() ? route('site.create') : route('register') }}" class="finder-button finder-button-primary finder-button-lg justify-center">Começar agora →</a>
                        <a href="#como-funciona" class="finder-button finder-button-secondary finder-button-lg justify-center">Ver como funciona</a>
                    </div>
                </div>

                <div class="relative mx-auto mt-16 max-w-6xl">
                    <div class="finder-browser-shadow rounded-[24px] border border-zinc-200/90 bg-white/90 p-2.5">
                        <div class="overflow-hidden rounded-[18px] border border-zinc-200 bg-zinc-50">
                            <div class="flex h-11 items-center gap-3 border-b border-zinc-200 bg-white px-4">
                                <div class="flex gap-1.5"><span class="size-2.5 rounded-full bg-zinc-300"></span><span class="size-2.5 rounded-full bg-zinc-300"></span><span class="size-2.5 rounded-full bg-zinc-300"></span></div>
                                <div class="mx-auto h-6 w-64 rounded-md bg-zinc-100"></div>
                            </div>
                            <div class="grid min-h-[390px] grid-cols-1 md:grid-cols-[190px_minmax(0,1fr)_230px]">
                                <aside class="hidden border-r border-zinc-200 bg-zinc-50 p-4 md:block">
                                    <div class="flex items-center gap-2"><span class="finder-mark finder-mark-small"><span></span></span><span class="text-xs font-semibold">My website</span></div>
                                    <div class="mt-6 space-y-1.5">@foreach (['Páginas','Secções','Media','Menus','Definições'] as $item)<div class="rounded-lg px-2.5 py-2 text-[11px] font-medium {{ $loop->first ? 'bg-zinc-900 text-white' : 'text-zinc-500' }}">{{ $item }}</div>@endforeach</div>
                                </aside>
                                <div class="min-w-0 bg-white p-4 sm:p-6">
                                    <div class="flex items-center justify-between border-b border-zinc-100 pb-4"><div><div class="h-3 w-28 rounded bg-zinc-200"></div><div class="mt-2 h-2 w-20 rounded bg-zinc-100"></div></div><div class="hidden h-7 w-20 rounded-lg bg-zinc-950 sm:block"></div></div>
                                    <div class="mt-6 rounded-2xl border border-indigo-100 bg-gradient-to-br from-indigo-50 via-white to-white p-6 sm:p-9">
                                        <div class="max-w-md"><div class="h-4 w-20 rounded bg-indigo-200"></div><div class="mt-4 h-8 w-full max-w-sm rounded-lg bg-zinc-900"></div><div class="mt-3 h-3 w-3/4 rounded bg-zinc-200"></div><div class="mt-6 flex gap-2"><div class="h-8 w-24 rounded-lg bg-zinc-900"></div><div class="h-8 w-20 rounded-lg border border-zinc-200"></div></div></div>
                                        <div class="mt-10 grid grid-cols-3 gap-2 sm:gap-3"><div class="h-16 rounded-xl bg-white shadow-sm"></div><div class="h-16 rounded-xl bg-white shadow-sm"></div><div class="h-16 rounded-xl bg-white shadow-sm"></div></div>
                                    </div>
                                </div>
                                <aside class="hidden border-l border-zinc-200 bg-zinc-50 p-4 lg:block">
                                    <span class="text-[11px] font-semibold">Propriedades</span>
                                    <div class="mt-5 space-y-3">@foreach (['Tipografia','Cores','Espaçamento','Botões'] as $property)<div class="rounded-xl border border-zinc-200 bg-white p-3"><div class="text-[10px] font-medium text-zinc-500">{{ $property }}</div><div class="mt-2 h-2 rounded bg-zinc-100"></div></div>@endforeach</div>
                                </aside>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="funcionalidades" class="border-y border-zinc-200/80 bg-zinc-50/70 px-5 py-24 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-7xl">
                <p class="finder-kicker">Uma plataforma, todo o fluxo</p>
                <h2 class="mt-3 text-3xl font-bold tracking-[-0.035em] sm:text-5xl">Mais do que um construtor de websites.</h2>
                <div class="mt-12 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ([['Editor visual','Cria e organiza páginas e secções rapidamente.'],['Templates','Começa com uma estrutura profissional.'],['Personalização','Controla tema, conteúdo e identidade visual.'],['Media','Gere as imagens e recursos visuais.'],['Menus','Organiza a navegação sem tocar em código.'],['Preview e publicação','Confirma o resultado antes de publicar.']] as $feature)
                        <article class="finder-card rounded-2xl border border-zinc-200 bg-white p-6">
                            <span class="flex size-10 items-center justify-center rounded-xl bg-zinc-950 text-white">✓</span>
                            <h3 class="mt-8 text-base font-semibold">{{ $feature[0] }}</h3>
                            <p class="mt-2 text-sm leading-6 text-zinc-500">{{ $feature[1] }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="como-funciona" class="px-5 py-24 sm:px-6 lg:px-8 lg:py-32">
            <div class="mx-auto max-w-7xl">
                <p class="finder-kicker">Como funciona</p>
                <h2 class="mt-3 text-3xl font-bold sm:text-5xl">Da ideia ao website publicado, sem complicar.</h2>
                <div class="mt-14 grid gap-8 md:grid-cols-4">
                    @foreach ([['01','Cria','Cria a tua conta e inicia o teu primeiro website.'],['02','Escolhe','Seleciona a estrutura adequada.'],['03','Personaliza','Edita páginas, conteúdo, cores e componentes.'],['04','Publica','Faz preview e publica quando estiveres pronto.']] as $step)
                        <div><span class="flex size-12 items-center justify-center rounded-full bg-zinc-950 text-xs font-bold text-white">{{ $step[0] }}</span><h3 class="mt-5 text-lg font-semibold">{{ $step[1] }}</h3><p class="mt-2 text-sm leading-6 text-zinc-500">{{ $step[2] }}</p></div>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="solucoes" class="px-5 pb-24 sm:px-6 lg:px-8 lg:pb-32">
            <div class="mx-auto max-w-7xl rounded-[32px] bg-zinc-950 px-6 py-12 text-white sm:px-10 lg:px-16">
                <p class="text-sm font-semibold text-indigo-300">Feito para diferentes projetos</p>
                <h2 class="mt-3 text-3xl font-bold sm:text-5xl">Uma base que acompanha aquilo que queres construir.</h2>
                <div class="mt-10 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">@foreach (['Negócios','Portfólios','Lojas','Restaurantes','Serviços','Landing pages'] as $solution)<div class="rounded-2xl border border-white/10 bg-white/[0.04] p-5"><span class="text-sm font-semibold">{{ $solution }}</span></div>@endforeach</div>
            </div>
        </section>

        <section id="precos" class="border-y border-zinc-200 bg-zinc-50/70 px-5 py-24 sm:px-6 lg:px-8 lg:py-32">
            <div class="mx-auto max-w-7xl">
                <div class="mx-auto max-w-2xl text-center"><p class="finder-kicker">Preços</p><h2 class="mt-3 text-3xl font-bold sm:text-5xl">Escolhe o plano que faz sentido para ti.</h2><p class="mt-4 text-base text-zinc-600">Os valores são carregados dos planos configurados no Finder.</p></div>
                <div class="mx-auto mt-12 grid max-w-5xl gap-5 md:grid-cols-2">
                    @forelse ($plans as $plan)
                        <article class="rounded-3xl border border-zinc-200 bg-white p-7 sm:p-8 {{ $plan->name === 'Pro' ? 'border-indigo-500 shadow-xl shadow-indigo-500/10' : '' }}">
                            <h3 class="text-lg font-semibold">{{ $plan->name }}</h3>
                            <div class="mt-5 flex items-end gap-1"><span class="text-4xl font-bold">{{ number_format($plan->price_monthly / 100, $plan->price_monthly % 100 === 0 ? 0 : 2, ',', '.') }}€</span><span class="pb-1 text-sm text-zinc-500">/mês</span></div>
                            <ul class="mt-7 space-y-3 text-sm text-zinc-600">
                                <li>✓ Até {{ $plan->product_limit }} produtos</li>
                                <li>✓ {{ $plan->has_ai ? 'IA disponível' : 'Base essencial' }}</li>
                                <li>✓ {{ $plan->has_reports ? 'Relatórios incluídos' : 'Gestão simples' }}</li>
                            </ul>
                            <a href="{{ auth()->check() ? route('saas.upgrade') : route('register') }}" class="finder-button finder-button-primary mt-8 w-full justify-center">Ver plano</a>
                        </article>
                    @empty
                        <div class="rounded-3xl border border-zinc-200 bg-white p-8 text-center text-sm text-zinc-500 md:col-span-2">Os planos serão apresentados aqui quando estiverem configurados.</div>
                    @endforelse
                </div>
            </div>
        </section>

        <section id="faq" class="px-5 py-24 sm:px-6 lg:px-8 lg:py-32">
            <div class="mx-auto max-w-3xl">
                <div class="text-center"><p class="finder-kicker">Perguntas frequentes</p><h2 class="mt-3 text-3xl font-bold sm:text-5xl">Tudo o que precisas de saber.</h2></div>
                <div class="mt-12 divide-y divide-zinc-200 border-y border-zinc-200">
                    @foreach ([['Preciso de saber programar?','Não. O editor visual do Finder foi pensado para criares e ajustares o teu website sem escrever código.'],['Posso personalizar o meu website?','Sim. Podes ajustar tema, cores, conteúdo e componentes.'],['Posso criar várias páginas?','Sim. O editor permite criar e organizar páginas e secções.'],['Posso ver o website antes de publicar?','Sim. Tens uma pré-visualização disponível.'],['Como funciona a publicação?','Depois de guardares as alterações, podes publicar quando estiveres satisfeito.']] as $index => $faq)
                        <div><button type="button" class="flex w-full items-center justify-between gap-4 py-5 text-left text-sm font-semibold" x-on:click="faqOpen = faqOpen === {{ $index }} ? null : {{ $index }}"><span>{{ $faq[0] }}</span><span class="text-xl">+</span></button><div x-cloak x-show="faqOpen === {{ $index }}" class="pb-5 pr-8 text-sm leading-6 text-zinc-500">{{ $faq[1] }}</div></div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="px-5 pb-24 sm:px-6 lg:px-8 lg:pb-32">
            <div class="finder-cta mx-auto max-w-6xl rounded-[32px] px-6 py-14 text-center sm:px-10 sm:py-20">
                <p class="text-sm font-semibold text-indigo-600">Começa agora</p>
                <h2 class="mx-auto mt-3 max-w-3xl text-4xl font-bold sm:text-6xl">O teu próximo website pode começar hoje.</h2>
                <p class="mx-auto mt-5 max-w-xl text-zinc-600">Cria o teu primeiro website no Finder e transforma a tua ideia numa presença digital profissional.</p>
                <a href="{{ auth()->check() ? route('site.create') : route('register') }}" class="finder-button finder-button-primary finder-button-lg mt-8">Começar agora →</a>
            </div>
        </section>
    </main>

    <footer class="border-t border-zinc-200 px-5 py-12 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl flex flex-col gap-3 text-sm text-zinc-500 sm:flex-row sm:items-center sm:justify-between">
            <span>© {{ date('Y') }} Finder. Todos os direitos reservados.</span>
            <span>Cria. Personaliza. Publica.</span>
        </div>
    </footer>
</div>
