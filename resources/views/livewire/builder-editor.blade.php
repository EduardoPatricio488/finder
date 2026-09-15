<div
    class="flex h-screen flex-col bg-zinc-100 dark:bg-zinc-950"
    x-data="{ mobile: false, tablet: false, autoSaveTimer: null }"
    x-on:input.debounce.1500ms="if ($wire.dirty) $wire.save()"
>
    <style>
        @keyframes builder-fade-in {
            from { opacity: 0; transform: translateY(4px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .builder-fade-in { animation: builder-fade-in .3s ease-out both; }
        @media (prefers-reduced-motion: reduce) {
            .builder-fade-in { animation: none; }
        }
    </style>

    {{-- ============================= TOP TOOLBAR ============================= --}}
    <header class="z-20 flex h-14 shrink-0 items-center justify-between gap-4 border-b border-zinc-200 bg-white px-3 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        {{-- ESQUERDA --}}
        <div class="flex min-w-0 flex-1 items-center gap-2.5">
            <a
                href="{{ route('dashboard') }}"
                wire:navigate
                title="Voltar ao Dashboard"
                aria-label="Voltar ao Dashboard"
                class="flex size-8 shrink-0 items-center justify-center rounded-lg text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-white"
            >
                <flux:icon name="arrow-left" class="size-4" />
            </a>

            <span class="hidden items-center gap-1.5 text-sm font-bold tracking-tight text-zinc-900 sm:flex dark:text-white">
                <span class="flex size-5 items-center justify-center rounded-md bg-zinc-950 text-[10px] font-black text-white dark:bg-white dark:text-zinc-950">F</span>
                FINDER
            </span>

            <span class="hidden h-4 w-px bg-zinc-200 sm:block dark:bg-zinc-800" aria-hidden="true"></span>

            <div class="flex min-w-0 items-center gap-2">
                <span class="truncate text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ $site->name }}</span>
                <span class="hidden shrink-0 rounded-full px-2 py-0.5 text-[10px] font-semibold sm:inline {{ $site->is_published ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400' : 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400' }}">
                    {{ $site->is_published ? 'Publicado' : 'Rascunho' }}
                </span>
            </div>
        </div>

        {{-- CENTRO — estado de gravação --}}
        <div class="hidden shrink-0 items-center gap-2 rounded-full border border-zinc-200 bg-zinc-50 px-3 py-1 text-xs font-medium sm:flex dark:border-zinc-800 dark:bg-zinc-800/60">
            @if ($dirty)
                <svg class="size-3 animate-spin text-amber-500" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3"></circle>
                    <path class="opacity-90" fill="currentColor" d="M21 12a9 9 0 0 0-9-9V0l6 6-6 6V6a6 6 0 1 1-6 6H3a9 9 0 0 0 18 0Z"></path>
                </svg>
                <span class="text-amber-600 dark:text-amber-400">A guardar automaticamente…</span>
            @else
                <span class="size-1.5 rounded-full bg-emerald-500" aria-hidden="true"></span>
                <span class="text-zinc-500 dark:text-zinc-400">Guardado</span>
            @endif
        </div>

        {{-- DIREITA --}}
        <div class="flex shrink-0 items-center gap-1.5">
            <div class="flex items-center overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700">
                <button
                    type="button"
                    wire:click="undo"
                    @disabled(count($history) === 0)
                    title="Desfazer"
                    aria-label="Desfazer"
                    class="flex size-8 items-center justify-center text-zinc-500 transition hover:bg-zinc-100 disabled:pointer-events-none disabled:opacity-30 dark:text-zinc-400 dark:hover:bg-zinc-800"
                >
                    <flux:icon name="arrow-uturn-left" class="size-4" />
                </button>
                <span class="h-5 w-px bg-zinc-200 dark:bg-zinc-700" aria-hidden="true"></span>
                <button
                    type="button"
                    wire:click="redo"
                    @disabled(count($future) === 0)
                    title="Refazer"
                    aria-label="Refazer"
                    class="flex size-8 items-center justify-center text-zinc-500 transition hover:bg-zinc-100 disabled:pointer-events-none disabled:opacity-30 dark:text-zinc-400 dark:hover:bg-zinc-800"
                >
                    <flux:icon name="arrow-uturn-right" class="size-4" />
                </button>
            </div>

            <span class="hidden h-5 w-px bg-zinc-200 sm:block dark:bg-zinc-700" aria-hidden="true"></span>

            <flux:button
                size="sm"
                icon="eye"
                href="{{ route('site.public', [$site, 'pageSlug' => $pageSlug]) }}?preview=1"
                target="_blank"
            >
                Pré-visualizar
            </flux:button>

            <flux:button
                size="sm"
                icon="cloud-arrow-up"
                wire:click="save"
                wire:loading.attr="disabled"
                wire:target="save"
            >
                Guardar
            </flux:button>

            @if ($site->is_published)
                <flux:button size="sm" variant="ghost" icon="eye-slash" wire:click="unpublish">
                    Despublicar
                </flux:button>
            @else
                <flux:button size="sm" variant="primary" icon="rocket-launch" wire:click="publish" wire:loading.attr="disabled">
                    Publicar
                </flux:button>
            @endif
        </div>
    </header>

    <div class="flex min-h-0 flex-1">
        {{-- ============================= SIDEBAR ESQUERDA ============================= --}}
        <aside class="hidden w-64 shrink-0 overflow-y-auto border-r border-zinc-200 bg-white p-4 lg:block dark:border-zinc-800 dark:bg-zinc-900">
            @php
                $elementGroups = [
                    'Básicos' => ['hero', 'text', 'image', 'button'],
                    'Conteúdo' => ['feature_grid', 'card', 'testimonials', 'faq', 'gallery'],
                    'Negócio' => ['product_grid', 'product_card', 'pricing', 'blog_posts', 'contact_form', 'newsletter'],
                    'Media' => ['video', 'map', 'social_links'],
                    'Conversão' => ['cta'],
                ];
                $elementIcons = [
                    'hero' => 'sparkles', 'text' => 'document-text', 'image' => 'photo', 'button' => 'cursor-arrow-rays',
                    'feature_grid' => 'squares-2x2', 'card' => 'rectangle-stack', 'testimonials' => 'chat-bubble-left-right',
                    'faq' => 'question-mark-circle', 'gallery' => 'photo',
                    'product_grid' => 'shopping-bag', 'product_card' => 'tag', 'pricing' => 'currency-euro',
                    'blog_posts' => 'newspaper', 'contact_form' => 'envelope', 'newsletter' => 'envelope-open',
                    'video' => 'play-circle', 'map' => 'map-pin', 'social_links' => 'share',
                    'cta' => 'bolt',
                ];
            @endphp

            <p class="text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Elementos</p>

            <div class="mt-3 space-y-5">
                @foreach ($elementGroups as $groupLabel => $groupTypes)
                    <div>
                        <p class="mb-2 text-[10px] font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-600">{{ $groupLabel }}</p>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach ($groupTypes as $type)
                                <button
                                    type="button"
                                    wire:click="addSection('{{ $type }}')"
                                    class="group flex flex-col items-start gap-2 rounded-xl border border-zinc-200 px-2.5 py-3 text-left transition-all duration-150 hover:-translate-y-0.5 hover:border-indigo-300 hover:bg-indigo-50 hover:shadow-sm active:translate-y-0 dark:border-zinc-700 dark:hover:border-indigo-500/40 dark:hover:bg-indigo-500/10"
                                >
                                    <span class="flex size-7 items-center justify-center rounded-lg bg-zinc-100 text-zinc-500 transition group-hover:bg-indigo-600 group-hover:text-white dark:bg-zinc-800 dark:text-zinc-400">
                                        <flux:icon :name="$elementIcons[$type] ?? 'squares-2x2'" class="size-3.5" />
                                    </span>
                                    <span class="text-xs font-medium leading-tight text-zinc-700 dark:text-zinc-300">
                                        {{ \Illuminate\Support\Str::headline($type) }}
                                    </span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- ===================== PÁGINAS ===================== --}}
            <div class="mt-8 flex items-center justify-between border-t border-zinc-100 pt-5 dark:border-zinc-800">
                <p class="text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Páginas</p>
                <button
                    type="button"
                    wire:click="createPage"
                    class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-semibold text-indigo-600 transition hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-500/10"
                >
                    <flux:icon name="plus" class="size-3.5" /> Página
                </button>
            </div>

            <div class="mt-3 space-y-1">
                @foreach ($site->pages as $page)
                    <button
                        type="button"
                        wire:click="selectPage({{ $page->id }})"
                        class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm transition {{ $pageId === $page->id ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300' : 'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800' }}"
                    >
                        <flux:icon name="document" class="size-3.5 shrink-0 opacity-60" />
                        <span class="flex-1 truncate">{{ $page->name }}</span>
                        @if ($page->is_homepage)
                            <span class="shrink-0 rounded-full bg-zinc-200 px-1.5 py-0.5 text-[9px] font-bold text-zinc-500 dark:bg-zinc-700 dark:text-zinc-400">HOME</span>
                        @endif
                    </button>
                @endforeach
            </div>

            <div class="mt-3 grid grid-cols-2 gap-2">
                <button
                    type="button"
                    wire:click="duplicatePage"
                    class="flex items-center justify-center gap-1.5 rounded-lg border border-zinc-200 px-2 py-2 text-xs font-medium text-zinc-600 transition hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800"
                >
                    <flux:icon name="document-duplicate" class="size-3.5" /> Duplicar
                </button>
                @if ($site->pages->count() > 1 && $site->pages->firstWhere('id', $pageId)?->is_homepage === false)
                    <button
                        type="button"
                        wire:click="deletePage"
                        wire:confirm="Tens a certeza que queres apagar esta página?"
                        class="flex items-center justify-center gap-1.5 rounded-lg border border-red-200 px-2 py-2 text-xs font-medium text-red-600 transition hover:bg-red-50 dark:border-red-900/60 dark:text-red-400 dark:hover:bg-red-500/10"
                    >
                        <flux:icon name="trash" class="size-3.5" /> Apagar
                    </button>
                @endif
            </div>
        </aside>

        {{-- ============================= CANVAS ============================= --}}
        <main class="min-w-0 flex-1 overflow-y-auto bg-zinc-100 p-4 sm:p-6 dark:bg-zinc-950">
            <div class="mx-auto max-w-6xl">
                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <input
                            wire:model.live.debounce.500ms="pageName"
                            aria-label="Nome da página"
                            class="rounded-lg border-0 bg-transparent p-0 text-lg font-semibold text-zinc-900 outline-none transition focus:ring-2 focus:ring-indigo-500/40 dark:text-white"
                        >
                        <p class="text-xs text-zinc-400 dark:text-zinc-500">/{{ $pageSlug }}</p>
                    </div>

                    {{-- ===================== DEVICE SWITCHER ===================== --}}
                    <div class="flex items-center gap-1 rounded-xl border border-zinc-200 bg-white p-1 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                        <button
                            type="button"
                            @click="mobile=false; tablet=false"
                            :class="!mobile && !tablet ? 'bg-zinc-950 text-white dark:bg-white dark:text-zinc-950' : 'text-zinc-500 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800'"
                            class="flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-medium transition"
                        >
                            <flux:icon name="computer-desktop" class="size-3.5" />
                            <span class="hidden sm:inline">Desktop</span>
                        </button>
                        <button
                            type="button"
                            @click="mobile=false; tablet=true"
                            :class="tablet ? 'bg-zinc-950 text-white dark:bg-white dark:text-zinc-950' : 'text-zinc-500 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800'"
                            class="flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-medium transition"
                        >
                            <flux:icon name="device-tablet" class="size-3.5" />
                            <span class="hidden sm:inline">Tablet</span>
                        </button>
                        <button
                            type="button"
                            @click="mobile=true; tablet=false"
                            :class="mobile ? 'bg-zinc-950 text-white dark:bg-white dark:text-zinc-950' : 'text-zinc-500 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800'"
                            class="flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-medium transition"
                        >
                            <flux:icon name="device-phone-mobile" class="size-3.5" />
                            <span class="hidden sm:inline">Mobile</span>
                        </button>
                    </div>
                </div>

                {{-- ===================== WEBSITE (dentro do editor) ===================== --}}
                <div
                    class="mx-auto overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-2xl shadow-zinc-900/10 transition-all duration-300 dark:border-zinc-800 dark:shadow-black/40"
                    :class="mobile ? 'max-w-[390px]' : (tablet ? 'max-w-[768px]' : 'max-w-none')"
                >
                    {{-- chrome do "browser" do website --}}
                    <div class="flex items-center gap-3 border-b border-zinc-100 bg-zinc-50 px-4 py-2.5 dark:border-zinc-800 dark:bg-zinc-900/60">
                        <div class="flex gap-1.5">
                            <span class="size-2 rounded-full bg-zinc-300 dark:bg-zinc-700"></span>
                            <span class="size-2 rounded-full bg-zinc-300 dark:bg-zinc-700"></span>
                            <span class="size-2 rounded-full bg-zinc-300 dark:bg-zinc-700"></span>
                        </div>
                        <div class="flex min-w-0 flex-1 items-center justify-center gap-2 truncate text-xs text-zinc-400 dark:text-zinc-500">
                            <span class="truncate">{{ $site->name }} · {{ $pageName }}</span>
                        </div>
                        @if ($site->is_published)
                            <span class="shrink-0 rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400">Publicado</span>
                        @else
                            <span class="shrink-0 rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold text-amber-700 dark:bg-amber-500/15 dark:text-amber-400">Rascunho</span>
                        @endif
                    </div>

                    @forelse ($sections as $index => $section)
                        <section
                            class="builder-fade-in group/section relative border-b border-zinc-100 p-8 outline-2 -outline-offset-2 outline-transparent transition-colors last:border-0 hover:outline-indigo-300/60 dark:border-zinc-800 dark:hover:outline-indigo-500/30"
                            wire:key="section-{{ $section['id'] ?? 'new-'.$index }}"
                        >
                            {{-- rótulo da secção, visível no hover --}}
                            <span class="pointer-events-none absolute left-3 top-3 rounded-md bg-zinc-900/80 px-2 py-0.5 text-[10px] font-medium text-white opacity-0 backdrop-blur transition group-hover/section:opacity-100 dark:bg-white/80 dark:text-zinc-900">
                                {{ $index + 1 }} · {{ \Illuminate\Support\Str::headline($section['type']) }}
                            </span>

                            {{-- toolbar da secção, visível no hover --}}
                            <div class="absolute right-3 top-3 hidden items-center gap-0.5 rounded-lg border border-zinc-200 bg-white p-1 shadow-md group-hover/section:flex dark:border-zinc-700 dark:bg-zinc-900">
                                <button
                                    type="button"
                                    wire:click="moveSection({{ $index }}, 'up')"
                                    title="Mover para cima"
                                    aria-label="Mover secção para cima"
                                    class="flex size-6 items-center justify-center rounded text-zinc-500 transition hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800"
                                >
                                    <flux:icon name="chevron-up" class="size-3.5" />
                                </button>
                                <button
                                    type="button"
                                    wire:click="moveSection({{ $index }}, 'down')"
                                    title="Mover para baixo"
                                    aria-label="Mover secção para baixo"
                                    class="flex size-6 items-center justify-center rounded text-zinc-500 transition hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800"
                                >
                                    <flux:icon name="chevron-down" class="size-3.5" />
                                </button>
                                <span class="mx-0.5 h-4 w-px bg-zinc-200 dark:bg-zinc-700" aria-hidden="true"></span>
                                <button
                                    type="button"
                                    wire:click="removeSection({{ $index }})"
                                    wire:confirm="Remover este elemento?"
                                    title="Remover"
                                    aria-label="Remover secção"
                                    class="flex size-6 items-center justify-center rounded text-red-500 transition hover:bg-red-50 dark:hover:bg-red-500/10"
                                >
                                    <flux:icon name="trash" class="size-3.5" />
                                </button>
                            </div>

                            @switch($section['type'])
                                @case('hero')
                                    <div class="mx-auto max-w-3xl text-center">
                                        <h2 class="text-4xl font-bold text-zinc-900 dark:text-white">{{ $section['content']['title'] ?? '' }}</h2>
                                        <p class="mt-4 text-zinc-500 dark:text-zinc-400">{{ $section['content']['subtitle'] ?? '' }}</p>
                                        @if (!empty($section['content']['button_label']))
                                            <span class="mt-6 inline-flex rounded-xl px-5 py-3 text-sm font-semibold text-white" style="background:{{ $theme['primary'] ?? '#635bff' }}">
                                                {{ $section['content']['button_label'] }}
                                            </span>
                                        @endif
                                    </div>
                                    @break

                                @case('text')
                                    <div class="mx-auto max-w-3xl">
                                        <h2 class="text-3xl font-semibold text-zinc-900 dark:text-white">{{ $section['content']['title'] ?? '' }}</h2>
                                        <p class="mt-4 whitespace-pre-line leading-7 text-zinc-600 dark:text-zinc-400">{{ $section['content']['body'] ?? '' }}</p>
                                    </div>
                                    @break

                                @case('image')
                                    <div class="mx-auto max-w-4xl">
                                        @if (!empty($section['content']['url']))
                                            <img src="{{ $section['content']['url'] }}" alt="{{ $section['content']['alt'] ?? '' }}" class="max-h-[500px] w-full rounded-2xl object-cover">
                                        @else
                                            <div class="rounded-2xl border-2 border-dashed border-zinc-200 p-16 text-center text-sm text-zinc-400 dark:border-zinc-700">
                                                Adiciona uma imagem no painel direito.
                                            </div>
                                        @endif
                                    </div>
                                    @break

                                @case('feature_grid')
                                    <div class="mx-auto max-w-4xl">
                                        <h2 class="text-center text-3xl font-semibold text-zinc-900 dark:text-white">{{ $section['content']['title'] ?? '' }}</h2>
                                        <div class="mt-8 grid gap-4 md:grid-cols-3">
                                            @foreach ($section['content']['items'] ?? [] as $item)
                                                <article class="rounded-2xl border border-zinc-200 p-5 dark:border-zinc-700">
                                                    <h3 class="font-semibold text-zinc-900 dark:text-white">{{ $item['title'] ?? '' }}</h3>
                                                    <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">{{ $item['description'] ?? '' }}</p>
                                                </article>
                                            @endforeach
                                        </div>
                                    </div>
                                    @break

                                @case('cta')
                                    <div class="mx-auto max-w-3xl rounded-3xl bg-zinc-950 p-12 text-center text-white">
                                        <h2 class="text-3xl font-bold">{{ $section['content']['title'] ?? '' }}</h2>
                                        <p class="mt-3 text-zinc-300">{{ $section['content']['description'] ?? '' }}</p>
                                    </div>
                                    @break

                                @default
                                    <div class="mx-auto max-w-3xl rounded-2xl border-2 border-dashed border-zinc-200 p-12 text-center dark:border-zinc-700">
                                        <p class="font-semibold text-zinc-900 dark:text-white">{{ \Illuminate\Support\Str::headline($section['type']) }}</p>
                                        <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">{{ $section['content']['title'] ?? $section['content']['description'] ?? 'Componente pronto para configurar.' }}</p>
                                    </div>
                            @endswitch
                        </section>
                    @empty
                        <div class="flex flex-col items-center gap-3 p-20 text-center">
                            <span class="flex size-12 items-center justify-center rounded-2xl bg-zinc-100 text-zinc-400 dark:bg-zinc-800 dark:text-zinc-500">
                                <flux:icon name="squares-2x2" class="size-6" />
                            </span>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">Adiciona um elemento a partir da barra lateral.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </main>

        {{-- ============================= SIDEBAR DIREITA — PROPRIEDADES ============================= --}}
        <aside class="hidden w-80 shrink-0 overflow-y-auto border-l border-zinc-200 bg-white p-4 xl:block dark:border-zinc-800 dark:bg-zinc-900">
            <p class="text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Propriedades</p>

            <div class="mt-4 space-y-2.5">
                @foreach ($sections as $index => $section)
                    <details class="group overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700" {{ $loop->last ? 'open' : '' }}>
                        <summary class="flex cursor-pointer list-none items-center gap-2.5 px-3 py-3 text-sm font-semibold text-zinc-800 transition hover:bg-zinc-50 dark:text-zinc-100 dark:hover:bg-zinc-800/60">
                            <span class="flex size-6 shrink-0 items-center justify-center rounded-lg bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                                <flux:icon :name="$elementIcons[$section['type']] ?? 'squares-2x2'" class="size-3.5" />
                            </span>
                            <span class="flex-1 truncate">{{ $index + 1 }} · {{ \Illuminate\Support\Str::headline($section['type']) }}</span>
                            <flux:icon name="chevron-down" class="size-3.5 shrink-0 text-zinc-400 transition group-open:rotate-180" />
                        </summary>

                        <div class="space-y-3 border-t border-zinc-100 bg-zinc-50/60 p-3 dark:border-zinc-800 dark:bg-zinc-950/40">
                            <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-300">
                                Nome
                                <input
                                    wire:model.live.debounce.500ms="sections.{{ $index }}.label"
                                    class="mt-1.5 w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white"
                                >
                            </label>

                            @if (isset($section['content']['title']))
                                <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-300">
                                    Título
                                    <input
                                        wire:model.live.debounce.500ms="sections.{{ $index }}.content.title"
                                        class="mt-1.5 w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white"
                                    >
                                </label>
                            @endif

                            @if (isset($section['content']['description']))
                                <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-300">
                                    Descrição
                                    <textarea
                                        wire:model.live.debounce.500ms="sections.{{ $index }}.content.description"
                                        rows="3"
                                        class="mt-1.5 w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white"
                                    ></textarea>
                                </label>
                            @endif

                            @if (isset($section['content']['body']))
                                <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-300">
                                    Conteúdo
                                    <textarea
                                        wire:model.live.debounce.500ms="sections.{{ $index }}.content.body"
                                        rows="5"
                                        class="mt-1.5 w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white"
                                    ></textarea>
                                </label>
                            @endif

                            @if (isset($section['content']['url']))
                                <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-300">
                                    URL
                                    <input
                                        wire:model.live.debounce.500ms="sections.{{ $index }}.content.url"
                                        class="mt-1.5 w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white"
                                    >
                                </label>
                            @endif

                            @if (isset($section['content']['alt']))
                                <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-300">
                                    Texto alternativo
                                    <input
                                        wire:model.live.debounce.500ms="sections.{{ $index }}.content.alt"
                                        class="mt-1.5 w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white"
                                    >
                                </label>
                            @endif

                            @if (isset($section['content']['button_label']))
                                <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-300">
                                    Texto do botão
                                    <input
                                        wire:model.live.debounce.500ms="sections.{{ $index }}.content.button_label"
                                        class="mt-1.5 w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white"
                                    >
                                </label>
                            @endif

                            @if (isset($section['content']['button_url']))
                                <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-300">
                                    URL do botão
                                    <input
                                        wire:model.live.debounce.500ms="sections.{{ $index }}.content.button_url"
                                        class="mt-1.5 w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white"
                                    >
                                </label>
                            @endif

                            <label class="flex items-center gap-2 text-xs font-medium text-zinc-600 dark:text-zinc-300">
                                <input
                                    type="checkbox"
                                    wire:model.live="sections.{{ $index }}.is_visible"
                                    class="rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500 dark:border-zinc-600 dark:bg-zinc-800"
                                >
                                Visível
                            </label>
                        </div>
                    </details>
                @endforeach
            </div>

            {{-- ===================== TEMA GLOBAL ===================== --}}
            <div class="mt-8 border-t border-zinc-200 pt-5 dark:border-zinc-700">
                <p class="text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Tema global</p>

                <div class="mt-4 space-y-3">
                    @foreach ([
                        ['key' => 'primary', 'label' => 'Primária'],
                        ['key' => 'secondary', 'label' => 'Secundária'],
                        ['key' => 'background', 'label' => 'Fundo'],
                        ['key' => 'text', 'label' => 'Texto'],
                    ] as $color)
                        <label class="flex items-center justify-between gap-3 rounded-xl border border-zinc-200 p-2.5 dark:border-zinc-700">
                            <span class="flex items-center gap-2.5">
                                <span
                                    class="size-6 shrink-0 rounded-full border border-black/5 shadow-sm"
                                    style="background-color: {{ $theme[$color['key']] ?? '#e5e7eb' }}"
                                    aria-hidden="true"
                                ></span>
                                <span class="text-xs font-medium text-zinc-600 dark:text-zinc-300">{{ $color['label'] }}</span>
                            </span>
                            <span class="flex items-center gap-2">
                                <span class="hidden font-mono text-[10px] text-zinc-400 sm:inline dark:text-zinc-500">{{ $theme[$color['key']] ?? '—' }}</span>
                                <input type="color" wire:model.live="theme.{{ $color['key'] }}" class="size-7 shrink-0 cursor-pointer rounded-md border-0 bg-transparent p-0">
                            </span>
                        </label>
                    @endforeach

                    <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-300">
                        Largura do conteúdo
                        <input
                            wire:model.live="theme.content_width"
                            class="mt-1.5 w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white"
                        >
                    </label>
                </div>
            </div>
        </aside>
    </div>
</div>
