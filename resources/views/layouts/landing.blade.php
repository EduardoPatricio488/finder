<!doctype html>
<html lang="pt-PT" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Finder' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white text-zinc-950 antialiased dark:bg-zinc-950 dark:text-white">
    {{ $slot }}
    @fluxScripts
</body>
</html>
