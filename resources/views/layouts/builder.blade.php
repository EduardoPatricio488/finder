<!doctype html>
<html lang="pt-PT" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Finder Editor' }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
    <style>
        /* O Builder tem um shell de altura fixa; o canvas precisa de uma área de scroll própria. */
        html:has(body.builder-layout),
        body.builder-layout {
            height: 100%;
            min-height: 0;
            overflow: hidden;
        }

        body.builder-layout > div:first-child {
            height: 100dvh;
            min-height: 0;
        }

        body.builder-layout main,
        body.builder-layout main > div,
        body.builder-layout main > div > div:last-child {
            min-height: 0;
        }

        body.builder-layout main > div > div:last-child {
            overflow-y: auto !important;
            overflow-x: hidden;
            overscroll-behavior: contain;
            -webkit-overflow-scrolling: touch;
        }

        body.builder-layout aside {
            scrollbar-width: thin;
            overscroll-behavior: contain;
        }
    </style>
</head>
<body class="builder-layout h-full overflow-hidden bg-zinc-100 text-zinc-950 antialiased dark:bg-zinc-950 dark:text-white">
    {{ $slot }}

    <div
        x-data="{ saving: false, status: 'Guardado' }"
        x-on:builder-autosave.window="saving = true; status = 'A guardar…'"
        x-on:builder-autosave-finished.window="saving = false; status = 'Guardado agora'"
        x-on:builder-autosave-error.window="saving = false; status = 'Erro ao guardar'"
        class="fixed bottom-5 right-5 z-[100] flex items-center gap-2 rounded-2xl border border-zinc-200 bg-white/95 p-2 shadow-2xl backdrop-blur dark:border-zinc-800 dark:bg-zinc-900/95"
    >
        <span class="px-2 text-xs font-semibold text-zinc-500 dark:text-zinc-400" x-text="status"></span>
        <button
            type="button"
            class="rounded-xl bg-zinc-900 px-4 py-2 text-xs font-bold text-white transition hover:bg-zinc-700 disabled:cursor-not-allowed disabled:opacity-60 dark:bg-white dark:text-zinc-900"
            x-bind:disabled="saving"
            x-on:click="saving = true; status = 'A guardar…'; const component = window.Livewire?.all?.()[0]; component?.$call('save').then(() => { saving = false; status = 'Guardado agora'; }).catch(() => { saving = false; status = 'Erro ao guardar'; })"
        >
            <span x-show="!saving">Guardar</span>
            <span x-show="saving">A guardar…</span>
        </button>
    </div>

    @fluxScripts
</body>
</html>
