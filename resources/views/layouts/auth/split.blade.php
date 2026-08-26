<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-[#f4f1ea] text-stone-950 antialiased">
        <div class="grid min-h-svh lg:grid-cols-2">
            <aside class="relative hidden overflow-hidden bg-stone-950 px-12 py-12 text-white lg:flex lg:flex-col">
                <div class="pointer-events-none absolute -right-16 top-24 size-72 rounded-full bg-amber-400/20 blur-3xl"></div>
                <div class="pointer-events-none absolute bottom-10 left-10 size-56 rounded-full bg-amber-200/10 blur-3xl"></div>

                <x-finder-mark inverted />

                <div class="relative z-10 mt-auto max-w-md">
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-amber-300">Página principal</p>
                    <h1 class="mt-4 text-4xl font-semibold tracking-tight">Os seus sites, num só lugar.</h1>
                    <p class="mt-5 text-base leading-7 text-white/70">
                        Entre para gerir a coleção Finder. A Casa &amp; Co. e os restantes projetos ficam todos aqui.
                    </p>
                </div>
            </aside>

            <div class="flex flex-col">
                <header class="flex h-[4.75rem] items-center justify-between gap-3 border-b border-stone-900/10 px-6 lg:px-12">
                    <x-finder-mark class="lg:hidden" />
                    <a
                        href="{{ route('home') }}"
                        wire:navigate
                        class="ms-auto text-sm font-semibold text-stone-600 transition hover:text-stone-950"
                    >
                        Voltar aos sites
                    </a>
                </header>

                <main class="flex flex-1 items-center justify-center px-6 py-10">
                    <div class="w-full max-w-md rounded-3xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8">
                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
