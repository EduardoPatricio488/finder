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
        $isPersonal = ($site->type ?? null) === 'personal';
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

        @if($isPersonal)
        body.finder-personal-site {
            background:
                radial-gradient(circle at 85% 8%, color-mix(in srgb, var(--finder-primary, #635bff) 10%, transparent), transparent 28rem),
                linear-gradient(180deg, #ffffff 0%, #fafafa 52%, #ffffff 100%);
        }
        body.finder-personal-site header {
            border-bottom-color: rgba(24, 24, 27, .07) !important;
            background: rgba(255,255,255,.78) !important;
            box-shadow: 0 8px 30px rgba(24,24,27,.035);
        }
        body.finder-personal-site main > section:first-child {
            position: relative;
            overflow: hidden;
            min-height: 620px;
            display: flex;
            align-items: center;
            background:
                radial-gradient(circle at 75% 30%, color-mix(in srgb, var(--finder-primary, #635bff) 18%, transparent), transparent 22rem),
                linear-gradient(135deg, #ffffff 0%, #f7f7fb 100%);
        }
        body.finder-personal-site main > section:first-child::before {
            content: '';
            position: absolute;
            width: 420px;
            height: 420px;
            right: -150px;
            top: -130px;
            border-radius: 9999px;
            border: 1px solid color-mix(in srgb, var(--finder-primary, #635bff) 18%, transparent);
            box-shadow: 0 0 0 70px color-mix(in srgb, var(--finder-primary, #635bff) 3%, transparent);
        }
        body.finder-personal-site main > section:first-child > div { position: relative; z-index: 1; }
        body.finder-personal-site main > section:first-child h1 {
            max-width: 900px;
            margin-inline: auto;
            font-weight: 800 !important;
            letter-spacing: -.055em;
            line-height: .98;
            text-wrap: balance;
        }
        body.finder-personal-site main > section:first-child p {
            max-width: 680px !important;
            color: #52525b;
            font-size: 1.15rem !important;
        }
        body.finder-personal-site main > section:first-child a {
            border-radius: 9999px !important;
            padding: .9rem 1.45rem !important;
            box-shadow: 0 14px 30px color-mix(in srgb, var(--finder-primary, #635bff) 22%, transparent) !important;
            transition: transform .2s ease, box-shadow .2s ease;
        }
        body.finder-personal-site main > section:first-child a:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 36px color-mix(in srgb, var(--finder-primary, #635bff) 28%, transparent) !important;
        }
        body.finder-personal-site main > section:not(:first-child) { position: relative; }
        body.finder-personal-site main > section:not(:first-child) h2 {
            font-weight: 750 !important;
            letter-spacing: -.035em;
        }
        body.finder-personal-site main > section:not(:first-child) article {
            border-color: rgba(24,24,27,.08) !important;
            background: rgba(255,255,255,.82);
            box-shadow: 0 12px 35px rgba(24,24,27,.045) !important;
            transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
        }
        body.finder-personal-site main > section:not(:first-child) article:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 45px rgba(24,24,27,.08) !important;
            border-color: color-mix(in srgb, var(--finder-primary, #635bff) 18%, transparent) !important;
        }
        body.finder-personal-site input,
        body.finder-personal-site textarea {
            border-radius: 1rem !important;
            background: rgba(255,255,255,.9);
            box-shadow: 0 5px 18px rgba(24,24,27,.035);
        }
        body.finder-personal-site footer {
            border-top-color: rgba(24,24,27,.08) !important;
            background: #fafafa !important;
        }
        @media (max-width: 640px) {
            body.finder-personal-site main > section:first-child {
                min-height: 560px;
                padding-top: 7rem !important;
                padding-bottom: 7rem !important;
            }
            body.finder-personal-site main > section:first-child h1 {
                font-size: 3.1rem !important;
            }
        }
        @endif

        @media (max-width: 640px) {
            body.finder-site-typography h1 { font-size: min({{ $typeScale['h1'] }}, 2.75rem) !important; }
        }
    </style>
</head>
<body class="finder-site-typography {{ $isPersonal ? 'finder-personal-site' : '' }} min-h-screen bg-white text-zinc-950 antialiased dark:bg-zinc-950 dark:text-white">
    {{ $slot }}
    @fluxScripts
</body>
</html>
