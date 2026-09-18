<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-[#f7f4ee] text-stone-950">
        <x-store-shell>
            {{ $slot }}
        </x-store-shell>
@fluxScripts
    </body>
</html>
