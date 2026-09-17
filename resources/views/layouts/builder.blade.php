<!doctype html>
<html lang="pt-PT" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#18181b">
    <meta name="color-scheme" content="light">
    <link rel="icon" href="/site-icon.svg" type="image/svg+xml">
    <title>{{ $title ?? 'Finder Website Studio' }}</title>
    @vite([
        'resources/css/app.css',
        'resources/css/builder-canva-pro.css',
        'resources/css/builder-canva-ultra.css',
        'resources/css/builder-canva-ultimate.css',
        'resources/css/builder-layers-enhancement.css',
        'resources/js/app.js',
        'resources/js/builder-studio.js',
        'resources/js/builder-interactions.js',
        'resources/js/builder-canva.js',
        'resources/js/builder-canva-runtime.js',
        'resources/js/builder-canva-pro.js',
        'resources/js/builder-elements-library.js',
        'resources/js/builder-canva-ultra-safe.js',
        'resources/js/builder-canva-ultimate.js',
        'resources/js/builder-layers-enhancement.js',
    ])
    @fluxAppearance
</head>
<body class="min-h-full bg-zinc-100 text-zinc-950 antialiased selection:bg-zinc-950 selection:text-white dark:bg-zinc-950 dark:text-white">
    {{ $slot }}
    @fluxScripts
</body>
</html>
