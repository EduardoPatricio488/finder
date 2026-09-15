<!doctype html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $site->seo['description'] ?? '' }}">
    <meta name="robots" content="{{ $preview ?? false ? 'noindex,nofollow' : 'index,follow' }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $page->seo['title'] ?? $site->seo['title'] ?? $site->name }}">
    <meta property="og:description" content="{{ $site->seo['description'] ?? '' }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="{{ $site->name }}">
    <title>{{ $page->seo['title'] ?? $site->seo['title'] ?? $page->name.' — '.$site->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-white text-zinc-950 antialiased dark:bg-zinc-950 dark:text-white">
    {{ $slot }}
    @fluxScripts
</body>
</html>
