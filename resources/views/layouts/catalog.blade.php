<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    @php
        $catalogSite = \App\Support\SiteContext::storefront();
        $catalogColors = ($catalogSite->settings ?? [])['products_page'] ?? [];
        $catalogBackground = $catalogColors['background'] ?? '#f7f4ee';
        $catalogText = $catalogColors['text'] ?? '#1c1917';
        $catalogAccent = $catalogColors['accent'] ?? '#f59e0b';
    @endphp
    <head>
        @include('partials.head')
        <style>
            :root { --catalog-bg: {{ $catalogBackground }}; --catalog-text: {{ $catalogText }}; --catalog-accent: {{ $catalogAccent }}; }
        </style>
    </head>
    <body class="min-h-screen" style="background-color: var(--catalog-bg); color: var(--catalog-text);">
        <x-store-shell>
            @if (request()->routeIs('sales'))
                <div class="mx-auto max-w-7xl px-6 pt-6 text-sm font-semibold text-stone-500 lg:px-10">
                    <span>Coisas bonitas para viver melhor.</span>
                    <a href="{{ route('products') }}" class="ml-4 underline underline-offset-4 hover:text-stone-950">Ver produtos</a>
                    @auth
                        <span class="ml-3">Olá, {{ auth()->user()->name }}</span>
                    @endauth
                </div>
            @endif
            {{ $slot }}
        </x-store-shell>
        <livewire:customer-assistant />
        @fluxScripts
    </body>
</html>
