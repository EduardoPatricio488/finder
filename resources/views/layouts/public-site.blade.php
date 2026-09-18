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
        html { scroll-behavior: smooth; }
        ::selection { background: color-mix(in srgb, var(--finder-primary, #635bff) 20%, white); }
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
                radial-gradient(circle at 92% 4%, color-mix(in srgb, var(--finder-primary, #635bff) 12%, transparent), transparent 28rem),
                radial-gradient(circle at 8% 46%, color-mix(in srgb, var(--finder-primary, #635bff) 6%, transparent), transparent 25rem),
                linear-gradient(180deg, #fff 0%, #fafafa 48%, #fff 100%);
        }
        body.finder-personal-site header {
            border-bottom-color: rgba(24,24,27,.06) !important;
            background: rgba(255,255,255,.72) !important;
            box-shadow: 0 8px 30px rgba(24,24,27,.035);
            backdrop-filter: blur(20px) saturate(150%);
        }
        body.finder-personal-site header a:first-child { font-weight: 800 !important; letter-spacing: -.035em; }
        body.finder-personal-site header nav a { position: relative; font-weight: 550; }
        body.finder-personal-site header nav a::after { content: ''; position: absolute; left: 0; right: 0; bottom: -7px; height: 2px; border-radius: 99px; background: var(--finder-primary, #635bff); transform: scaleX(0); transition: transform .2s ease; }
        body.finder-personal-site header nav a:hover::after { transform: scaleX(1); }
        body.finder-personal-site header > div > a:last-child { border-radius: 999px !important; padding-inline: 1.15rem !important; box-shadow: 0 10px 25px color-mix(in srgb, var(--finder-primary, #635bff) 20%, transparent); }
        body.finder-personal-site > * { width: 100% !important; max-width: none !important; }
        body.finder-personal-site main { display: block !important; width: 100% !important; max-width: none !important; position: static !important; left: auto !important; transform: none !important; margin: 0 !important; padding: 0 !important; text-align: center !important; }
        body.finder-personal-site main > section { text-align: center !important; }
        body.finder-personal-site main > section > div { margin-inline: auto !important; }
        body.finder-personal-site main > section p { margin-inline: auto !important; }
        body.finder-personal-site main > section article { text-align: center !important; }
        body.finder-personal-site main > section form { text-align: left !important; margin-inline: auto !important; }
        body.finder-personal-site main > section:first-child { position: relative; overflow: hidden; width: 100% !important; min-height: 720px; display: flex; align-items: center; justify-content: center !important; text-align: center !important; background: radial-gradient(circle at 78% 38%, color-mix(in srgb, var(--finder-primary, #635bff) 18%, transparent), transparent 20rem), linear-gradient(135deg, #fff 0%, #f7f7fb 58%, #f1f0f8 100%); isolation: isolate; }
        body.finder-personal-site main > section:first-child h1 { width: 100%; max-width: 920px; margin-inline: auto !important; text-align: center !important; font-weight: 850 !important; letter-spacing: -.065em; line-height: .94; text-wrap: balance; }
        body.finder-personal-site main > section:first-child p { width: 100%; max-width: 680px !important; margin-inline: auto !important; text-align: center !important; color: #52525b; font-size: 1.16rem !important; line-height: 1.8 !important; }
        body.finder-personal-site main > section:first-child a { align-self: center; }
        body.finder-personal-site main > section:first-child a, body.finder-personal-site main > section:not(:first-child) a[style*="background"] { border-radius: 9999px !important; padding: .9rem 1.5rem !important; box-shadow: 0 14px 30px color-mix(in srgb, var(--finder-primary, #635bff) 22%, transparent) !important; transition: transform .2s ease, box-shadow .2s ease, filter .2s ease; }
        body.finder-personal-site main > section:first-child a:hover, body.finder-personal-site main > section:not(:first-child) a[style*="background"]:hover { transform: translateY(-3px); filter: brightness(.98); box-shadow: 0 20px 42px color-mix(in srgb, var(--finder-primary, #635bff) 28%, transparent) !important; }
        body.finder-personal-site main > section:not(:first-child) { position: relative; padding-top: 7rem !important; padding-bottom: 7rem !important; }
        body.finder-personal-site main > section:nth-child(2n+1):not(:first-child) { background: rgba(248,248,250,.62); border-block: 1px solid rgba(24,24,27,.045); }
        body.finder-personal-site main > section:not(:first-child) h2 { font-weight: 800 !important; letter-spacing: -.045em; line-height: 1.05; }
        body.finder-personal-site main > section:not(:first-child) article { border-color: rgba(24,24,27,.075) !important; background: rgba(255,255,255,.82); box-shadow: 0 12px 35px rgba(24,24,27,.045) !important; transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease; }
        body.finder-personal-site main > section:not(:first-child) article:hover { transform: translateY(-5px); box-shadow: 0 24px 55px rgba(24,24,27,.09) !important; border-color: color-mix(in srgb, var(--finder-primary, #635bff) 20%, transparent) !important; }
        body.finder-personal-site details { transition: background .2s ease; }
        body.finder-personal-site details:hover { background: rgba(255,255,255,.65); }
        body.finder-personal-site input, body.finder-personal-site textarea { border-radius: 1rem !important; border-color: rgba(24,24,27,.10) !important; background: rgba(255,255,255,.9); box-shadow: 0 5px 18px rgba(24,24,27,.035); transition: border-color .2s ease, box-shadow .2s ease; }
        body.finder-personal-site input:focus, body.finder-personal-site textarea:focus { border-color: color-mix(in srgb, var(--finder-primary, #635bff) 48%, white) !important; box-shadow: 0 0 0 4px color-mix(in srgb, var(--finder-primary, #635bff) 9%, transparent), 0 8px 24px rgba(24,24,27,.05); }
        body.finder-personal-site footer { border-top-color: rgba(24,24,27,.08) !important; background: #fafafa !important; }
        @media (max-width: 640px) { body.finder-personal-site main > section:first-child { min-height: 620px; padding-top: 6.5rem !important; padding-bottom: 6.5rem !important; } body.finder-personal-site main > section:first-child h1 { font-size: 3.15rem !important; } body.finder-personal-site main > section:first-child p { font-size: 1rem !important; line-height: 1.7 !important; } body.finder-personal-site main > section:not(:first-child) { padding-top: 5rem !important; padding-bottom: 5rem !important; } body.finder-personal-site main > section:first-child::before { width: 360px; height: 360px; right: -170px; top: -110px; } }
        @endif
        @media (prefers-reduced-motion: reduce) { html { scroll-behavior: auto; } *, *::before, *::after { transition-duration: .01ms !important; animation-duration: .01ms !important; } }
        @media (max-width: 640px) { body.finder-site-typography h1 { font-size: min({{ $typeScale['h1'] }}, 2.75rem) !important; } }
    </style>
</head>
<body class="finder-site-typography {{ $isPersonal ? 'finder-personal-site' : '' }} min-h-screen bg-white text-zinc-950 antialiased dark:bg-zinc-950 dark:text-white">
    {{ $slot }}
    @fluxScripts
</body>
</html>
