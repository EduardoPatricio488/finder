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
            {{ $slot }}
        </x-store-shell>
        <livewire:customer-assistant />
        @fluxScripts
    </body>
</html>
