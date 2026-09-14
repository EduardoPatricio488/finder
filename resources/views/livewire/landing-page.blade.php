<div>
    <header class="absolute inset-x-0 top-0 z-20">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-5 py-5 sm:px-6">
            <a href="{{ route('home') }}" class="text-lg font-bold tracking-tight">FINDER</a>
            <nav class="hidden items-center gap-7 text-sm text-zinc-600 md:flex dark:text-zinc-300">
                <a href="#funcionalidades" class="hover:text-zinc-950 dark:hover:text-white">Funcionalidades</a>
                <a href="#como-funciona" class="hover:text-zinc-950 dark:hover:text-white">Como funciona</a>
                <a href="#precos" class="hover:text-zinc-950 dark:hover:text-white">Preços</a>
            </nav>
            <div class="flex items-center gap-2">
                @auth
                    <a href="{{ route('dashboard') }}" class="rounded-xl px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-100 dark:text-zinc-200 dark:hover:bg-zinc-800">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="rounded-xl px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-100 dark:text-zinc-200 dark:hover:bg-zinc-800">Entrar</a>
                    <a href="{{ route('register') }}" class="rounded-xl bg-zinc-950 px-4 py-2 text-sm font-semibold text-white dark:bg-white dark:text-zinc-950">Começar</a>
                @endauth
            </div>
        </div>
    </header>

    <section class="relative overflow-hidden px-5 pb-24 pt-36 sm:pt-44">
        <div class="absolute left-1/2 top-0 -z-10 h-[520px] w-[900px] -translate-x-1/2 rounded-full bg-indigo-200/40 blur-3xl dark:bg-indigo-950/30"></div>
        <div class="mx-auto max-w-5xl text-center">
            <div class="mx-auto inline-flex rounded-full border border-indigo-200 bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700 dark:border-indigo-500/20 dark:bg-indigo-500/10 dark:text-indigo-300">Website builder · Website-as-a-Service</div>
            <h1 class="mt-7 text-5xl font-bold tracking-[-0.04em] sm:text-7xl">Build your website.<br><span class="text-indigo-600 dark:text-indigo-400">Make it yours.</span></h1>
            <p class="mx-auto mt-7 max-w-2xl text-lg leading-8 text-zinc-600 dark:text-zinc-300">Uma plataforma para criar, personalizar, gerir e publicar websites profissionais sem começar do zero.</p>
            <div class="mt-9 flex flex-col justify-center gap-3 sm:flex-row">
                <a href="{{ auth()->check() ? route('site.create') : route('register') }}" class="rounded-xl bg-zinc-950 px-6 py-3.5 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 dark:bg-white dark:text-zinc-950">Criar o meu website</a>
                <a href="#como-funciona" class="rounded-xl border border-zinc-200 bg-white px-6 py-3.5 text-sm font-semibold text-zinc-700 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-200">Ver como funciona</a>
            </div>
        </div>
        <div class="mx-auto mt-16 max-w-6xl rounded-3xl border border-zinc-200 bg-zinc-50 p-3 shadow-2xl dark:border-zinc-800 dark:bg-zinc-900">
            <div class="rounded-2xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-950">
                <div class="grid min-h-[360px] grid-cols-[180px_1fr_220px] gap-3">
                    <div class="hidden rounded-xl bg-zinc-50 p-4 sm:block dark:bg-zinc-900"><div class="h-3 w-20 rounded bg-zinc-200 dark:bg-zinc-700"></div><div class="mt-6 space-y-2">@for($i=0;$i<8;$i++)<div class="h-8 rounded-lg bg-zinc-100 dark:bg-zinc-800"></div>@endfor</div></div>
                    <div class="rounded-xl border border-zinc-100 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-950"><div class="mx-auto max-w-lg pt-10 text-center"><div class="mx-auto h-8 w-3/4 rounded-lg bg-zinc-100 dark:bg-zinc-800"></div><div class="mx-auto mt-4 h-4 w-1/2 rounded bg-zinc-100 dark:bg-zinc-800"></div><div class="mx-auto mt-8 h-10 w-32 rounded-xl bg-indigo-500"></div></div></div>
                    <div class="hidden rounded-xl bg-zinc-50 p-4 md:block dark:bg-zinc-900"><div class="h-3 w-24 rounded bg-zinc-200 dark:bg-zinc-700"></div><div class="mt-5 space-y-4">@for($i=0;$i<5;$i++)<div class="h-12 rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800"></div>@endfor</div></div>
                </div>
            </div>
        </div>
    </section>

    <section id="funcionalidades" class="border-y border-zinc-100 px-5 py-24 dark:border-zinc-900">
        <div class="mx-auto max-w-7xl">
            <div class="max-w-2xl"><p class="text-sm font-semibold text-indigo-600">Tudo num só lugar</p><h2 class="mt-2 text-3xl font-bold tracking-tight sm:text-4xl">Do primeiro clique à publicação.</h2></div>
            <div class="mt-12 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                @foreach([['Criador visual','Edita páginas e secções com uma experiência visual rápida e sem código.'],['Templates','Começa com uma estrutura profissional e adapta-a à tua marca.'],['Design system','Cores, tipografia, espaçamento, botões e componentes consistentes.'],['Produtos','Cria produtos, categorias e prepara a tua loja para vender.'],['Analytics','Acompanha visitas e atividade com dados reais, sem métricas fictícias.'],['Publicação','Guarda rascunhos, faz preview e publica quando estiveres pronto.']] as $feature)
                    <article class="rounded-2xl border border-zinc-200 p-6 dark:border-zinc-800"><div class="size-10 rounded-xl bg-indigo-50 dark:bg-indigo-500/10"></div><h3 class="mt-5 font-semibold">{{ $feature[0] }}</h3><p class="mt-2 text-sm leading-6 text-zinc-500 dark:text-zinc-400">{{ $feature[1] }}</p></article>
                @endforeach
            </div>
        </div>
    </section>

    <section id="como-funciona" class="px-5 py-24">
        <div class="mx-auto max-w-7xl"><div class="text-center"><p class="text-sm font-semibold text-indigo-600">Simples por design</p><h2 class="mt-2 text-3xl font-bold tracking-tight sm:text-4xl">Cria em minutos.</h2></div><div class="mt-12 grid gap-5 md:grid-cols-4">@foreach([['01','Cria a conta','Entra na plataforma e cria o teu primeiro projeto.'],['02','Escolhe o tipo','Empresa, portfólio, loja, restaurante e muito mais.'],['03','Personaliza','Edita páginas, conteúdo, cores e componentes.'],['04','Publica','Faz preview, guarda e coloca o website online.']] as $step)<div class="rounded-2xl bg-zinc-50 p-6 dark:bg-zinc-900"><span class="text-xs font-bold text-indigo-600">{{ $step[0] }}</span><h3 class="mt-8 font-semibold">{{ $step[1] }}</h3><p class="mt-2 text-sm leading-6 text-zinc-500">{{ $step[2] }}</p></div>@endforeach</div></div>
    </section>

    <section id="precos" class="bg-zinc-950 px-5 py-24 text-white dark:bg-black"><div class="mx-auto max-w-5xl text-center"><h2 class="text-3xl font-bold sm:text-4xl">Começa sem complicações.</h2><p class="mx-auto mt-4 max-w-xl text-zinc-400">A arquitetura do Finder está preparada para crescer com templates, domínios, subscrições e integrações futuras.</p><a href="{{ auth()->check() ? route('site.create') : route('register') }}" class="mt-8 inline-flex rounded-xl bg-white px-6 py-3.5 text-sm font-semibold text-zinc-950">Começar agora</a></div></section>

    <footer class="px-5 py-10"><div class="mx-auto flex max-w-7xl flex-col gap-2 text-sm text-zinc-500 sm:flex-row sm:items-center sm:justify-between"><span class="font-semibold text-zinc-900 dark:text-white">FINDER</span><span>Build your website. Make it yours.</span></div></footer>
</div>
