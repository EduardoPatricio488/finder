<div
    x-data
    x-on:builder-template-applied.window="window.location.reload()"
    class="contents"
>
    @if($site)
        <button
            type="button"
            wire:click="$set('open', true)"
            class="inline-flex shrink-0 items-center gap-2 rounded-xl border border-zinc-200 bg-white px-3 py-2 text-xs font-black text-zinc-900 shadow-sm transition hover:border-indigo-300 hover:bg-indigo-50 hover:text-indigo-700 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white dark:hover:bg-zinc-800"
        >
            <span class="flex h-5 w-5 items-center justify-center rounded-lg bg-indigo-50 text-[11px] text-indigo-600 dark:bg-indigo-950/60 dark:text-indigo-300">✦</span>
            <span>Modelos</span>
            @if(data_get($site->theme, 'template_label'))
                <span class="hidden rounded-full bg-zinc-100 px-2 py-1 text-[9px] font-black text-zinc-500 xl:inline dark:bg-zinc-800">{{ data_get($site->theme, 'template_label') }}</span>
            @endif
        </button>

        @if($open)
            <div class="fixed inset-0 z-[80] flex items-center justify-center bg-zinc-950/50 p-4 backdrop-blur-sm" wire:click.self="$set('open', false)">
                <div class="w-full max-w-5xl overflow-hidden rounded-3xl border border-zinc-200 bg-white shadow-2xl dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex items-start justify-between gap-4 border-b border-zinc-200 px-6 py-5 dark:border-zinc-800">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[.2em] text-indigo-600">Finder · Modelos</p>
                            <h2 class="mt-1 text-2xl font-black tracking-tight">Escolhe a estrutura do teu website</h2>
                            <p class="mt-1 max-w-2xl text-sm leading-6 text-zinc-500">Escolhe como queres organizar a navegação: menu em cima, sidebar à esquerda ou à direita. As páginas do teu website aparecem automaticamente no menu.</p>
                        </div>
                        <button type="button" wire:click="$set('open', false)" class="rounded-xl border border-zinc-200 px-3 py-2 text-sm font-black hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800">✕</button>
                    </div>
                    <div class="max-h-[72vh] overflow-y-auto p-6">
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            @php
                                $templates = [
                                    'modern' => ['Moderno', 'Navegação em cima · hero centrado', 'bg-gradient-to-br from-indigo-50 via-white to-violet-50', '☰'],
                                    'sidebar-left' => ['Sidebar esquerda', 'Menu vertical · conteúdo à direita', 'bg-zinc-100', '◧'],
                                    'sidebar-right' => ['Sidebar direita', 'Conteúdo à esquerda · menu vertical', 'bg-zinc-100', '◨'],
                                    'minimal' => ['Minimalista', 'Navegação superior · pouco ruído · títulos limpos', 'bg-white', '—'],
                                    'business' => ['Empresa', 'Navegação superior · hero lateral · blocos profissionais', 'bg-slate-100', '▦'],
                                    'portfolio' => ['Portfólio', 'Navegação superior · editorial · imagens e títulos fortes', 'bg-stone-100', '▤'],
                                    'store' => ['Loja', 'Navegação superior · catálogo · cartões · produtos', 'bg-amber-50', '▥'],
                                ];
                                $pageNames = $site->pages->sortBy('sort_order')->pluck('name')->filter()->values();
                            @endphp
                            @foreach($templates as $key => [$name, $description, $preview, $icon])
                                <button type="button" wire:click="apply('{{ $key }}')" wire:loading.attr="disabled" class="group overflow-hidden rounded-2xl border border-zinc-200 bg-white text-left transition hover:-translate-y-1 hover:border-indigo-400 hover:shadow-xl dark:border-zinc-700 dark:bg-zinc-950">
                                    <div class="relative h-40 {{ $preview }} p-4 dark:opacity-90">
                                        <div class="flex h-full gap-2">
                                            @if(in_array($key, ['sidebar-left', 'sidebar-right'], true))
                                                @if($key === 'sidebar-left')
                                                    <div class="w-1/3 overflow-hidden rounded-lg bg-zinc-900/10 p-2">
                                                        <div class="mb-2 h-2 w-10 rounded bg-zinc-900/30"></div>
                                                        <div class="space-y-1">
                                                            @forelse($pageNames->take(4) as $pageName)
                                                                <div class="truncate rounded bg-white/70 px-1.5 py-1 text-[6px] font-bold text-zinc-700">{{ $pageName }}</div>
                                                            @empty
                                                                <div class="h-2 rounded bg-zinc-900/15"></div><div class="h-2 rounded bg-zinc-900/15"></div>
                                                            @endforelse
                                                        </div>
                                                    </div>
                                                @endif
                                                <div class="flex-1 rounded-lg bg-white/80 p-3 shadow-sm">
                                                    <div class="h-2 w-2/3 rounded bg-zinc-900/20"></div>
                                                    <div class="mt-2 h-1.5 w-full rounded bg-zinc-900/10"></div>
                                                    <div class="mt-1 h-1.5 w-4/5 rounded bg-zinc-900/10"></div>
                                                    <div class="mt-4 h-10 rounded bg-zinc-900/5"></div>
                                                </div>
                                                @if($key === 'sidebar-right')
                                                    <div class="w-1/3 overflow-hidden rounded-lg bg-zinc-900/10 p-2">
                                                        <div class="mb-2 h-2 w-10 rounded bg-zinc-900/30"></div>
                                                        <div class="space-y-1">
                                                            @forelse($pageNames->take(4) as $pageName)
                                                                <div class="truncate rounded bg-white/70 px-1.5 py-1 text-[6px] font-bold text-zinc-700">{{ $pageName }}</div>
                                                            @empty
                                                                <div class="h-2 rounded bg-zinc-900/15"></div><div class="h-2 rounded bg-zinc-900/15"></div>
                                                            @endforelse
                                                        </div>
                                                    </div>
                                                @endif
                                            @else
                                                <div class="w-full rounded-lg bg-white/80 p-3 shadow-sm">
                                                    <div class="flex items-center justify-between gap-2">
                                                        <div class="h-2 w-14 shrink-0 rounded bg-zinc-900/25"></div>
                                                        <div class="flex min-w-0 gap-1 overflow-hidden">
                                                            @forelse($pageNames->take(4) as $pageName)
                                                                <span class="max-w-[42px] truncate rounded bg-zinc-900/5 px-1.5 py-1 text-[6px] font-bold text-zinc-600">{{ $pageName }}</span>
                                                            @empty
                                                                <span class="h-1.5 w-7 rounded bg-zinc-900/10"></span><span class="h-1.5 w-7 rounded bg-zinc-900/10"></span><span class="h-1.5 w-7 rounded bg-zinc-900/10"></span>
                                                            @endforelse
                                                        </div>
                                                    </div>
                                                    <div class="mt-4 {{ in_array($key, ['modern', 'minimal', 'store'], true) ? 'text-center' : '' }}">
                                                        <div class="h-3 w-2/3 rounded bg-zinc-900/20 {{ in_array($key, ['modern', 'minimal', 'store'], true) ? 'mx-auto' : '' }}"></div>
                                                        <div class="mt-2 h-1.5 w-4/5 rounded bg-zinc-900/10 {{ in_array($key, ['modern', 'minimal', 'store'], true) ? 'mx-auto' : '' }}"></div>
                                                        <div class="mt-4 h-5 w-16 rounded bg-indigo-500/50 {{ in_array($key, ['modern', 'minimal', 'store'], true) ? 'mx-auto' : '' }}"></div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                        <span class="absolute right-3 top-3 flex h-8 w-8 items-center justify-center rounded-xl bg-white/80 text-xs font-black text-zinc-700 shadow-sm">{{ $icon }}</span>
                                    </div>
                                    <div class="p-4">
                                        <div class="flex items-center justify-between gap-2">
                                            <h3 class="font-black">{{ $name }}</h3>
                                            @if(data_get($site->theme, 'template') === $key)<span class="rounded-full bg-emerald-50 px-2 py-1 text-[9px] font-black uppercase text-emerald-700">Atual</span>@endif
                                        </div>
                                        <p class="mt-1 text-xs leading-5 text-zinc-500">{{ $description }}</p>
                                        <p class="mt-2 text-[10px] font-semibold text-zinc-400">{{ $pageNames->count() }} {{ $pageNames->count() === 1 ? 'página' : 'páginas' }} detectadas</p>
                                        <span class="mt-3 inline-flex rounded-xl border border-zinc-200 px-3 py-2 text-[10px] font-black group-hover:border-indigo-200 group-hover:bg-indigo-50 group-hover:text-indigo-700 dark:border-zinc-700 dark:group-hover:bg-indigo-950/40">Aplicar modelo →</span>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                        <div class="mt-6 grid gap-3 rounded-2xl border border-zinc-200 bg-zinc-50 p-4 sm:grid-cols-3 dark:border-zinc-800 dark:bg-zinc-950">
                            <div><p class="text-[10px] font-black uppercase tracking-wider text-zinc-400">Navegação</p><p class="mt-1 text-sm font-bold">Em cima, esquerda ou direita</p></div>
                            <div><p class="text-[10px] font-black uppercase tracking-wider text-zinc-400">Páginas</p><p class="mt-1 text-sm font-bold">Os nomes são lidos automaticamente</p></div>
                            <div><p class="text-[10px] font-black uppercase tracking-wider text-zinc-400">Estrutura</p><p class="mt-1 text-sm font-bold">Largura, cartões, sombras e cantos</p></div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>