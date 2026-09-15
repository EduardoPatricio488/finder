<!doctype html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        $seoTitle = $page->seo['title'] ?? $site->seo['title'] ?? $page->name.' — '.$site->name;
        $seoDescription = $page->seo['description'] ?? $site->seo['description'] ?? $site->description ?? '';
        $seoImage = $page->seo['og_image'] ?? $site->seo['og_image'] ?? null;
        $favicon = $site->favicon ?: null;
        $canonical = $page->is_homepage
            ? route('site.public', ['site' => $site->slug])
            : route('site.public', ['site' => $site->slug, 'pageSlug' => $page->slug]);
    @endphp
    <meta name="description" content="{{ $seoDescription }}">
    <meta name="robots" content="{{ $preview ?? false ? 'noindex,nofollow,noarchive' : 'index,follow' }}">
    <link rel="canonical" href="{{ $canonical }}">
    @if($favicon)
        <link rel="icon" href="{{ str_starts_with($favicon, ['http://', 'https://']) ? $favicon : asset($favicon) }}">
    @else
        <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
        <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    @endif
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $seoTitle }}">
    <meta property="og:description" content="{{ $seoDescription }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:site_name" content="{{ $site->name }}">
    @if($seoImage)
        <meta property="og:image" content="{{ str_starts_with($seoImage, ['http://', 'https://']) ? $seoImage : asset($seoImage) }}">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:image" content="{{ str_starts_with($seoImage, ['http://', 'https://']) ? $seoImage : asset($seoImage) }}">
    @else
        <meta name="twitter:card" content="summary">
    @endif
    <meta name="twitter:title" content="{{ $seoTitle }}">
    <meta name="twitter:description" content="{{ $seoDescription }}">
    <title>{{ $seoTitle }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-white text-zinc-950 antialiased dark:bg-zinc-950 dark:text-white">
    {{ $slot }}
    @fluxScripts
</body>
</html>
