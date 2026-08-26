<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-[#f4f1ea] text-stone-950">
        {{ $slot }}
        @fluxScripts
    </body>
</html>
