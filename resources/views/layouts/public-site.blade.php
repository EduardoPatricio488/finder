<!doctype html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        $seoTitle = $page->seo['title'] ?? $site->seo['title'] ?? $page->name.' — '.$site->name;
        $seoDescription = $page->seo['description'] ?? $site->seo['description'] ?? $site->description ?? '';
        $seoImage = $page->seo['og_image'] ?? $site->seo['og_image'] ?? null;
        $favicon = $site->favicon ?: ($site->settings['favicon_url'] ?? null);
        $defaultCanonical = $page->is_homepage
            ? route('site.public', ['site' => $site->slug])
            : route('site.public', ['site' => $site->slug, 'pageSlug' => $page->slug]);
        $canonical = $page->seo['canonical'] ?? $defaultCanonical;
        $canonical = is_string($canonical) && preg_match('#^https?://#i', $canonical) ? $canonical : $defaultCanonical;
        $faviconUrl = $favicon && preg_match('#^https?://#i', $favicon) ? $favicon : ($favicon ? asset($favicon) : asset('favicon.svg'));
        $seoImageUrl = $seoImage && preg_match('#^https?://#i', $seoImage) ? $seoImage : ($seoImage ? asset($seoImage) : null);
        $theme = is_array($site->theme) ? $site->theme : [];
        $primary = is_string($theme['primary'] ?? null) && preg_match('/^#[0-9a-f]{6}$/i', $theme['primary']) ? $theme['primary'] : '#635bff';
        $secondary = is_string($theme['secondary'] ?? null) && preg_match('/^#[0-9a-f]{6}$/i', $theme['secondary']) ? $theme['secondary'] : '#111827';
        $background = is_string($theme['background'] ?? null) && preg_match('/^#[0-9a-f]{6}$/i', $theme['background']) ? $theme['background'] : '#ffffff';
        $text = is_string($theme['text'] ?? null) && preg_match('/^#[0-9a-f]{6}$/i', $theme['text']) ? $theme['text'] : '#111827';
        $font = in_array($theme['font_body'] ?? null, ['Inter', 'Manrope', 'DM Sans', 'Plus Jakarta Sans'], true) ? $theme['font_body'] : 'Inter';
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $site->name,
            'url' => $canonical,
            'description' => $seoDescription,
        ];
        if ($seoImageUrl) {
            $schema['image'] = $seoImageUrl;
        }
    @endphp
    <meta name="description" content="{{ $seoDescription }}">
    <meta name="robots" content="{{ $preview ?? false ? 'noindex,nofollow,noarchive' : 'index,follow' }}">
    <link rel="canonical" href="{{ $canonical }}">
    <link rel="icon" href="{{ $faviconUrl }}">
    @if(!$favicon)
        <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    @endif
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $seoTitle }}">
    <meta property="og:description" content="{{ $seoDescription }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:site_name" content="{{ $site->name }}">
    @if($seoImageUrl)
        <meta property="og:image" content="{{ $seoImageUrl }}">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:image" content="{{ $seoImageUrl }}">
    @else
        <meta name="twitter:card" content="summary">
    @endif
    <meta name="twitter:title" content="{{ $seoTitle }}">
    <meta name="twitter:description" content="{{ $seoDescription }}">
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    <title>{{ $seoTitle }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-white text-zinc-950 antialiased dark:bg-zinc-950 dark:text-white" style="--finder-primary: {{ $primary }}; --finder-secondary: {{ $secondary }}; --finder-background: {{ $background }}; --finder-text: {{ $text }}; --finder-font: '{{ $font }}', ui-sans-serif, system-ui, sans-serif;">
    {{ $slot }}
    @fluxScripts
</body>
</html>
