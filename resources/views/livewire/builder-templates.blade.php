<div
    x-data
    x-on:builder-template-applied.window="window.location.reload()"
    class="fixed right-4 top-20 z-[70]"
>
    @if($site)
        <button
            type="button"
            wire:click="$set('open', true)"
            class="flex items-center gap-2 rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-xs font-black text-zinc-900 shadow-xl shadow-zinc-900/10 transition hover:-translate-y-0.5 hover:border-indigo-300 hover:shadow-2xl dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
        >
            <span class="flex h-7 w-7 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/60 dark:text-indigo-300">✦</span>
            <span>Modelos</span>
            @if(data_get($site->theme, 'template_label'))
                <span class="hidden rounded-full bg-zinc-100 px-2 py-1 text-[9px] font-black text-zinc-500 sm:inline dark:bg-zinc-800">{{ data_get($site->theme, 'template_label') }}</span>
            @endif
        </button>

        @if($open)
            <div class="fixed inset-0 z-[80] flex items-center justify-center bg-zinc-950/50 p-4 backdrop-blur-sm" wire:click.self="$set('open', false)">
                <div class="w-full max-w-5xl overflow-hidden rounded-3xl border border-zinc-200 bg-white shadow-2xl dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex items-start justify-between gap-4 border-b border-zinc-200 px-6 py-5 dark:border-zinc-800">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[.2em] text-indigo-600">Finder · Modelos</p>
                            <h2 class="mt-1 text-2xl font-black tracking-tight">Escolhe a estrutura do teu website</h2>
                            <p class="mt-1 max-w-2xl text-sm leading-6 text-zinc-500">Aplica uma base completa e depois continua a personalizar títulos, secções, cores e conteúdo.</p>
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
                                    'minimal' => ['Minimalista', 'Pouco ruído · títulos limpos', 'bg-white', '—'],
                                    'business' => ['Empresa', 'Hero lateral · blocos profissionais', 'bg-slate-100', '▦'],
                                    'portfolio' => ['Portfólio', 'Editorial · imagens e títulos fortes', 'bg-stone-100', '▤'],
                                    'store' => ['Loja', 'Catálogo · cartões · navegação superior', 'bg-amber-50', '▥'],
                                ];
                            @endphp

                            @foreach($templates as $key => [$name, $description, $preview, $icon])
                                <button
                                    type="button"
                                    wire:click="apply('{{ $key }}')"
                                    wire:loading.attr="disabled"
                                    class="group overflow-hidden rounded-2xl border border-zinc-200 bg-white text-left transition hover:-translate-y-1 hover:border-indigo-400 hover:shadow-xl dark:border-zinc-700 dark:bg-zinc-950"
                                >
                                    <div class="relative h-32 {{ $preview }} p-4 dark:opacity-90">
                                        <div class="flex h-full gap-2">
                                            @if(in_array($key, ['sidebar-left', 'sidebar-right'], true))
                                                @if($key === 'sidebar-left')<div class="w-1/4 rounded-lg bg-zinc-900/15 p-2"><div class="h-2 w-10 rounded bg-zinc-900/30"></div><div class="mt-3 space-y-1"><div class="h-1.5 rounded bg-zinc-900/15"></div><div class="h-1.5 rounded bg-zinc-900/15"></div><div class="h-1.5 rounded bg-zinc-900/15"></div></div></div>@endif
                                                <div class="flex-1 rounded-lg bg-white/80 p-3 shadow-sm"><div class="h-2 w-2/3 rounded bg-zinc-900/20"></div><div class="mt-2 h-1.5 w-full rounded bg-zinc-900/10"></div><div class="mt-1 h-1.5 w-4/5 rounded bg-zinc-900/10"></div></div>
                                                @if($key === 'sidebar-right')<div class="w-1/4 rounded-lg bg-zinc-900/15 p-2"><div class="h-2 w-10 rounded bg-zinc-900/30"></div><div class="mt-3 space-y-1"><div class="h-1.5 rounded bg-zinc-900/15"></div><div class="h-1.5 rounded bg-zinc-900/15"></div><div class="h-1.5 rounded bg-zinc-900/15"></div></div></div>@endif
                                            @else
                                                <div class="w-full rounded-lg bg-white/80 p-3 shadow-sm">
                                                    <div class="flex items-center justify-between"><div class="h-2 w-14 rounded bg-zinc-900/25"></div><div class="flex gap-1"><span class="h-1.5 w-7 rounded bg-zinc-900/10"></span><span class="h-1.5 w-7 rounded bg-zinc-900/10"></span><span class="h-1.5 w-7 rounded bg-zinc-900/10"></span></div></div>
                                                    <div class="mt-4 {{ in_array($key, ['modern', 'minimal', 'store'], true) ? 'text-center' : '' }}"><div class="h-3 w-2/3 rounded bg-zinc-900/20 {{ in_array($key, ['modern', 'minimal', 'store'], true) ? 'mx-auto' : '' }}"></div><div class="mt-2 h-1.5 w-4/5 rounded bg-zinc-900/10 {{ in_array($key, ['modern', 'minimal', 'store'], true) ? 'mx-auto' : '' }}"></div><div class="mt-4 h-5 w-16 rounded bg-indigo-500/50 {{ in_array($key, ['modern', 'minimal', 'store'], true) ? 'mx-auto' : '' }}"></div></div>
                                                </div>
                                            @endif
                                        </div>
                                        <span class="absolute right-3 top-3 flex h-8 w-8 items-center justify-center rounded-xl bg-white/80 text-xs font-black text-zinc-700 shadow-sm">{{ $icon }}</span>
                                    </div>
                                    <div class="p-4">
                                        <div class="flex items-center justify-between gap-2"><h3 class="font-black">{{ $name }}</h3>@if(data_get($site->theme, 'template') === $key)<span class="rounded-full bg-emerald-50 px-2 py-1 text-[9px] font-black uppercase text-emerald-700">Atual</span>@endif</div>
                                        <p class="mt-1 text-xs leading-5 text-zinc-500">{{ $description }}</p>
                                        <span class="mt-4 inline-flex rounded-xl border border-zinc-200 px-3 py-2 text-[10px] font-black group-hover:border-indigo-200 group-hover:bg-indigo-50 group-hover:text-indigo-700 dark:border-zinc-700 dark:group-hover:bg-indigo-950/40">Aplicar modelo →</span>
                                    </div>
                                </button>
                            @endforeach
                        </div>

                        <div class="mt-6 grid gap-3 rounded-2xl border border-zinc-200 bg-zinc-50 p-4 sm:grid-cols-3 dark:border-zinc-800 dark:bg-zinc-950">
                            <div><p class="text-[10px] font-black uppercase tracking-wider text-zinc-400">Navegação</p><p class="mt-1 text-sm font-bold">Em cima, esquerda ou direita</p></div>
                            <div><p class="text-[10px] font-black uppercase tracking-wider text-zinc-400">Títulos</p><p class="mt-1 text-sm font-bold">Minimal, bold, editorial ou empresa</p></div>
                            <div><p class="text-[10px] font-black uppercase tracking-wider text-zinc-400">Estrutura</p><p class="mt-1 text-sm font-bold">Largura, cartões, sombras e cantos</p></div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
