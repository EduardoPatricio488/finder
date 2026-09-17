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
        @php($builderSite = request()->route('site'))
        @php($builderTheme = $builderSite?->theme ?? [])
        @php($builderPages = $builderSite?->pages?->sortBy('sort_order') ?? collect())
        @php($builderLayout = data_get($builderTheme, 'layout', 'top'))

        @if(in_array($builderLayout, ['sidebar-left', 'sidebar-right'], true) && $builderPages->count())
            <div class="pointer-events-auto fixed top-[150px] z-[55] hidden w-52 overflow-hidden rounded-2xl border border-zinc-200 bg-white/95 p-3 shadow-2xl backdrop-blur-xl lg:block {{ $builderLayout === 'sidebar-right' ? 'right-[calc(50%-590px)]' : 'left-[calc(50%-590px)]' }}">
                <div class="mb-3 flex items-center gap-2 border-b border-zinc-100 pb-3">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-zinc-950 text-xs font-black text-white">{{ mb_strtoupper(mb_substr($builderSite->name ?? 'F', 0, 1)) }}</span>
                    <span class="min-w-0 truncate text-xs font-black">{{ $builderSite->name ?? 'Website' }}</span>
                </div>
                <nav class="space-y-1">
                    @foreach($builderPages as $builderPage)
                        <div class="flex items-center gap-2 rounded-xl px-2.5 py-2 text-xs font-bold {{ request()->route('pageSlug') === $builderPage->slug ? 'bg-zinc-950 text-white' : 'text-zinc-600' }}">
                            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md {{ request()->route('pageSlug') === $builderPage->slug ? 'bg-white/10' : 'bg-zinc-100' }}">{{ $builderPage->is_homepage ? '⌂' : '○' }}</span>
                            <span class="truncate">{{ $builderPage->name }}</span>
                        </div>
                    @endforeach
                </nav>
            </div>
        @elseif(in_array($builderLayout, ['sidebar-top', 'sidebar-bottom'], true) && $builderPages->count())
            <div class="pointer-events-auto fixed left-1/2 z-[55] hidden w-[min(900px,calc(100vw-32px))] -translate-x-1/2 overflow-hidden rounded-2xl border border-zinc-200 bg-white/95 p-3 shadow-2xl backdrop-blur-xl lg:block {{ $builderLayout === 'sidebar-top' ? 'top-[150px]' : 'bottom-6' }}">
                <nav class="flex items-center justify-center gap-1 overflow-x-auto">
                    @foreach($builderPages as $builderPage)
                        <div class="flex shrink-0 items-center gap-2 rounded-xl px-3 py-2 text-xs font-bold {{ request()->route('pageSlug') === $builderPage->slug ? 'bg-zinc-950 text-white' : 'text-zinc-600' }}">
                            <span>{{ $builderPage->is_homepage ? '⌂' : '○' }}</span>
                            <span>{{ $builderPage->name }}</span>
                        </div>
                    @endforeach
                </nav>
            </div>
        @endif

        <div x-data="{ open: false, category: 'sidebar' }" class="pointer-events-none fixed inset-0 z-[80] flex justify-center">
            <button type="button" @click="open = !open" class="pointer-events-auto fixed left-1/2 top-4 -translate-x-1/2 flex min-h-[50px] items-center gap-2.5 rounded-2xl border-2 border-zinc-950 bg-zinc-950 px-5 py-3 text-sm font-black text-white shadow-[0_10px_30px_rgba(0,0,0,0.20)] ring-4 ring-white/80 transition duration-200 hover:-translate-y-0.5 hover:bg-zinc-800 hover:shadow-[0_15px_38px_rgba(0,0,0,0.24)] focus:outline-none focus:ring-4 focus:ring-zinc-300">
                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-white text-sm text-zinc-950 shadow-sm">◈</span><span>Modelos</span>
            </button>
            <div x-show="open" x-cloak x-transition class="pointer-events-auto fixed left-1/2 top-[74px] w-[380px] max-w-[calc(100vw-24px)] -translate-x-1/2 rounded-3xl border border-zinc-200 bg-white p-4 shadow-2xl">
                <div class="flex items-start justify-between gap-3"><div><p class="text-[9px] font-black uppercase tracking-[0.2em] text-zinc-400">Design do website</p><h2 class="mt-1 text-lg font-black tracking-tight">Modelos</h2><p class="mt-1 text-[11px] leading-5 text-zinc-400">Escolhe a estrutura ou personaliza a tipografia do website.</p></div><button type="button" @click="open=false" class="h-8 w-8 rounded-xl bg-zinc-100 text-zinc-500">×</button></div>
                <div class="mt-4 space-y-2 rounded-2xl border border-zinc-200 bg-zinc-50 p-1.5">
                    <button type="button" @click="category='sidebar'" class="flex w-full items-center gap-3 rounded-xl px-3 py-3 text-left transition" :class="category === 'sidebar' ? 'bg-zinc-950 text-white shadow-md' : 'text-zinc-700 hover:bg-white'"><span class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/10 text-base">◧</span><span class="flex-1"><span class="block text-xs font-black">Sidebar</span><span class="block text-[10px] opacity-60">Esquerda, direita, em cima ou em baixo</span></span><span>›</span></button>
                    <button type="button" @click="category='typography'" class="flex w-full items-center gap-3 rounded-xl px-3 py-3 text-left transition" :class="category === 'typography' ? 'bg-zinc-950 text-white shadow-md' : 'text-zinc-700 hover:bg-white'"><span class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/10 text-base">Aa</span><span class="flex-1"><span class="block text-xs font-black">Tipo de letra</span><span class="block text-[10px] opacity-60">Fonte, títulos, subtítulos e texto</span></span><span>›</span></button>
                </div>
                <div x-show="category === 'sidebar'" x-transition class="mt-4">
                    <div class="mb-2 flex items-center gap-2 px-1"><span class="text-[9px] font-black uppercase tracking-[0.18em] text-zinc-400">Posição da Sidebar</span><span class="h-px flex-1 bg-zinc-100"></span></div>
                    <div class="grid grid-cols-2 gap-2">
                        @php($sidebarModels = ['sidebar-left' => ['Esquerda', 'Menu lateral esquerdo', '◧'], 'sidebar-right' => ['Direita', 'Menu lateral direito', '◨'], 'sidebar-top' => ['Em cima', 'Menu horizontal no topo', '▱'], 'sidebar-bottom' => ['Em baixo', 'Menu horizontal em baixo', '▰']])
                        @foreach($sidebarModels as $modelKey => [$modelName, $modelDescription, $modelIcon])
                            <button type="button" wire:click="$set('theme.layout', '{{ $modelKey }}'); $wire.save()" class="group flex min-h-[104px] flex-col items-start gap-2 rounded-2xl border p-3 text-left transition {{ data_get($builderTheme, 'layout', 'top') === $modelKey ? 'border-zinc-950 bg-zinc-950 text-white shadow-lg' : 'border-zinc-200 bg-white text-zinc-800 hover:border-zinc-400 hover:bg-zinc-50' }}"><span class="flex h-9 w-9 items-center justify-center rounded-xl {{ data_get($builderTheme, 'layout', 'top') === $modelKey ? 'bg-white/10' : 'bg-zinc-100' }} text-base">{{ $modelIcon }}</span><span><span class="block text-xs font-black">{{ $modelName }}</span><span class="mt-0.5 block text-[9px] {{ data_get($builderTheme, 'layout', 'top') === $modelKey ? 'text-white/50' : 'text-zinc-400' }}">{{ $modelDescription }}</span></span></button>
                        @endforeach
                    </div>
                    <div class="mt-4 rounded-2xl bg-zinc-50 p-3 text-[10px] leading-5 text-zinc-500"><strong class="text-zinc-800">Navegação automática:</strong> a sidebar usa os nomes das páginas do website.</div>
                </div>
                <div x-show="category === 'typography'" x-cloak x-transition class="mt-4">
                    <div class="mb-3 flex items-center gap-2 px-1"><span class="text-[9px] font-black uppercase tracking-[0.18em] text-zinc-400">Tipografia do website</span><span class="h-px flex-1 bg-zinc-100"></span></div>
                    @php($fontModels = ['modern-sans' => ['Inter', 'Moderno', 'Aa', 'font-sans'], 'classic-serif' => ['Georgia', 'Clássico', 'Aa', 'font-serif'], 'editorial' => ['Playfair Display', 'Editorial', 'Aa', 'font-serif'], 'geometric' => ['Trebuchet MS', 'Geométrico', 'Aa', 'font-sans'], 'clean' => ['Arial', 'Clean', 'Aa', 'font-sans'], 'friendly' => ['Verdana', 'Friendly', 'Aa', 'font-sans']])
                    <div class="grid gap-2">@foreach($fontModels as $fontKey => [$fontName, $fontLabel, $fontExample, $fontClass])<button type="button" wire:click="$set('theme.font_family', '{{ $fontKey }}'); $wire.save()" class="group flex items-center gap-3 rounded-2xl border px-3 py-3 text-left transition {{ data_get($builderTheme, 'font_family', 'modern-sans') === $fontKey ? 'border-zinc-950 bg-zinc-950 text-white shadow-lg' : 'border-zinc-200 bg-white text-zinc-800 hover:border-zinc-400 hover:bg-zinc-50' }}"><span class="flex h-12 w-14 items-center justify-center rounded-xl {{ data_get($builderTheme, 'font_family', 'modern-sans') === $fontKey ? 'bg-white/10' : 'bg-zinc-100' }} text-xl {{ $fontClass }}">Aa</span><span class="min-w-0 flex-1"><span class="block text-xs font-black">{{ $fontLabel }}</span><span class="mt-0.5 block text-[10px] opacity-60">{{ $fontName }}</span></span>@if(data_get($builderTheme, 'font_family', 'modern-sans') === $fontKey)<span>✓</span>@endif</button>@endforeach</div>
                    <div class="mt-4 rounded-2xl border border-zinc-200 bg-zinc-50 p-3"><p class="text-[9px] font-black uppercase tracking-[0.18em] text-zinc-400">Tamanhos</p><div class="mt-3 grid grid-cols-4 gap-2">@foreach(['compact' => ['Pequeno', 'text-2xl', 'text-sm'], 'balanced' => ['Normal', 'text-3xl', 'text-base'], 'large' => ['Grande', 'text-4xl', 'text-lg'], 'display' => ['Display', 'text-5xl', 'text-xl']] as $sizeKey => [$sizeName, $headingClass, $bodyClass])<button type="button" wire:click="$set('theme.typography_scale', '{{ $sizeKey }}'); $wire.save()" class="rounded-xl border p-2 text-left transition {{ data_get($builderTheme, 'typography_scale', 'balanced') === $sizeKey ? 'border-zinc-950 bg-zinc-950 text-white' : 'border-zinc-200 bg-white hover:border-zinc-400' }}"><span class="block text-[9px] font-black">{{ $sizeName }}</span><span class="mt-1 block truncate {{ $headingClass }} font-black">Título</span><span class="block truncate {{ $bodyClass }} opacity-60">Texto</span></button>@endforeach</div></div>
                    <div class="mt-3 rounded-2xl bg-zinc-950 p-4 text-white"><p class="text-[9px] font-black uppercase tracking-[0.18em] text-white/40">Pré-visualização</p><p class="mt-2 text-2xl font-black tracking-tight">Título principal</p><p class="mt-1 text-sm font-semibold text-white/60">Subtítulo da página</p><p class="mt-3 text-sm leading-6 text-white/55">Este texto mostra a tipografia escolhida.</p></div>
                </div>
                <button type="button" wire:click="save" class="mt-3 w-full rounded-xl bg-zinc-950 px-4 py-3 text-xs font-black text-white transition hover:bg-zinc-800">Aplicar e guardar modelo</button>
            </div>
        </div>
    @endif

    @fluxScripts
</body>
</html>
