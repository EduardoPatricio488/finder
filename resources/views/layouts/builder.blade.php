<!doctype html>
<html lang="pt-PT" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Finder Editor' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
</head>
<body class="min-h-full bg-zinc-100 text-zinc-950 antialiased dark:bg-zinc-950 dark:text-white">
    {{ $slot }}

    @if(request()->routeIs('builder.edit'))
        <div
            x-data="{ open: false }"
            class="pointer-events-none fixed inset-y-0 right-0 z-[80] flex items-start"
        >
            <button
                type="button"
                @click="open = !open"
                class="pointer-events-auto mt-24 mr-4 flex min-h-[54px] items-center gap-3 rounded-2xl border-2 border-zinc-950 bg-zinc-950 px-5 py-3.5 text-sm font-black text-white shadow-[0_12px_35px_rgba(0,0,0,0.22)] ring-4 ring-white/80 transition duration-200 hover:-translate-y-1 hover:bg-zinc-800 hover:shadow-[0_18px_45px_rgba(0,0,0,0.28)] focus:outline-none focus:ring-4 focus:ring-zinc-300"
                aria-label="Abrir modelos de website"
            >
                <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-white text-base text-zinc-950 shadow-sm">◈</span>
                <span class="flex flex-col items-start leading-none">
                    <span class="text-[9px] font-black uppercase tracking-[0.18em] text-white/50">Design</span>
                    <span class="mt-1">Modelos</span>
                </span>
                <span class="ml-1 rounded-full bg-white/10 px-2 py-1 text-[9px] font-black uppercase tracking-wider text-white/70">Novo</span>
            </button>

            <div
                x-show="open"
                x-cloak
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="translate-x-4 opacity-0"
                x-transition:enter-end="translate-x-0 opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="translate-x-0 opacity-100"
                x-transition:leave-end="translate-x-4 opacity-0"
                class="pointer-events-auto mt-24 mr-4 w-[330px] max-w-[calc(100vw-24px)] rounded-3xl border border-zinc-200 bg-white p-4 shadow-2xl"
            >
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[9px] font-black uppercase tracking-[0.2em] text-zinc-400">Estrutura do website</p>
                        <h2 class="mt-1 text-lg font-black tracking-tight">Escolhe um modelo</h2>
                        <p class="mt-1 text-[11px] leading-5 text-zinc-400">A navegação usa automaticamente os nomes das páginas deste website.</p>
                    </div>
                    <button type="button" @click="open=false" class="h-8 w-8 rounded-xl bg-zinc-100 text-zinc-500">×</button>
                </div>

                <div class="mt-4 grid gap-2">
                    @php($builderModels = [
                        'top' => ['Top navigation', 'Menu no topo', '☰'],
                        'sidebar-left' => ['Sidebar esquerda', 'Menu lateral à esquerda', '◧'],
                        'sidebar-right' => ['Sidebar direita', 'Menu lateral à direita', '◨'],
                        'minimal' => ['Minimalista', 'Navegação discreta', '○'],
                        'business' => ['Empresa', 'Estrutura corporativa', '▦'],
                        'portfolio' => ['Portfólio', 'Foco em trabalhos', '◫'],
                        'shop' => ['Loja', 'Foco em produtos', '▤'],
                    ])

                    @foreach($builderModels as $modelKey => [$modelName, $modelDescription, $modelIcon])
                        <button
                            type="button"
                            wire:click="$set('theme.layout', '{{ $modelKey }}')"
                            @click="open=false"
                            class="group flex w-full items-center gap-3 rounded-2xl border px-3 py-3 text-left transition {{ data_get($theme ?? [], 'layout', 'top') === $modelKey ? 'border-zinc-950 bg-zinc-950 text-white shadow-lg' : 'border-zinc-200 bg-white text-zinc-800 hover:border-zinc-400 hover:bg-zinc-50' }}"
                        >
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ data_get($theme ?? [], 'layout', 'top') === $modelKey ? 'bg-white/10' : 'bg-zinc-100' }} text-lg">{{ $modelIcon }}</span>
                            <span class="min-w-0 flex-1">
                                <span class="block text-xs font-black">{{ $modelName }}</span>
                                <span class="mt-0.5 block text-[10px] {{ data_get($theme ?? [], 'layout', 'top') === $modelKey ? 'text-white/50' : 'text-zinc-400' }}">{{ $modelDescription }}</span>
                            </span>
                            @if(data_get($theme ?? [], 'layout', 'top') === $modelKey)
                                <span class="text-xs font-black">✓</span>
                            @endif
                        </button>
                    @endforeach
                </div>

                <div class="mt-4 rounded-2xl bg-zinc-50 p-3 text-[10px] leading-5 text-zinc-500">
                    <strong class="text-zinc-800">Navegação automática:</strong> Home, Produtos, Sobre, Contacto e qualquer nova página são apresentados pelo nome da página.
                </div>

                <button type="button" wire:click="save" @click="open=false" class="mt-3 w-full rounded-xl bg-zinc-950 px-4 py-3 text-xs font-black text-white transition hover:bg-zinc-800">
                    Aplicar e guardar modelo
                </button>
            </div>
        </div>
    @endif

    @fluxScripts
</body>
</html>
