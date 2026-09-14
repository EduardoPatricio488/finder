<!doctype html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $site->seo['description'] ?? $site->description ?? '' }}">
    <meta property="og:title" content="{{ $site->seo['title'] ?? $site->name }}">
    <meta property="og:description" content="{{ $site->seo['description'] ?? $site->description ?? '' }}">
    <title>{{ $page->seo['title'] ?? $site->seo['title'] ?? $page->name.' — '.$site->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-white text-zinc-950 antialiased dark:bg-zinc-950 dark:text-white">
    {{ $slot }}
    @fluxScripts
</body>
</html>
