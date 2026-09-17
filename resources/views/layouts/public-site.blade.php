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
    @php
        $fontFamily = match($site->theme['font_family'] ?? 'modern-sans') {
            'classic-serif' => 'Georgia, Times New Roman, serif',
            'editorial' => 'Georgia, Times New Roman, serif',
            'geometric' => 'Trebuchet MS, Arial, sans-serif',
            'clean' => 'Arial, Helvetica, sans-serif',
            'friendly' => 'Verdana, Geneva, sans-serif',
            default => 'Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, Segoe UI, sans-serif',
        };
        $typeScale = match($site->theme['typography_scale'] ?? 'balanced') {
            'compact' => ['h1' => '2.5rem', 'h2' => '1.75rem', 'h3' => '1.25rem', 'body' => '0.95rem'],
            'large' => ['h1' => '3.75rem', 'h2' => '2.5rem', 'h3' => '1.5rem', 'body' => '1.1rem'],
            'display' => ['h1' => '4.5rem', 'h2' => '3rem', 'h3' => '1.75rem', 'body' => '1.15rem'],
            default => ['h1' => '3rem', 'h2' => '2rem', 'h3' => '1.35rem', 'body' => '1rem'],
        };
    @endphp
    <style>
        body.finder-site-typography,
        body.finder-site-typography * { font-family: {{ $fontFamily }}; }
        body.finder-site-typography h1 { font-size: {{ $typeScale['h1'] }} !important; }
        body.finder-site-typography h2 { font-size: {{ $typeScale['h2'] }} !important; }
        body.finder-site-typography h3 { font-size: {{ $typeScale['h3'] }} !important; }
        body.finder-site-typography p,
        body.finder-site-typography li,
        body.finder-site-typography label,
        body.finder-site-typography input,
        body.finder-site-typography textarea,
        body.finder-site-typography button { font-size: {{ $typeScale['body'] }}; }
        @media (max-width: 640px) {
            body.finder-site-typography h1 { font-size: min({{ $typeScale['h1'] }}, 2.75rem) !important; }
        }
    </style>
</head>
<body class="finder-site-typography min-h-screen bg-white text-zinc-950 antialiased dark:bg-zinc-950 dark:text-white">
    {{ $slot }}
    @fluxScripts
</body>
</html>
