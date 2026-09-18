@php
    $brief = data_get($site->settings, 'builder.brief', []);
    $description = trim((string) ($brief['description'] ?? $site->tagline ?? ''));
    $description = $description !== '' ? $description : 'Uma apresentação pessoal simples, clara e profissional.';
    $audience = trim((string) ($brief['audience'] ?? ''));
    $additionalInfo = trim((string) ($brief['additional_info'] ?? ''));
    $goal = (string) ($brief['goal'] ?? 'contact');
    $categoryLabel = trim((string) ($site->category_label ?? ''));
    $style = trim((string) data_get($site->settings, 'builder.template_style', ''));

    $goalLabel = match ($goal) {
        'buy' => 'Comprar agora',
        'quote' => 'Pedir orçamento',
        'booking' => 'Marcar uma reunião',
        'call' => 'Ligar agora',
        'message' => 'Enviar mensagem',
        default => 'Entrar em contacto',
    };
    $availabilityNote = match ($goal) {
        'buy' => 'Disponível para novas encomendas e parcerias.',
        'quote' => 'Respondo a pedidos de orçamento em 24 a 48 horas.',
        'booking' => 'Consulta a minha disponibilidade e marca a tua reunião.',
        'call' => 'Prefiro uma chamada rápida? Deixa o teu contacto que ligo-te de volta.',
        'message' => 'Respondo a todas as mensagens pessoalmente.',
        default => 'Disponível para novos projectos e colaborações.',
    };

    $currentPage = $page->slug;
    $accent = $site->theme['primary'] ?? $site->primary_color ?? '#635bff';
    $initial = mb_strtoupper(mb_substr($site->name, 0, 1));
@endphp

<div class="min-h-screen bg-[#f8f8f6] text-zinc-950 antialiased" style="--finder-primary: {{ $accent }}">
    <header class="sticky top-0 z-40 border-b border-black/5 bg-[#f8f8f6]/90 backdrop-blur-xl">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-5 py-4 sm:px-8">
            <a href="{{ route('site.public', $site) }}" class="flex items-center gap-3 font-semibold tracking-tight">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl text-sm font-bold text-white shadow-sm" style="background: var(--finder-primary)">{{ $initial }}</span>
                <span class="max-w-[190px] truncate sm:max-w-none">{{ $site->name }}</span>
            </a>
            <nav class="hidden items-center gap-1 md:flex" aria-label="Navegação principal">
                @foreach($site->pages->sortBy('sort_order') as $sitePage)
                    <a href="{{ route('site.public', [$site, 'pageSlug' => $sitePage->slug, 'preview' => $preview ? 1 : null]) }}" class="rounded-full px-4 py-2 text-sm font-medium transition {{ $currentPage === $sitePage->slug ? 'bg-zinc-950 text-white' : 'text-zinc-600 hover:bg-white hover:text-zinc-950' }}">
                        {{ match($sitePage->slug) { 'home' => 'Início', 'about' => 'Sobre mim', 'contact' => 'Contactos', default => $sitePage->name } }}
                    </a>
                @endforeach
            </nav>
            <a href="{{ route('site.public', [$site, 'pageSlug' => 'contact', 'preview' => $preview ? 1 : null]) }}" class="hidden rounded-full px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 sm:inline-flex" style="background: var(--finder-primary)">{{ $goalLabel }}</a>
        </div>
        <div class="overflow-x-auto border-t border-black/5 md:hidden">
            <nav class="mx-auto flex max-w-6xl min-w-max gap-1 px-5 py-2" aria-label="Navegação mobile">
                @foreach($site->pages->sortBy('sort_order') as $sitePage)
                    <a href="{{ route('site.public', [$site, 'pageSlug' => $sitePage->slug, 'preview' => $preview ? 1 : null]) }}" class="rounded-full px-3 py-1.5 text-xs font-medium {{ $currentPage === $sitePage->slug ? 'bg-zinc-950 text-white' : 'text-zinc-500' }}">
                        {{ match($sitePage->slug) { 'home' => 'Início', 'about' => 'Sobre mim', 'contact' => 'Contactos', default => $sitePage->name } }}
                    </a>
                @endforeach
            </nav>
        </div>
    </header>

    @if($preview)
        <div class="border-b border-amber-200 bg-amber-50 px-5 py-2.5 text-center text-xs font-medium text-amber-800">Pré-visualização privada — este conteúdo ainda não está público.</div>
    @endif

    @if(session('site_form_success'))
        <div class="mx-auto max-w-3xl px-5 pt-6" role="status">
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('site_form_success') }}</div>
        </div>
    @endif

    @if($currentPage === 'home')
        <main>
            <section class="relative isolate overflow-hidden px-5 pb-20 pt-16 sm:px-8 sm:pb-28 sm:pt-24">
                <div class="pointer-events-none absolute -right-32 -top-32 -z-10 h-96 w-96 rounded-full opacity-20 blur-3xl" style="background: var(--finder-primary)"></div>
                <div class="pointer-events-none absolute -bottom-40 -left-32 -z-10 h-80 w-80 rounded-full bg-zinc-300/50 blur-3xl"></div>
                <div class="mx-auto flex max-w-3xl flex-col items-center">
                    <div class="mb-7 inline-flex items-center gap-2 rounded-full border border-black/5 bg-white/70 px-3.5 py-2 text-xs font-semibold text-zinc-600 shadow-sm backdrop-blur">
                        <span class="h-2 w-2 rounded-full" style="background: var(--finder-primary)"></span>
                        {{ $categoryLabel !== '' ? $categoryLabel : 'Website pessoal' }}
                    </div>
                    <h1 class="tracking-[-0.045em]">{{ $site->name }}</h1>
                    <p class="mt-7 max-w-2xl text-zinc-600">{{ $description }}</p>
                    @if($audience !== '')
                        <p class="mt-3 max-w-2xl text-sm font-medium text-zinc-500">Trabalho principalmente com <span class="font-semibold text-zinc-800">{{ $audience }}</span>.</p>
                    @endif
                    <div class="mt-9 flex flex-wrap items-center justify-center gap-3">
                        <a href="{{ route('site.public', [$site, 'pageSlug' => 'about', 'preview' => $preview ? 1 : null]) }}" class="inline-flex items-center gap-2 bg-zinc-950 px-6 py-3.5 text-sm font-semibold text-white shadow-lg shadow-zinc-900/10 transition hover:-translate-y-0.5">Conhecer-me <span aria-hidden="true">→</span></a>
                        <a href="{{ route('site.public', [$site, 'pageSlug' => 'contact', 'preview' => $preview ? 1 : null]) }}" class="inline-flex border border-black/10 bg-white px-6 py-3.5 text-sm font-semibold text-zinc-800 transition hover:-translate-y-0.5 hover:border-black/20">{{ $goalLabel }}</a>
                    </div>
                    <div class="relative mt-14 w-full max-w-md">
                        <div class="absolute inset-8 rounded-[2.5rem] opacity-20 blur-2xl" style="background: var(--finder-primary)"></div>
                        <div class="relative overflow-hidden rounded-[2rem] border border-white/80 bg-white/80 p-3 shadow-2xl shadow-zinc-900/10 backdrop-blur">
                            <div class="rounded-[1.5rem] bg-zinc-950 p-7 text-white sm:p-9">
                                <div class="flex items-center justify-between"><span class="text-xs font-medium uppercase tracking-[0.18em] text-white/45">Olá</span><span class="h-2.5 w-2.5 rounded-full" style="background: var(--finder-primary)"></span></div>
                                <div class="mt-20">
                                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl text-xl font-bold text-white" style="background: var(--finder-primary)">{{ $initial }}</div>
                                    <p class="mt-6 text-2xl font-semibold tracking-tight">{{ $site->name }}</p>
                                    <p class="mt-3 text-sm leading-6 text-white/55">{{ Str::limit($description, 130) }}</p>
                                </div>
                                <div class="mt-12 flex items-center justify-between border-t border-white/10 pt-5 text-xs text-white/45"><span>{{ $categoryLabel !== '' ? $categoryLabel : 'Personal' }}</span><span>PT-PT</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <section class="border-y border-black/5 bg-white px-5 py-10 sm:px-8">
                <div class="mx-auto grid max-w-6xl gap-6 sm:grid-cols-3">
                    <div class="flex items-start gap-3"><span class="mt-1 h-2 w-2 shrink-0 rounded-full" style="background: var(--finder-primary)"></span><div><p class="text-xs font-bold uppercase tracking-wider text-zinc-400">Área</p><p class="mt-1 font-semibold">{{ $categoryLabel !== '' ? $categoryLabel : 'Trabalho independente' }}</p></div></div>
                    <div class="flex items-start gap-3"><span class="mt-1 h-2 w-2 shrink-0 rounded-full" style="background: var(--finder-primary)"></span><div><p class="text-xs font-bold uppercase tracking-wider text-zinc-400">Para quem</p><p class="mt-1 font-semibold">{{ $audience !== '' ? $audience : 'Todos os que procuram um serviço de confiança' }}</p></div></div>
                    <div class="flex items-start gap-3"><span class="mt-1 h-2 w-2 shrink-0 rounded-full" style="background: var(--finder-primary)"></span><div><p class="text-xs font-bold uppercase tracking-wider text-zinc-400">Disponibilidade</p><p class="mt-1 font-semibold">{{ $availabilityNote }}</p></div></div>
                </div>
            </section>
            <section class="bg-white px-5 py-16 sm:px-8 sm:py-20">
                <div class="mx-auto grid max-w-6xl gap-10 lg:grid-cols-[.75fr_1.25fr] lg:gap-20">
                    <div><p class="text-xs font-bold uppercase tracking-[0.18em]" style="color: var(--finder-primary)">Sobre mim</p><h2 class="mt-3 text-3xl font-semibold tracking-tight sm:text-4xl">Uma apresentação simples, sem ruído.</h2></div>
                    <div><p class="text-lg leading-8 text-zinc-600">{{ $description }}</p>@if($additionalInfo !== '')<p class="mt-5 rounded-2xl border-l-4 bg-zinc-50 py-4 pl-5 pr-4 text-base leading-7 text-zinc-600" style="border-color: var(--finder-primary)">{{ $additionalInfo }}</p>@endif<a href="{{ route('site.public', [$site, 'pageSlug' => 'about', 'preview' => $preview ? 1 : null]) }}" class="mt-7 inline-flex items-center gap-2 text-sm font-semibold" style="color: var(--finder-primary)">Saber mais <span>→</span></a></div>
                </div>
            </section>
            <section class="px-5 py-16 sm:px-8 sm:py-20">
                <div class="mx-auto max-w-6xl">
                    <p class="text-xs font-bold uppercase tracking-[0.18em]" style="color: var(--finder-primary)">Como posso ajudar</p><h2 class="mt-3 max-w-2xl text-3xl font-semibold tracking-tight sm:text-4xl">O que podes esperar de trabalhar comigo.</h2>
                    <div class="mt-10 grid gap-5 sm:grid-cols-3">
                        <div class="rounded-3xl border border-black/5 bg-white p-7 shadow-sm"><span class="flex h-10 w-10 items-center justify-center rounded-xl text-sm font-bold text-white" style="background: var(--finder-primary)">01</span><p class="mt-5 font-semibold">O que faço</p><p class="mt-2 text-sm leading-6 text-zinc-500">{{ Str::limit($description, 160) }}</p></div>
                        <div class="rounded-3xl border border-black/5 bg-white p-7 shadow-sm"><span class="flex h-10 w-10 items-center justify-center rounded-xl text-sm font-bold text-white" style="background: var(--finder-primary)">02</span><p class="mt-5 font-semibold">Para quem trabalho</p><p class="mt-2 text-sm leading-6 text-zinc-500">{{ $audience !== '' ? "Trabalho principalmente com {$audience}, adaptando sempre a abordagem a cada caso." : 'Trabalho com qualquer pessoa ou empresa que procure um serviço próximo e de confiança.' }}</p></div>
                        <div class="rounded-3xl border border-black/5 bg-white p-7 shadow-sm"><span class="flex h-10 w-10 items-center justify-center rounded-xl text-sm font-bold text-white" style="background: var(--finder-primary)">03</span><p class="mt-5 font-semibold">Como trabalho</p><p class="mt-2 text-sm leading-6 text-zinc-500">{{ $additionalInfo !== '' ? Str::limit($additionalInfo, 160) : 'Comunicação clara, prazos cumpridos e atenção ao detalhe em cada etapa do processo.' }}</p></div>
                    </div>
                </div>
            </section>
            <section class="bg-white px-5 py-16 sm:px-8 sm:py-20"><div class="mx-auto max-w-4xl text-center"><p class="text-xs font-bold uppercase tracking-[0.18em]" style="color: var(--finder-primary)">O que dizem</p><blockquote class="mt-5 text-2xl font-medium leading-tight tracking-tight text-zinc-800 sm:text-3xl">"Simples, directo e sempre disponível para esclarecer dúvidas. Recomendo sem hesitar."</blockquote><p class="mt-5 text-sm font-semibold text-zinc-500">Cliente satisfeito</p></div></section>
            <section class="px-5 py-16 sm:px-8 sm:py-24"><div class="mx-auto max-w-6xl rounded-[2rem] bg-zinc-950 px-7 py-12 text-white sm:px-12 sm:py-16"><div class="flex flex-col gap-8 sm:flex-row sm:items-end sm:justify-between"><div class="max-w-2xl"><p class="text-xs font-bold uppercase tracking-[0.18em] text-white/40">Vamos conversar</p><h2 class="mt-3 text-3xl font-semibold tracking-tight sm:text-5xl">Tens uma pergunta ou queres saber mais?</h2><p class="mt-4 text-white/60">{{ $availabilityNote }}</p></div><a href="{{ route('site.public', [$site, 'pageSlug' => 'contact', 'preview' => $preview ? 1 : null]) }}" class="inline-flex shrink-0 rounded-full px-6 py-3.5 text-sm font-semibold text-white transition hover:-translate-y-0.5" style="background: var(--finder-primary)">{{ $goalLabel }} →</a></div></div></section>
        </main>
    @elseif($currentPage === 'about')
        <main>
            <section class="px-5 pb-16 pt-20 sm:px-8 sm:pb-24 sm:pt-28"><div class="mx-auto max-w-5xl"><p class="text-xs font-bold uppercase tracking-[0.18em]" style="color: var(--finder-primary)">{{ $categoryLabel !== '' ? $categoryLabel : 'Sobre mim' }}</p><h1 class="mt-4 max-w-4xl text-5xl font-semibold tracking-[-0.04em] sm:text-7xl">{{ $site->name }}</h1><p class="mt-8 max-w-3xl text-xl leading-9 text-zinc-600">{{ $description }}</p></div></section>
            <section class="bg-white px-5 py-16 sm:px-8 sm:py-24"><div class="mx-auto grid max-w-6xl gap-10 lg:grid-cols-[.65fr_1.35fr] lg:gap-24"><div><span class="text-sm font-semibold text-zinc-400">01</span><h2 class="mt-2 text-2xl font-semibold">Quem sou</h2></div><div class="space-y-6 text-lg leading-9 text-zinc-600"><p>{{ $description }}</p><p>{{ $additionalInfo !== '' ? $additionalInfo : 'Este espaço foi criado para apresentar o essencial de forma clara e profissional. À medida que o conteúdo for completado, pode receber mais detalhes sobre experiência, interesses, trabalho ou projectos.' }}</p></div></div></section>
            @if($audience !== '')<section class="px-5 py-16 sm:px-8 sm:py-20"><div class="mx-auto grid max-w-6xl gap-10 lg:grid-cols-[.65fr_1.35fr] lg:gap-24"><div><span class="text-sm font-semibold text-zinc-400">02</span><h2 class="mt-2 text-2xl font-semibold">Para quem trabalho</h2></div><div class="text-lg leading-9 text-zinc-600"><p>Trabalho sobretudo com <span class="font-semibold text-zinc-900">{{ $audience }}</span>, adaptando sempre a forma de trabalhar às necessidades específicas de cada pessoa ou projecto.</p></div></div></section>@endif
            <section class="bg-white px-5 py-16 sm:px-8 sm:py-24"><div class="mx-auto max-w-6xl rounded-[2rem] border border-black/5 bg-[#f8f8f6] p-8 shadow-sm sm:p-12"><div class="grid gap-8 sm:grid-cols-4"><div><p class="text-sm text-zinc-400">01</p><p class="mt-2 font-semibold">Clareza</p><p class="mt-2 text-sm leading-6 text-zinc-500">Informação directa e fácil de compreender.</p></div><div><p class="text-sm text-zinc-400">02</p><p class="mt-2 font-semibold">Simplicidade</p><p class="mt-2 text-sm leading-6 text-zinc-500">Um espaço limpo, focado no que importa.</p></div><div><p class="text-sm text-zinc-400">03</p><p class="mt-2 font-semibold">Presença</p><p class="mt-2 text-sm leading-6 text-zinc-500">Uma apresentação profissional na web.</p></div><div><p class="text-sm text-zinc-400">04</p><p class="mt-2 font-semibold">{{ $style !== '' ? $style : 'Consistência' }}</p><p class="mt-2 text-sm leading-6 text-zinc-500">Um estilo próprio, mantido em cada detalhe do trabalho.</p></div></div></div></section>
            <section class="px-5 py-16 sm:px-8 sm:py-20"><div class="mx-auto max-w-6xl rounded-[2rem] bg-zinc-950 px-7 py-12 text-white text-center sm:px-12 sm:py-16"><h2 class="text-3xl font-semibold tracking-tight sm:text-4xl">{{ $availabilityNote }}</h2><a href="{{ route('site.public', [$site, 'pageSlug' => 'contact', 'preview' => $preview ? 1 : null]) }}" class="mt-7 inline-flex rounded-full px-6 py-3.5 text-sm font-semibold text-white transition hover:-translate-y-0.5" style="background: var(--finder-primary)">{{ $goalLabel }} →</a></div></section>
        </main>
    @else
        <main><section id="formulario" class="px-5 pb-20 pt-20 sm:px-8 sm:pb-28 sm:pt-28"><div class="mx-auto grid max-w-6xl gap-12 lg:grid-cols-[.8fr_1.2fr] lg:gap-24"><div><p class="text-xs font-bold uppercase tracking-[0.18em]" style="color: var(--finder-primary)">Contactos</p><h1 class="mt-4 text-5xl font-semibold tracking-[-0.04em] sm:text-6xl">Vamos falar.</h1><p class="mt-6 max-w-md text-lg leading-8 text-zinc-600">Se quiseres saber mais, envia uma mensagem através do formulário. Não é necessário partilhar informação pessoal nesta página.</p><div class="mt-10 space-y-4"><div class="flex items-start gap-3 rounded-2xl border border-black/5 bg-white p-4"><span class="mt-0.5 h-2 w-2 shrink-0 rounded-full" style="background: var(--finder-primary)"></span><div><p class="text-sm font-semibold">Disponibilidade</p><p class="mt-0.5 text-sm text-zinc-500">{{ $availabilityNote }}</p></div></div>@if($audience !== '')<div class="flex items-start gap-3 rounded-2xl border border-black/5 bg-white p-4"><span class="mt-0.5 h-2 w-2 shrink-0 rounded-full" style="background: var(--finder-primary)"></span><div><p class="text-sm font-semibold">Trabalho com</p><p class="mt-0.5 text-sm text-zinc-500">{{ $audience }}</p></div></div>@endif</div></div><div class="rounded-[2rem] border border-black/5 bg-white p-6 shadow-xl shadow-zinc-900/5 sm:p-9"><form wire:submit="submitContact" class="grid gap-5"><div><label for="personal-contact-name" class="mb-2 block text-sm font-semibold">Nome</label><input id="personal-contact-name" wire:model="contactName" type="text" autocomplete="name" placeholder="O teu nome" class="w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3.5 outline-none transition focus:border-zinc-400 focus:bg-white"></div><div><label for="personal-contact-email" class="mb-2 block text-sm font-semibold">Email</label><input id="personal-contact-email" wire:model="contactEmail" type="email" autocomplete="email" placeholder="o-teu@email.com" class="w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3.5 outline-none transition focus:border-zinc-400 focus:bg-white"></div><div><label for="personal-contact-message" class="mb-2 block text-sm font-semibold">Mensagem</label><textarea id="personal-contact-message" wire:model="contactMessage" rows="6" placeholder="Como posso ajudar?" class="w-full resize-none rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3.5 outline-none transition focus:border-zinc-400 focus:bg-white"></textarea></div><button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl px-5 py-3.5 text-sm font-semibold text-white transition hover:-translate-y-0.5" style="background: var(--finder-primary)">{{ $goalLabel }}</button></form></div></div></section></main>
    @endif

    <footer class="border-t border-black/5 bg-white px-5 py-8 sm:px-8">
        <div class="mx-auto flex max-w-6xl flex-col gap-4 text-sm text-zinc-500 sm:flex-row sm:items-center sm:justify-between">
            <p>© {{ now()->year }} {{ $site->name }}</p>
            <p>Website criado com <span class="font-semibold text-zinc-700">Finder</span></p>
        </div>
    </footer>
</div>
