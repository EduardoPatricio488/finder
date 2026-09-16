@use('App\Support\WebsiteBuilder\ElementRegistry')
@use('App\Support\WebsiteBuilder\WebsiteTemplates')
@use('App\Support\WebsiteBuilder\SiteSectionDocument')
@use('App\Models\SiteSection')
@use('Illuminate\Support\Str')

@php
    $score = $this->readinessScore();
    $scoreColor = $score >= 80 ? '#10b981' : ($score >= 50 ? '#f59e0b' : '#ef4444');
    $aiPageOptions = ['home' => 'Home', 'about' => 'Sobre nós', 'services' => 'Serviços', 'products' => 'Produtos', 'contact' => 'Contactos'];
@endphp

<div
    class="min-h-screen bg-slate-100 text-slate-900"
    x-data="{ sectionDrag: null, insertAt: null }"
    x-on:open-builder-preview.window="window.open($event.detail.url, '_blank', 'noopener,noreferrer')"
    x-on:keydown.window="if((event.metaKey || event.ctrlKey) && event.key === 's') { event.preventDefault(); $wire.save(); }"
>
    {{-- ===== Top bar ===== --}}
    <header class="sticky top-0 z-50 border-b border-slate-200 bg-white/95 backdrop-blur">
        <div class="flex min-h-16 items-center gap-3 px-4 lg:px-6">
            <a href="{{ route('dashboard') }}" class="rounded-xl px-2 py-2 text-sm font-semibold hover:bg-slate-100">← <span class="hidden sm:inline">Dashboard</span></a>

            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2 text-sm">
                    <b class="truncate">{{ $site->name }}</b>
                    <span class="rounded-full px-2 py-0.5 text-[11px] font-bold {{ $site->is_published ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">{{ $site->is_published ? 'Online' : 'Rascunho' }}</span>
                    <span class="text-slate-300">/</span>
                    <span class="truncate text-slate-500">{{ $this->currentPage()['name'] ?? 'Página' }}</span>
                </div>
                <div class="mt-0.5 flex items-center gap-1.5 text-xs text-slate-400">
                    <span class="inline-block h-1.5 w-1.5 rounded-full {{ $dirty ? 'animate-pulse bg-amber-400' : 'bg-emerald-400' }}"></span>
                    <span wire:loading.remove wire:target="save,updateSelectedElement,updateSelectedElementSetting">{{ $statusMessage }}</span>
                    <span wire:loading wire:target="save,updateSelectedElement,updateSelectedElementSetting">A guardar…</span>
                    <span class="hidden text-slate-300 sm:inline">·</span>
                    <span class="hidden sm:inline">⌘S para guardar</span>
                </div>
            </div>

            {{-- Prontidão para publicar --}}
            <div class="hidden items-center gap-2 rounded-xl border px-3 py-1.5 md:flex" title="Prontidão do website para publicação">
                <svg viewBox="0 0 36 36" class="h-6 w-6 -rotate-90">
                    <path class="opacity-20" d="M18 2a16 16 0 1 1 0 32 16 16 0 0 1 0-32" fill="none" stroke="{{ $scoreColor }}" stroke-width="4"></path>
                    <path d="M18 2a16 16 0 1 1 0 32 16 16 0 0 1 0-32" fill="none" stroke="{{ $scoreColor }}" stroke-width="4" stroke-dasharray="{{ $score }}, 100"></path>
                </svg>
                <span class="text-xs font-bold" style="color: {{ $scoreColor }}">{{ $score }}%</span>
            </div>

            <div class="hidden rounded-xl border bg-slate-50 p-1 md:flex">
                @foreach(['desktop'=>'Computador','tablet'=>'Tablet','mobile'=>'Telemóvel'] as $key=>$label)
                    <button wire:click="$set('device','{{ $key }}')" class="rounded-lg px-3 py-1.5 text-xs font-semibold transition {{ $device === $key ? 'bg-white shadow-sm' : 'text-slate-500' }}">{{ $label }}</button>
                @endforeach
            </div>

            <button wire:click="$set('showVersions',true)" class="hidden rounded-xl border px-3 py-2 text-sm font-semibold sm:block">Histórico</button>
            <button wire:click="openPreview" class="rounded-xl border px-3 py-2 text-sm font-semibold">Pré-visualizar</button>
            <button wire:click="runPublishChecks" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white transition hover:bg-slate-700">{{ $site->is_published ? 'Publicar alterações' : 'Publicar' }}</button>
        </div>
    </header>

    <div class="grid min-h-[calc(100vh-4rem)] lg:grid-cols-[270px_minmax(0,1fr)_360px]">

        {{-- ===== Sidebar esquerda ===== --}}
        <aside class="hidden border-r bg-white lg:flex lg:flex-col">
            <div class="border-b p-3">
                <div class="grid grid-cols-3 gap-1 rounded-xl bg-slate-100 p-1">
                    @foreach(['pages'=>'Páginas','elements'=>'Secções','design'=>'Design'] as $key=>$label)
                        <button wire:click="$set('panel','{{ $key }}')" class="rounded-lg px-2 py-2 text-[11px] font-bold transition {{ $panel === $key ? 'bg-white shadow-sm' : 'text-slate-500' }}">{{ $label }}</button>
                    @endforeach
                </div>
            </div>

            <div class="flex-1 overflow-y-auto">
                @if($panel === 'pages')
                    <div class="p-3">
                        <div class="mb-3 flex items-center justify-between">
                            <div><b class="text-sm">Páginas</b><div class="text-[11px] text-slate-500">Estrutura e navegação</div></div>
                            <button wire:click="createPage" class="rounded-lg bg-slate-900 px-2.5 py-1.5 text-xs font-bold text-white">+ Página</button>
                        </div>
                        <div class="space-y-1">
                            @foreach($pages as $page)
                                <div class="group flex items-center gap-1 rounded-xl border px-2 py-2 transition {{ $pageId === $page['id'] ? 'border-blue-200 bg-blue-50' : 'border-transparent hover:border-slate-200' }}">
                                    <button wire:click="loadPage({{ $page['id'] }})" class="min-w-0 flex-1 text-left">
                                        <div class="flex items-center gap-1.5">
                                            <span class="truncate text-sm font-semibold">{{ $page['name'] }}</span>
                                            @if($page['is_homepage'])<span class="rounded-full bg-slate-900 px-1.5 py-0.5 text-[9px] font-bold text-white">HOME</span>@endif
                                        </div>
                                        <div class="truncate text-[10px] text-slate-500">/{{ $page['slug'] }} · {{ $page['status'] === 'draft' ? 'Rascunho' : 'Publicada' }}</div>
                                    </button>
                                    @if(!$page['is_homepage'])
                                        <button wire:click="setHomepage({{ $page['id'] }})" class="rounded px-2 py-1 text-xs opacity-0 transition group-hover:opacity-100" title="Definir como homepage">⌂</button>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-4 grid grid-cols-2 gap-2">
                            <button wire:click="duplicatePage" class="rounded-xl border px-2 py-2 text-xs font-bold">Duplicar</button>
                            <button wire:click="deletePage" wire:confirm="Eliminar esta página?" class="rounded-xl border border-red-100 px-2 py-2 text-xs font-bold text-red-600">Eliminar</button>
                        </div>
                        <div class="mt-5 rounded-2xl bg-slate-900 p-4 text-white">
                            <b class="text-xs">Começa mais rápido</b>
                            <p class="mt-1 text-[11px] leading-4 text-slate-300">Template profissional ou criação por IA em poucos passos.</p>
                            <div class="mt-3 grid grid-cols-2 gap-2">
                                <button wire:click="$set('showTemplates',true)" class="rounded-lg bg-white/10 py-2 text-[11px] font-bold transition hover:bg-white/20">Templates</button>
                                <button wire:click="openAiWizard" class="rounded-lg bg-white py-2 text-[11px] font-bold text-slate-900 transition hover:bg-slate-100">✨ Criar com IA</button>
                            </div>
                        </div>
                    </div>
                @elseif($panel === 'elements')
                    <div class="p-3">
                        <b class="text-sm">Secções</b>
                        <p class="mb-3 text-[11px] text-slate-500">Blocos prontos para construir a página.</p>
                        <input
                            type="search"
                            wire:model.live.debounce.200ms="sectionQuery"
                            placeholder="Pesquisar secções…"
                            class="mb-3 w-full rounded-xl border-slate-200 text-xs"
                        >
                        @forelse($this->sectionCatalog() as $category => $items)
                            <div class="mb-4">
                                <div class="mb-1.5 px-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ $category }}</div>
                                @foreach($items as $type => $meta)
                                    <button wire:click="addSection('{{ $type }}')" class="mb-1.5 flex w-full items-start justify-between gap-2 rounded-xl border bg-white px-3 py-2.5 text-left transition hover:border-blue-300 hover:shadow-sm">
                                        <span>
                                            <span class="block text-xs font-bold">{{ $meta['label'] }}</span>
                                            <span class="block text-[10px] leading-tight text-slate-400">{{ $meta['description'] }}</span>
                                        </span>
                                        <span class="mt-0.5 shrink-0 text-blue-600">+</span>
                                    </button>
                                @endforeach
                            </div>
                        @empty
                            <p class="text-xs text-slate-400">Sem resultados para "{{ $sectionQuery }}".</p>
                        @endforelse
                    </div>
                @else
                    <div class="p-3">
                        <b class="text-sm">Design global</b>
                        <p class="mb-4 text-[11px] text-slate-500">Uma identidade consistente em todo o website.</p>
                        @foreach(['primary'=>'Cor principal','secondary'=>'Cor secundária','background'=>'Fundo','text'=>'Cor do texto'] as $key=>$label)
                            <label class="mb-3 flex items-center gap-2 text-xs font-bold">
                                <input type="color" wire:change="updateTheme('{{ $key }}',$event.target.value)" value="{{ $theme[$key] ?? '#000000' }}" class="h-8 w-8 shrink-0 cursor-pointer rounded-lg border p-0.5">
                                <span class="flex-1">
                                    {{ $label }}
                                    <input wire:change="updateTheme('{{ $key }}',$event.target.value)" value="{{ $theme[$key] ?? '' }}" class="mt-1 w-full rounded-xl border-slate-200 text-xs font-normal">
                                </span>
                            </label>
                        @endforeach
                        <label class="mb-3 block text-xs font-bold">Tipografia
                            <select wire:change="updateTheme('font_body',$event.target.value)" class="mt-1.5 w-full rounded-xl border-slate-200 text-sm">
                                @foreach(['Inter','Manrope','DM Sans','Plus Jakarta Sans'] as $font)
                                    <option @selected(($theme['font_body'] ?? 'Inter') === $font)>{{ $font }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="mb-2 block text-xs font-bold">Raio dos cantos
                            <input wire:change="updateTheme('radius',$event.target.value)" value="{{ $theme['radius'] ?? '1rem' }}" class="mt-1.5 w-full rounded-xl border-slate-200 text-sm">
                        </label>
                        <div class="mb-4 flex h-16 items-center justify-center border bg-slate-50 text-[10px] text-slate-400" style="border-radius: {{ $theme['radius'] ?? '1rem' }}">Pré-visualização do raio</div>
                        <button wire:click="save" class="w-full rounded-xl bg-slate-900 py-2.5 text-xs font-bold text-white">Guardar design</button>
                    </div>
                @endif
            </div>
        </aside>

        {{-- ===== Canvas ===== --}}
        <main class="min-w-0 overflow-auto bg-slate-100">
            <div class="flex items-center justify-between border-b bg-white px-4 py-2 lg:hidden">
                <select wire:change="loadPage($event.target.value)" class="rounded-lg border-slate-200 text-sm font-bold">
                    @foreach($pages as $page)<option value="{{ $page['id'] }}" @selected($pageId===$page['id'])>{{ $page['name'] }}</option>@endforeach
                </select>
                <div class="flex gap-1">
                    @foreach(['desktop','tablet','mobile'] as $key)
                        <button wire:click="$set('device','{{ $key }}')" class="rounded-lg px-2 py-1 text-xs {{ $device===$key?'bg-slate-900 text-white':'bg-slate-100' }}">{{ ucfirst($key) }}</button>
                    @endforeach
                </div>
            </div>

            <div class="flex min-h-[calc(100vh-7rem)] justify-center p-4 md:p-8">
                <div class="w-full overflow-hidden rounded-2xl bg-white shadow-xl ring-1 ring-slate-200 transition-all {{ $device === 'mobile' ? 'max-w-[390px]' : ($device === 'tablet' ? 'max-w-[768px]' : 'max-w-[1280px]') }}" style="--finder-primary:{{ $theme['primary'] ?? '#635bff' }};--finder-secondary:{{ $theme['secondary'] ?? '#111827' }};--finder-bg:{{ $theme['background'] ?? '#ffffff' }};--finder-text:{{ $theme['text'] ?? '#111827' }}">
                    <div class="flex items-center justify-between border-b px-5 py-3 text-xs text-slate-400">
                        <span>{{ $this->currentPage()['name'] ?? 'Página' }}</span>
                        <span>{{ ucfirst($device) }}</span>
                    </div>

                    @forelse($sections as $i=>$section)
                        @php($sectionModel = new SiteSection($section))
                        @php($sectionModel->id = (int) ($section['id'] ?? 0))
                        @php($document = SiteSectionDocument::fromSection($sectionModel))

                        <section
                            draggable="true"
                            x-on:dragstart="sectionDrag={{ $i }}"
                            x-on:dragover.prevent
                            x-on:drop="if(sectionDrag!==null && sectionDrag!=={{ $i }}) { $wire.moveSection(sectionDrag, sectionDrag < {{ $i }} ? 'down' : 'up'); sectionDrag=null }"
                            wire:click="selectSection({{ $i }})"
                            wire:key="builder-section-{{ $section['id'] ?? 'new-'.$i }}"
                            class="group relative border-2 transition {{ $selectedSection === $i ? 'border-blue-500 ring-2 ring-blue-100' : 'border-transparent hover:border-blue-200' }} {{ !$section['is_visible'] ? 'opacity-40' : '' }}"
                        >
                            <div class="absolute left-3 top-3 z-20 hidden rounded-lg bg-slate-900/80 px-2 py-1 text-[10px] font-bold text-white group-hover:block">{{ $section['label'] ?: Str::headline($section['type']) }}</div>

                            <div class="absolute right-3 top-3 z-20 hidden gap-1 rounded-xl border bg-white p-1 shadow-lg group-hover:flex">
                                <button wire:click.stop="moveSection({{ $i }},'up')" title="Mover para cima">↑</button>
                                <button wire:click.stop="moveSection({{ $i }},'down')" title="Mover para baixo">↓</button>
                                <button wire:click.stop="duplicateSection({{ $i }})" title="Duplicar">⧉</button>
                                <button wire:click.stop="regenerateSectionWithAi({{ $i }})" wire:loading.attr="disabled" wire:target="regenerateSectionWithAi({{ $i }})" title="Regenerar conteúdo com IA">✨</button>
                                <button wire:click.stop="toggleSection({{ $i }})" title="Mostrar/ocultar">{{ $section['is_visible'] ? '◌' : '●' }}</button>
                                <button wire:click.stop="deleteSection({{ $i }})" class="text-red-600" title="Eliminar">×</button>
                            </div>

                            <div wire:loading.class="opacity-40" wire:target="regenerateSectionWithAi({{ $i }})" class="min-h-28 px-6 py-10 transition md:px-12">
                                @if(is_array($section['settings']['builder_document'] ?? null))
                                    @include('livewire.builder-document-renderer', [
    'document' => $section['settings']['builder_document'],
    'device' => $device,
    'selectedElementId' => $selectedElementId,
    'sectionIndex' => $i,
])
                                @else
                                    @switch($section['type'])
                                        @case('hero')<div class="mx-auto max-w-3xl text-center"><h1 class="text-4xl font-black tracking-tight md:text-6xl">{{ $section['content']['title'] ?? 'Título principal' }}</h1><p class="mt-4 text-lg text-slate-600">{{ $section['content']['subtitle'] ?? '' }}</p><span class="mt-7 inline-block rounded-xl px-5 py-3 text-sm font-bold text-white" style="background:var(--finder-primary)">{{ $section['content']['button_label'] ?? 'Saber mais' }}</span></div>@break
                                        @case('text')<div class="mx-auto max-w-3xl"><h2 class="text-3xl font-black">{{ $section['content']['title'] ?? 'Título' }}</h2><p class="mt-4 whitespace-pre-line leading-7 text-slate-600">{{ $section['content']['body'] ?? 'Texto da secção.' }}</p></div>@break
                                        @case('image')<div class="mx-auto flex min-h-56 max-w-4xl items-center justify-center overflow-hidden rounded-2xl bg-slate-100 text-sm text-slate-400">@if(!empty($section['content']['url']))<img src="{{ $section['content']['url'] }}" alt="{{ $section['content']['alt'] ?? '' }}" class="max-h-[500px] w-full object-cover">@else Adiciona uma imagem @endif</div>@break
                                        @case('button')<div class="text-center"><span class="inline-block rounded-xl px-5 py-3 text-sm font-bold text-white" style="background:var(--finder-primary)">{{ $section['content']['label'] ?? 'Botão' }}</span></div>@break
                                        @case('feature_grid')<div class="mx-auto max-w-5xl"><h2 class="text-center text-3xl font-black">{{ $section['content']['title'] ?? 'Benefícios' }}</h2><div class="mt-8 grid gap-4 md:grid-cols-3">@foreach(($section['content']['items'] ?? []) as $item)<div class="rounded-2xl border p-6"><b>{{ $item['title'] ?? 'Benefício' }}</b><p class="mt-2 text-sm text-slate-500">{{ $item['description'] ?? '' }}</p></div>@endforeach</div></div>@break
                                        @case('testimonials')<div class="mx-auto max-w-4xl"><h2 class="text-center text-3xl font-black">{{ $section['content']['title'] ?? 'Testemunhos' }}</h2><div class="mt-7 grid gap-4 md:grid-cols-2">@foreach(($section['content']['items'] ?? []) as $item)<blockquote class="rounded-2xl bg-slate-50 p-6"><p class="text-slate-600">"{{ $item['quote'] ?? '' }}"</p><b class="mt-4 block text-sm">{{ $item['name'] ?? 'Cliente' }}</b></blockquote>@endforeach</div></div>@break
                                        @case('faq')<div class="mx-auto max-w-3xl"><h2 class="text-3xl font-black">{{ $section['content']['title'] ?? 'Perguntas frequentes' }}</h2><div class="mt-6 space-y-3">@foreach(($section['content']['items'] ?? []) as $item)<div class="rounded-xl border p-4"><b>{{ $item['question'] ?? 'Pergunta?' }}</b><p class="mt-2 text-sm text-slate-500">{{ $item['answer'] ?? '' }}</p></div>@endforeach</div></div>@break
                                        @case('pricing')<div class="mx-auto max-w-5xl"><h2 class="text-center text-3xl font-black">{{ $section['content']['title'] ?? 'Planos' }}</h2><div class="mt-8 grid gap-4 md:grid-cols-3">@foreach(($section['content']['items'] ?? []) as $item)<div class="rounded-2xl border p-6"><b>{{ $item['name'] ?? 'Plano' }}</b><div class="mt-3 text-3xl font-black">{{ $item['price'] ?? '' }}</div><p class="mt-2 text-sm text-slate-500">{{ $item['description'] ?? '' }}</p></div>@endforeach</div></div>@break
                                        @case('cta')<div class="mx-auto max-w-4xl rounded-3xl p-10 text-center text-white" style="background:var(--finder-secondary)"><h2 class="text-3xl font-black">{{ $section['content']['title'] ?? 'Vamos falar?' }}</h2><p class="mt-3 text-white/70">{{ $section['content']['description'] ?? '' }}</p></div>@break
                                        @default<div class="mx-auto max-w-4xl rounded-2xl border border-dashed p-10 text-center"><div class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ $section['label'] }}</div><div class="mt-2 text-lg font-bold">{{ Str::headline($section['type']) }}</div></div>
                                    @endswitch
                                @endif
                            </div>
                        </section>

                        {{-- "+" flutuante para inserir uma secção neste ponto --}}
                        <div class="group/insert relative h-0" x-data="{ open: false }">
                            <div class="absolute inset-x-0 -top-3 z-10 flex justify-center opacity-0 transition group-hover/insert:opacity-100" x-on:mouseenter="open = true" x-on:mouseleave="open = false">
                                <button type="button" class="flex h-6 w-6 items-center justify-center rounded-full bg-slate-900 text-xs font-bold text-white shadow" title="Inserir secção aqui">+</button>
                                <div x-show="open" x-transition class="absolute top-6 z-30 grid w-64 grid-cols-2 gap-1 rounded-xl border bg-white p-2 shadow-2xl" style="display: none;">
                                    @foreach(['hero'=>'Hero','text'=>'Texto','image'=>'Imagem','feature_grid'=>'Benefícios','testimonials'=>'Testemunhos','faq'=>'FAQ','cta'=>'CTA','contact_form'=>'Formulário'] as $type=>$label)
                                        <button wire:click="insertSectionAfter({{ $i }}, '{{ $type }}')" class="rounded-lg px-2 py-1.5 text-left text-[11px] font-semibold hover:bg-slate-100">{{ $label }}</button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="flex min-h-[500px] items-center justify-center p-8">
                            <div class="max-w-md text-center">
                                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-blue-50 text-2xl">✦</div>
                                <h2 class="mt-5 text-2xl font-black">Começa a criar o teu website</h2>
                                <p class="mt-2 text-sm leading-6 text-slate-500">Escolhe um template, usa a IA ou adiciona uma secção.</p>
                                <div class="mt-5 flex justify-center gap-2">
                                    <button wire:click="$set('showTemplates',true)" class="rounded-xl border px-4 py-2 text-sm font-bold">Templates</button>
                                    <button wire:click="openAiWizard" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white">✨ Criar com IA</button>
                                </div>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </main>

        {{-- ===== Inspector ===== --}}
        <aside class="hidden border-l bg-white lg:flex lg:flex-col">
            @php($selected = $selectedSection !== null ? ($sections[$selectedSection] ?? null) : null)
            @php($selectedModel = $selected ? new SiteSection($selected) : null)
            @php($selectedModel?->setAttribute('id', (int) ($selected['id'] ?? 0)))
            @php($selectedDocument = $selectedModel ? SiteSectionDocument::fromSection($selectedModel) : null)
            @php($selectedElement = $selectedDocument ? collect($selectedDocument['nodes'][0]['children'] ?? [])->firstWhere('id', $selectedElementId) : null)
            @php($elementStyles = $this->selectedElementStyles())

            <div class="border-b px-4 py-3">
                <b class="text-sm">{{ $selected ? ($selected['label'] ?: Str::headline($selected['type'])) : 'Inspector' }}</b>
                <div class="text-[11px] text-slate-500">Seleciona uma secção ou elemento para editar.</div>
            </div>

            @if($selected)
                <div class="border-b bg-slate-50 px-4 py-2">
                    <div class="grid grid-cols-3 gap-1 rounded-xl bg-white p-1">
                        @foreach(['content'=>'Conteúdo','style'=>'Estilo','seo'=>'SEO'] as $key=>$label)
                            <button wire:click="$set('inspectorTab','{{ $key }}')" class="rounded-lg px-2 py-1.5 text-[11px] font-bold transition {{ $inspectorTab === $key ? 'bg-slate-900 text-white' : 'text-slate-500' }}">{{ $label }}</button>
                        @endforeach
                    </div>
                </div>

                <div class="flex-1 space-y-5 overflow-y-auto p-4">

                    @if($inspectorTab === 'content')
                        <label class="block text-xs font-bold">Nome da secção
                            <input wire:model.live="sections.{{ $selectedSection }}.label" wire:change="$set('dirty',true)" class="mt-1.5 w-full rounded-xl border-slate-200 text-sm">
                        </label>

                        <button wire:click="regenerateSectionWithAi({{ $selectedSection }})" wire:loading.attr="disabled" wire:target="regenerateSectionWithAi({{ $selectedSection }})" class="flex w-full items-center justify-center gap-2 rounded-xl border border-blue-200 bg-blue-50 py-2 text-xs font-bold text-blue-700 transition hover:bg-blue-100 disabled:opacity-50">
                            <span wire:loading.remove wire:target="regenerateSectionWithAi({{ $selectedSection }})">✨ Regenerar conteúdo com IA</span>
                            <span wire:loading wire:target="regenerateSectionWithAi({{ $selectedSection }})">A gerar…</span>
                        </button>

                        <div>
                            <div class="flex items-center justify-between"><b class="text-xs">Elementos</b><span class="text-[10px] text-slate-400">Clica para editar</span></div>
                            <div class="mt-2 space-y-1">
                                @foreach(($selectedDocument['nodes'][0]['children'] ?? []) as $element)
                                    <div class="flex items-center gap-1 rounded-xl border p-1 {{ $selectedElementId === $element['id'] ? 'border-blue-300 bg-blue-50' : 'bg-slate-50' }}">
                                        <button wire:click="selectElement('{{ $element['id'] }}')" class="flex min-w-0 flex-1 items-center gap-1 rounded-lg px-1.5 py-1 text-left">
                                            <span class="min-w-0 flex-1 truncate text-xs font-bold">{{ ElementRegistry::get($element['type'])['label'] }}</span>
                                            <span class="text-[10px] text-slate-400">{{ $element['type'] }}</span>
                                        </button>
                                        @if($selectedElementId === $element['id'])
                                            <button wire:click.stop="duplicateElement('{{ $element['id'] }}')" class="px-1.5 py-1 text-xs" title="Duplicar">⧉</button>
                                            <button wire:click.stop="moveElement('{{ $element['id'] }}', -1)" class="px-1.5 py-1 text-xs" title="Mover para cima">↑</button>
                                            <button wire:click.stop="moveElement('{{ $element['id'] }}', 1)" class="px-1.5 py-1 text-xs" title="Mover para baixo">↓</button>
                                            <button wire:click.stop="removeElement('{{ $element['id'] }}')" class="px-1.5 py-1 text-xs text-red-600" title="Remover">×</button>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        @if($selectedElement)
                            <div class="rounded-2xl border border-blue-100 bg-blue-50/50 p-3">
                                <div class="mb-2 flex items-center justify-between"><b class="text-xs">Editar elemento</b><button wire:click="$set('selectedElementId',null)" class="text-xs text-slate-400">×</button></div>
                                @foreach($selectedElement['content'] ?? [] as $key=>$value)
                                    @if(is_string($value) && in_array($key, ['text','label','url','alt','caption','title','author','button_label','button_url'], true))
                                        <label class="mt-3 block text-[11px] font-bold capitalize">{{ str_replace('_',' ',$key) }}
                                            <textarea wire:change="updateSelectedElement('{{ $key }}',$event.target.value)" rows="{{ strlen($value) > 120 ? 4 : 2 }}" class="mt-1 w-full rounded-xl border-slate-200 bg-white text-xs">{{ $value }}</textarea>
                                        </label>
                                    @endif
                                @endforeach
                            </div>
                        @endif

                        <div>
                            <div class="flex items-center justify-between"><b class="text-xs">Adicionar elemento</b><span class="text-[10px] text-slate-400">Blocos internos</span></div>
                            <input type="search" wire:model.live.debounce.200ms="elementQuery" placeholder="Pesquisar elementos…" class="mt-2 w-full rounded-xl border-slate-200 text-xs">
                            @foreach($this->elementCatalog() as $category => $items)
                                <div class="mt-2">
                                    <div class="mb-1 px-0.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ $category }}</div>
                                    <div class="grid grid-cols-2 gap-2">
                                        @foreach($items as $type => $meta)
                                            <button wire:click="addElement('{{ $type }}')" class="rounded-xl border px-2 py-2 text-left text-[11px] font-semibold transition hover:border-blue-300">{{ $meta['label'] }}</button>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div>
                            <b class="text-xs">Conteúdo da secção</b>
                            @foreach($selected['content'] ?? [] as $key=>$value)
                                @if(is_string($value))
                                    <label class="mt-3 block text-[11px] font-bold capitalize">{{ str_replace('_',' ',$key) }}
                                        <textarea wire:model.live="sections.{{ $selectedSection }}.content.{{ $key }}" wire:change="$set('dirty',true)" rows="{{ strlen($value)>100 ? 4 : 2 }}" class="mt-1 w-full rounded-xl border-slate-200 text-xs"></textarea>
                                    </label>
                                @endif
                            @endforeach
                        </div>
                    @endif

                    @if($inspectorTab === 'style')
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 text-[11px] text-slate-500">
                            A editar o estilo para <b class="text-slate-700">{{ ucfirst($device) }}</b>. Muda o dispositivo na barra superior para afinar cada breakpoint.
                        </div>

                        @if($selectedElement)
                            <label class="block text-[11px] font-bold">Alinhamento
                                <div class="mt-1.5 grid grid-cols-3 gap-1 rounded-xl border p-1">
                                    @foreach(['left'=>'Esquerda','center'=>'Centro','right'=>'Direita'] as $value=>$label)
                                        <button wire:click="updateSelectedElementSetting('align','{{ $value }}')" class="rounded-lg py-1.5 text-[11px] font-semibold {{ ($elementStyles['align'] ?? 'left') === $value ? 'bg-slate-900 text-white' : 'text-slate-500' }}">{{ $label }}</button>
                                    @endforeach
                                </div>
                            </label>
                            <label class="block text-[11px] font-bold">Tamanho da fonte (ex: 1rem, 18px)
                                <input wire:change="updateSelectedElementSetting('font_size',$event.target.value)" value="{{ $elementStyles['font_size'] ?? '' }}" class="mt-1.5 w-full rounded-xl border-slate-200 text-xs">
                            </label>
                            <label class="block text-[11px] font-bold">Espaçamento interno (padding)
                                <input wire:change="updateSelectedElementSetting('padding',$event.target.value)" value="{{ $elementStyles['padding'] ?? '' }}" class="mt-1.5 w-full rounded-xl border-slate-200 text-xs">
                            </label>
                            <label class="block text-[11px] font-bold">Margem
                                <input wire:change="updateSelectedElementSetting('margin',$event.target.value)" value="{{ $elementStyles['margin'] ?? '' }}" class="mt-1.5 w-full rounded-xl border-slate-200 text-xs">
                            </label>
                            <label class="block text-[11px] font-bold">Largura (ex: 100%, 320px)
                                <input wire:change="updateSelectedElementSetting('width',$event.target.value)" value="{{ $elementStyles['width'] ?? '' }}" class="mt-1.5 w-full rounded-xl border-slate-200 text-xs">
                            </label>
                            <label class="flex items-center justify-between text-[11px] font-bold">
                                Visível neste dispositivo
                                <button wire:click="updateSelectedElementSetting('visibility', '{{ ($elementStyles['visibility'] ?? 'visible') === 'visible' ? 'hidden' : 'visible' }}')" class="rounded-full px-3 py-1 text-[10px] font-bold {{ ($elementStyles['visibility'] ?? 'visible') === 'visible' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-500' }}">
                                    {{ ($elementStyles['visibility'] ?? 'visible') === 'visible' ? 'Visível' : 'Oculto' }}
                                </button>
                            </label>
                        @else
                            <p class="text-xs text-slate-400">Seleciona um elemento dentro da secção (aba Conteúdo) para editar o estilo responsivo.</p>
                        @endif
                    @endif

                    @if($inspectorTab === 'seo')
                        <b class="text-xs">SEO da página</b>
                        <label class="mt-3 block text-[11px] font-bold">Título SEO
                            <input wire:change="updatePageSeo('title',$event.target.value)" value="{{ $pageSeo['title'] ?? '' }}" placeholder="Título para motores de pesquisa" class="mt-1 w-full rounded-xl border-slate-200 text-xs">
                        </label>
                        <label class="mt-3 block text-[11px] font-bold">Meta descrição
                            <textarea wire:change="updatePageSeo('description',$event.target.value)" rows="3" placeholder="Descrição curta da página" class="mt-1 w-full rounded-xl border-slate-200 text-xs">{{ $pageSeo['description'] ?? '' }}</textarea>
                        </label>
                        <div class="mt-4 rounded-xl border p-3 text-[11px] text-slate-500">
                            <div class="truncate text-sm text-blue-700">{{ $pageSeo['title'] ?: ($this->currentPage()['name'] ?? 'Título da página') }}</div>
                            <div class="text-[10px] text-emerald-700">{{ url('/'.$site->slug.'/'.($this->currentPage()['slug'] ?? '')) }}</div>
                            <div class="mt-1">{{ Str::limit($pageSeo['description'] ?? 'Sem meta descrição definida.', 140) }}</div>
                        </div>
                    @endif

                    <button wire:click="save" class="sticky bottom-0 w-full rounded-xl bg-slate-900 py-2.5 text-xs font-bold text-white">{{ $dirty ? 'Guardar alterações' : 'Tudo guardado' }}</button>
                </div>
            @else
                <div class="p-6 text-center text-sm text-slate-500">Seleciona uma secção no canvas para abrir o inspector.</div>
            @endif
        </aside>
    </div>

    {{-- ===== Modal: Templates ===== --}}
    @if($showTemplates)
        <div class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/50 p-4" wire:click.self="$set('showTemplates',false)">
            <div class="max-h-[85vh] w-full max-w-4xl overflow-auto rounded-3xl bg-white p-6 shadow-2xl">
                <div class="flex justify-between">
                    <div><h2 class="text-2xl font-black">Templates profissionais</h2><p class="mt-1 text-sm text-slate-500">Estrutura pronta a personalizar.</p></div>
                    <button wire:click="$set('showTemplates',false)" class="text-2xl text-slate-400">×</button>
                </div>
                <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach(WebsiteTemplates::all() as $category=>$template)
                        <button wire:click="applyTemplate('{{ $category }}')" class="rounded-2xl border p-5 text-left transition hover:border-blue-300 hover:shadow-lg">
                            <b class="text-sm">{{ $template['name'] ?? Str::headline($category) }}</b>
                            <p class="mt-1 text-xs leading-5 text-slate-500">{{ $template['description'] ?? 'Website profissional pronto a editar.' }}</p>
                            <div class="mt-3 text-[10px] font-bold uppercase text-blue-600">{{ count($template['pages'] ?? []) }} páginas</div>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- ===== Modal: IA (wizard 3 passos) ===== --}}
    @if($showAi)
        <div class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/50 p-4" wire:click.self="closeAiWizard">
            <div class="w-full max-w-2xl rounded-3xl bg-white p-6 shadow-2xl">
                <div class="flex justify-between">
                    <div>
                        <h2 class="text-2xl font-black">Criar website com IA</h2>
                        <p class="mt-1 text-sm text-slate-500">3 passos rápidos e a IA monta a estrutura por ti.</p>
                    </div>
                    <button wire:click="closeAiWizard" class="text-2xl text-slate-400">×</button>
                </div>

                <div class="mt-5 flex items-center gap-2">
                    @foreach([1=>'Negócio',2=>'Estilo',3=>'Páginas'] as $step=>$label)
                        <div class="flex flex-1 items-center gap-2">
                            <button wire:click="aiGoToStep({{ $step }})" class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-bold {{ $aiStep >= $step ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-400' }}">{{ $step }}</button>
                            <span class="text-[11px] font-semibold {{ $aiStep === $step ? 'text-slate-900' : 'text-slate-400' }}">{{ $label }}</span>
                            @if($step < 3)<div class="h-px flex-1 bg-slate-200"></div>@endif
                        </div>
                    @endforeach
                </div>

                <div class="mt-6 min-h-[220px]">
                    @if($aiStep === 1)
                        <label class="block text-xs font-bold">Nome do negócio
                            <input wire:model="aiBusinessName" placeholder="Ex: Barbearia Lisboa Norte" class="mt-1.5 w-full rounded-xl border-slate-200 text-sm">
                        </label>
                        <label class="mt-4 block text-xs font-bold">Tipo de negócio
                            <input wire:model="aiCategory" placeholder="Ex: barbearia, restaurante, consultoria…" class="mt-1.5 w-full rounded-xl border-slate-200 text-sm">
                        </label>
                        <label class="mt-4 block text-xs font-bold">Descreve o negócio, público e objetivo
                            <textarea wire:model="aiDescription" rows="6" class="mt-1.5 w-full rounded-2xl border-slate-200 p-3 text-sm" placeholder="Quero um site para uma barbearia moderna em Lisboa, focada em clientes jovens..."></textarea>
                        </label>
                    @elseif($aiStep === 2)
                        <b class="text-xs">Tom de comunicação</b>
                        <div class="mt-2 grid grid-cols-2 gap-2">
                            @foreach(['profissional'=>'Profissional','casual'=>'Descontraído','luxuoso'=>'Sofisticado','divertido'=>'Divertido'] as $value=>$label)
                                <button wire:click="$set('aiTone','{{ $value }}')" class="rounded-xl border px-3 py-3 text-left text-xs font-bold transition {{ $aiTone === $value ? 'border-slate-900 bg-slate-900 text-white' : 'hover:border-slate-300' }}">{{ $label }}</button>
                            @endforeach
                        </div>
                        <p class="mt-4 text-[11px] text-slate-500">O tom influencia a linguagem usada nos títulos, textos e chamadas à ação geradas.</p>
                    @else
                        <b class="text-xs">Que páginas queres gerar?</b>
                        <div class="mt-2 grid grid-cols-2 gap-2">
                            @foreach($aiPageOptions as $slug=>$label)
                                <button wire:click="aiTogglePage('{{ $slug }}')" class="rounded-xl border px-3 py-3 text-left text-xs font-bold transition {{ in_array($slug, $aiPagesSelected, true) ? 'border-blue-500 bg-blue-50 text-blue-700' : 'hover:border-slate-300' }}">{{ $label }}</button>
                            @endforeach
                        </div>
                        <div class="mt-4 rounded-xl border bg-slate-50 p-3 text-[11px] text-slate-500">
                            <b class="text-slate-700">{{ $aiBusinessName ?: $site->name }}</b> · {{ ucfirst($aiTone) }} · {{ count($aiPagesSelected) }} página(s)
                        </div>
                    @endif
                </div>

                <div class="mt-6 flex justify-between gap-2">
                    <button wire:click="aiGoToStep({{ $aiStep - 1 }})" @if($aiStep === 1) disabled @endif class="rounded-xl border px-4 py-2 text-sm font-bold disabled:opacity-30">Voltar</button>
                    @if($aiStep < 3)
                        <button wire:click="aiGoToStep({{ $aiStep + 1 }})" class="rounded-xl bg-slate-900 px-5 py-2 text-sm font-bold text-white">Seguinte</button>
                    @else
                        <button wire:click="generateWithAi" wire:loading.attr="disabled" wire:target="generateWithAi" class="rounded-xl bg-slate-900 px-5 py-2 text-sm font-bold text-white disabled:opacity-50">
                            <span wire:loading.remove wire:target="generateWithAi">✨ Criar website</span>
                            <span wire:loading wire:target="generateWithAi">A gerar…</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- ===== Modal: Publicar ===== --}}
    @if($showPublish)
        <div class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/50 p-4" wire:click.self="$set('showPublish',false)">
            <div class="w-full max-w-2xl rounded-3xl bg-white p-6 shadow-2xl">
                <div class="flex justify-between">
                    <div><h2 class="text-2xl font-black">Verificar e publicar</h2><p class="mt-1 text-sm text-slate-500">Erros impedem a publicação; recomendações não impedem.</p></div>
                    <button wire:click="$set('showPublish',false)" class="text-2xl text-slate-400">×</button>
                </div>
                <div class="mt-6 space-y-2">
                    @foreach($publishChecks as $check)
                        <div class="rounded-xl border p-3 {{ $check['level'] === 'error' ? 'border-red-200 bg-red-50' : ($check['level'] === 'success' ? 'border-emerald-200 bg-emerald-50' : 'border-amber-200 bg-amber-50') }}">
                            <b class="text-sm">{{ $check['label'] }}</b>
                            <p class="mt-1 text-xs text-slate-600">{{ $check['message'] }}</p>
                        </div>
                    @endforeach
                </div>
                <div class="mt-6 flex justify-end gap-2">
                    <button wire:click="$set('showPublish',false)" class="rounded-xl border px-4 py-2 text-sm font-bold">Fechar</button>
                    <button wire:click="publish" wire:loading.attr="disabled" class="rounded-xl bg-slate-900 px-5 py-2 text-sm font-bold text-white">Publicar agora</button>
                </div>
            </div>
        </div>
    @endif

    {{-- ===== Modal: Histórico ===== --}}
    @if($showVersions)
        <div class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/50 p-4" wire:click.self="$set('showVersions',false)">
            <div class="w-full max-w-2xl rounded-3xl bg-white p-6 shadow-2xl">
                <div class="flex justify-between">
                    <div><h2 class="text-2xl font-black">Histórico</h2><p class="mt-1 text-sm text-slate-500">Recupera versões anteriores do website.</p></div>
                    <button wire:click="$set('showVersions',false)" class="text-2xl text-slate-400">×</button>
                </div>
                <button wire:click="saveVersion('Versão manual')" class="mt-5 w-full rounded-xl bg-slate-900 py-2.5 text-sm font-bold text-white">Guardar versão atual</button>
                <div class="mt-5 max-h-80 space-y-2 overflow-auto">
                    @forelse($versions as $version)
                        <div class="flex items-center justify-between rounded-xl border p-3">
                            <div><b class="text-sm">v{{ $version['version_number'] }} · {{ $version['label'] }}</b><div class="text-xs text-slate-500">{{ $version['created_at'] }} @if($version['created_by']) · {{ $version['created_by'] }} @endif</div></div>
                            <button wire:click="restoreVersion({{ $version['id'] }})" wire:confirm="Restaurar esta versão? As alterações actuais serão substituídas." class="rounded-lg border px-3 py-1.5 text-xs font-bold">Restaurar</button>
                        </div>
                    @empty
                        <div class="rounded-xl bg-slate-50 p-5 text-center text-sm text-slate-500">Sem versões guardadas.</div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif
</div>
