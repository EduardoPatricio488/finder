<!doctype html>
<html lang="pt-PT" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Finder — cria, personaliza, gere e publica websites profissionais a partir de uma única plataforma.">
    <meta name="robots" content="index,follow">
    <meta name="theme-color" content="#111827">
    <link rel="canonical" href="{{ url('/') }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $title ?? 'Finder — Cria. Personaliza. Publica.' }}">
    <meta property="og:description" content="Cria, personaliza, gere e publica websites profissionais a partir de uma única plataforma.">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:site_name" content="Finder">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ $title ?? 'Finder — Cria. Personaliza. Publica.' }}">
    <meta name="twitter:description" content="Uma plataforma moderna para criar e gerir websites profissionais.">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="shortcut icon" href="{{ asset('favicon.svg') }}">
    <title>{{ $title ?? 'Finder — Cria. Personaliza. Publica.' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white text-zinc-950 antialiased dark:bg-zinc-950 dark:text-white">
    {{ $slot }}
    @fluxScripts
</body>
</html>
