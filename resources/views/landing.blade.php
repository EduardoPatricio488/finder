<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
        <style>
            @keyframes rise-in {
                from { opacity: 0; transform: translateY(24px); }
                to { opacity: 1; transform: translateY(0); }
            }

            @keyframes float-soft {
                0%, 100% { transform: translateY(0) rotate(0deg); }
                50% { transform: translateY(-12px) rotate(2deg); }
            }

            @keyframes drift {
                from { transform: translateX(-8%); }
                to { transform: translateX(8%); }
            }

            .reveal { animation: rise-in 800ms cubic-bezier(.22, 1, .36, 1) both; }
            .reveal-delay-1 { animation-delay: 120ms; }
            .reveal-delay-2 { animation-delay: 240ms; }
            .reveal-delay-3 { animation-delay: 360ms; }
            .float-soft { animation: float-soft 6s ease-in-out infinite; }
            .drift { animation: drift 8s ease-in-out infinite alternate; }

            @media (prefers-reduced-motion: reduce) {
                .reveal, .float-soft, .drift { animation: none; }
            }
        </style>
    </head>
    <body class="min-h-screen bg-[#f7f4ee] text-stone-950">
        @php
            $site = \App\Support\SiteContext::storefront();
            $homepage = $site->homepage ?? [];
            $productsRoute = request()->route('site') ? route('sites.products', $site) : route('products');
        @endphp
        <x-store-shell>
            <x-slot:actions>
                <a href="{{ $productsRoute }}" wire:navigate class="hidden rounded-xl bg-stone-950 px-3 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-400 hover:text-stone-950 sm:inline-flex">Ver produtos</a>
            </x-slot:actions>

            <main class="overflow-hidden">
                <section class="mx-auto grid min-h-[calc(100vh-4.75rem)] max-w-7xl items-center gap-12 px-6 pb-16 pt-10 lg:grid-cols-[1fr_0.9fr] lg:px-12 lg:pb-24 lg:pt-8">
                    <div class="max-w-xl">
                        @auth
                            <div class="reveal mb-6 inline-flex items-center gap-3 rounded-xl border border-amber-200 bg-amber-100/80 px-3 py-2 text-amber-950 shadow-sm">
                                <span class="flex size-9 items-center justify-center rounded-full bg-stone-950 text-xs font-bold text-amber-200">{{ auth()->user()->initials() }}</span>
                                <span><span class="block text-[10px] font-semibold uppercase tracking-[0.16em] text-amber-700">A sua área</span><strong class="block text-sm">Olá, {{ auth()->user()->name }}</strong></span>
                            </div>
                        @endauth
                        <p class="reveal text-sm font-semibold uppercase tracking-[0.2em] text-amber-700">Bem-vindo à {{ $site->name }}</p>
                        <h1 class="reveal reveal-delay-1 mt-5 text-5xl font-semibold leading-[1.05] tracking-tight sm:text-6xl">{{ $homepage['hero_title'] ?? $site->tagline ?? $site->name }}</h1>
                        <p class="reveal reveal-delay-2 mt-6 max-w-lg text-lg leading-8 text-stone-600">{{ $homepage['hero_subtitle'] ?? $site->description }}</p>
                        <div class="reveal reveal-delay-3 mt-9 flex flex-col gap-3 sm:flex-row sm:items-center">
                            <a href="{{ $productsRoute }}" wire:navigate class="rounded-lg bg-stone-950 px-5 py-3 text-center text-sm font-semibold text-white transition hover:-translate-y-0.5 hover:bg-amber-400 hover:text-stone-950">{{ $homepage['primary_button_label'] ?? 'Explorar produtos' }} <span aria-hidden="true">&rarr;</span></a>
                            <a href="#manifesto" class="px-2 text-sm text-stone-500 transition hover:text-stone-950">Conhecer a nossa forma de escolher <span aria-hidden="true">&darr;</span></a>
                        </div>
                    </div>
                    <div class="reveal reveal-delay-2 relative overflow-hidden rounded-3xl bg-stone-900 p-8 shadow-2xl sm:p-12">
                        <div class="float-soft absolute -right-16 -top-16 size-64 rounded-full border-[28px] border-amber-300/20"></div>
                        <div class="drift absolute -bottom-24 -left-20 size-72 rounded-full border border-emerald-200/20"></div>
                        <div class="relative flex min-h-[360px] flex-col justify-between text-white sm:min-h-[440px]">
                            <div class="flex items-center justify-between text-xs font-semibold uppercase tracking-[0.2em] text-amber-300"><span>Desde 2026</span><span>{{ $site->category_label }}</span></div>
                            <div><p class="text-7xl font-semibold tracking-tight text-amber-200 sm:text-8xl">01</p><p class="mt-4 max-w-xs text-2xl font-medium leading-tight">O essencial, escolhido com atenção.</p></div>
                            <div class="flex items-end justify-between gap-6 border-t border-white/20 pt-5 text-sm text-stone-300"><span>Descubra a coleção atual</span><span aria-hidden="true" class="text-2xl text-amber-300">↗</span></div>
                        </div>
                    </div>
                </section>

                <div class="border-y border-stone-900/10 bg-[#e8dfd0]">
                    <div class="mx-auto grid max-w-7xl gap-6 px-6 py-6 text-sm text-stone-700 sm:grid-cols-3 lg:px-12">
                        <div class="flex items-center gap-3"><span class="text-xl text-amber-700">✦</span><span>Seleção cuidada, sem excessos</span></div>
                        <div class="flex items-center gap-3"><span class="text-xl text-amber-700">↗</span><span>Novidades escolhidas todas as semanas</span></div>
                        <div class="flex items-center gap-3"><span class="text-xl text-amber-700">○</span><span>Produtos para acompanhar a sua rotina</span></div>
                    </div>
                </div>

                <section id="manifesto" class="mx-auto max-w-7xl scroll-mt-28 px-6 py-20 lg:px-12 lg:py-28">
                    <div class="grid gap-12 lg:grid-cols-[0.8fr_1.2fr] lg:items-end">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-amber-700">A nossa forma de escolher</p>
                            <h2 class="mt-4 max-w-md text-3xl font-semibold leading-tight tracking-tight sm:text-4xl">Menos coisas. Melhores escolhas.</h2>
                        </div>
                        <p class="max-w-2xl text-lg leading-8 text-stone-600">Acreditamos que uma boa loja começa por prestar atenção. Procuramos peças úteis, bonitas e honestas, capazes de encontrar o seu lugar em diferentes casas e histórias.</p>
                    </div>
                    <div class="mt-14 grid gap-5 md:grid-cols-3">
                        <article class="group rounded-2xl border border-transparent bg-white/40 p-6 pt-5 transition hover:-translate-y-1 hover:border-amber-200 hover:bg-white hover:shadow-lg"><p class="text-4xl font-semibold text-stone-950 transition group-hover:text-amber-700">01</p><h3 class="mt-8 text-lg font-semibold">Útil primeiro</h3><p class="mt-3 text-sm leading-6 text-stone-600">Objetos que fazem sentido no dia a dia e não ficam esquecidos numa gaveta.</p></article>
                        <article class="group rounded-2xl border border-transparent bg-white/40 p-6 pt-5 transition hover:-translate-y-1 hover:border-stone-200 hover:bg-white hover:shadow-lg"><p class="text-4xl font-semibold text-stone-950 transition group-hover:text-stone-500">02</p><h3 class="mt-8 text-lg font-semibold">Bonito sem esforço</h3><p class="mt-3 text-sm leading-6 text-stone-600">Formas e materiais que envelhecem bem e combinam com a sua maneira de viver.</p></article>
                        <article class="group rounded-2xl border border-transparent bg-white/40 p-6 pt-5 transition hover:-translate-y-1 hover:border-emerald-200 hover:bg-white hover:shadow-lg"><p class="text-4xl font-semibold text-stone-950 transition group-hover:text-emerald-700">03</p><h3 class="mt-8 text-lg font-semibold">Escolhido consigo</h3><p class="mt-3 text-sm leading-6 text-stone-600">Uma coleção que cresce devagar, guiada pelo que realmente procura.</p></article>
                    </div>
                </section>

                <section class="mx-auto max-w-7xl px-6 pb-20 lg:px-12 lg:pb-28">
                    <div class="grid overflow-hidden rounded-3xl bg-stone-950 text-white shadow-xl lg:grid-cols-[1fr_0.85fr]">
                        <div class="p-8 sm:p-12 lg:p-16"><p class="text-sm font-semibold uppercase tracking-[0.2em] text-amber-300">A coleção atual</p><h2 class="mt-4 max-w-lg text-3xl font-semibold leading-tight tracking-tight sm:text-4xl">Encontre algo que vai querer usar todos os dias.</h2><p class="mt-5 max-w-md leading-7 text-stone-300">Explore artigos escolhidos para tornar o quotidiano mais especial.</p><a href="{{ $productsRoute }}" wire:navigate class="mt-8 inline-flex rounded-lg bg-amber-300 px-5 py-3 text-sm font-semibold text-stone-950 transition hover:bg-amber-200">Ver a coleção <span aria-hidden="true" class="ml-2">&rarr;</span></a></div>
                        <div class="relative min-h-64 overflow-hidden bg-[#c9d5c0] p-8 text-stone-950 lg:min-h-full"><div class="absolute -bottom-24 -right-12 size-72 rounded-full border-[38px] border-stone-950/10"></div><div class="relative flex h-full flex-col justify-between"><span class="text-xs font-semibold uppercase tracking-[0.2em]">Coleção</span><span class="max-w-xs text-5xl font-semibold leading-none tracking-tight">Para os dias comuns, feitos de coisas boas.</span></div></div>
                    </div>
                </section>

                <footer class="border-t border-stone-900/10 px-6 py-8 lg:px-12"><div class="mx-auto flex max-w-7xl flex-col justify-between gap-4 text-sm text-stone-500 sm:flex-row"><span>{{ $site->name }} &copy; 2026</span><a href="{{ $productsRoute }}" wire:navigate class="font-semibold text-stone-800 hover:text-amber-700">Entrar na loja &rarr;</a></div></footer>
            </main>
        </x-store-shell>
        <livewire:customer-assistant />
        @fluxScripts
    </body>
</html>
