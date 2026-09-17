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
        'resources/css/builder-surface.css',
        'resources/js/app.js',
        'resources/js/builder-studio.js',
        'resources/js/builder-interactions.js',
        'resources/js/builder-surface.js',
    ])
    @fluxAppearance
</head>
<body class="min-h-full bg-zinc-100 text-zinc-950 antialiased selection:bg-zinc-950 selection:text-white dark:bg-zinc-950 dark:text-white">
    {{ $slot }}
    @fluxScripts
</body>
</html>
