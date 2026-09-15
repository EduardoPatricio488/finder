<div
    x-data="{
        add: false,
        settings: false,
        dragging: null,
        autosaveTimer: null,
        autosaveLabel: 'Guardado',
        queueAutosave() {
            this.autosaveLabel = 'Alterações por guardar';
            clearTimeout(this.autosaveTimer);
            this.autosaveTimer = setTimeout(() => {
                this.autosaveLabel = 'A guardar…';
                $wire.save().then(() => this.autosaveLabel = 'Guardado').catch(() => this.autosaveLabel = 'Erro ao guardar');
            }, 900);
        },
        dropSection(target) {
            if (this.dragging === null || this.dragging === target) return;
            const ids = [...this.$refs.canvas.querySelectorAll('[data-section-id]')].map(el => el.dataset.sectionId);
            const moved = ids.splice(this.dragging, 1)[0];
            ids.splice(target, 0, moved);
            $wire.reorderSections(ids);
            this.dragging = null;
        }
    }"
    x-on:builder-dirty.window="queueAutosave()"
    x-on:builder-saved.window="autosaveLabel = 'Guardado'"
    class="flex h-screen min-h-0 flex-col overflow-hidden bg-zinc-100 text-zinc-900 dark:bg-zinc-950 dark:text-zinc-100"
>
    <header class="z-40 shrink-0 border-b border-zinc-200 bg-white/95 backdrop-blur dark:border-zinc-800 dark:bg-zinc-900/95">
        <div class="flex min-h-16 items-center gap-3 px-4 lg:px-6">
            <a href="{{ route('admin.site.dashboard', $site) }}" class="shrink-0 rounded-xl border border-zinc-200 px-3 py-2 text-sm font-bold hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800">← Sair</a>
            <div class="hidden min-w-0 lg:block"><p class="text-[10px] font-black uppercase tracking-widest text-indigo-600">Finder · Website Builder</p><p class="truncate text-sm font-black">{{ $site->name }}</p></div>
            <div class="ml-auto flex items-center gap-2">
                <select wire:model.live="pageId" wire:change="selectPage($event.target.value)" class="max-w-44 rounded-xl border-zinc-200 bg-white text-sm font-bold dark:border-zinc-700 dark:bg-zinc-800">
                    @foreach($site->pages->sortBy('sort_order') as $page)
                        <option value="{{ $page->id }}">{{ $page->name }}{{ $page->is_homepage ? ' · Home' : '' }}</option>
                    @endforeach
                </select>
                <button type="button" wire:click="createPage" class="hidden rounded-xl border px-3 py-2 text-xs font-bold lg:block">+ Página</button>
                <button type="button" wire:click="duplicatePage" class="hidden rounded-xl border px-3 py-2 text-xs font-bold xl:block">Duplicar</button>
                <button type="button" wire:click="undo" class="rounded-xl border px-3 py-2 text-xs font-bold" @disabled(!$this->canUndo)>↶</button>
                <button type="button" wire:click="redo" class="rounded-xl border px-3 py-2 text-xs font-bold" @disabled(!$this->canRedo)>↷</button>
                <span class="hidden text-xs font-semibold text-zinc-400 sm:block" x-text="autosaveLabel"></span>
                <a href="{{ route('site.public', ['site' => $site, 'preview' => 1]) }}" target="_blank" class="hidden rounded-xl border px-3 py-2 text-xs font-bold md:block">Pré-visualizar</a>
                <button type="button" wire:click="publish" class="rounded-xl bg-zinc-950 px-4 py-2 text-xs font-black text-white hover:bg-zinc-800 dark:bg-white dark:text-zinc-950">Publicar</button>
            </div>
        </div>
    </header>

    <div class="flex min-h-0 flex-1 overflow-hidden">
        <aside class="builder-scroll hidden h-full w-72 shrink-0 overflow-y-auto overscroll-contain border-r border-zinc-200 bg-white lg:block dark:border-zinc-800 dark:bg-zinc-900">
            <div class="border-b border-zinc-100 p-5 dark:border-zinc-800">
                <p class="text-[10px] font-black uppercase tracking-widest text-indigo-600">1 · Adicionar</p>
                <h2 class="mt-1 text-lg font-black">Construir página</h2>
                <p class="mt-1 text-xs leading-5 text-zinc-500">Adiciona uma secção e depois personaliza-a no painel da direita.</p>
            </div>
            <div class="space-y-5 p-4">
                @foreach([
                    'Conteúdo'=>['hero'=>'Hero / destaque','text'=>'Texto','image'=>'Imagem','button'=>'Botão','card'=>'Cartão'],
                    'Estrutura'=>['feature_grid'=>'Benefícios','gallery'=>'Galeria','testimonials'=>'Testemunhos','faq'=>'Perguntas frequentes','pricing'=>'Preços'],
                    'Negócio'=>['contact_form'=>'Formulário de contacto','product_grid'=>'Produtos','blog_posts'=>'Artigos','newsletter'=>'Newsletter','cta'=>'Call to action'],
                    'Avançado'=>['video'=>'Vídeo','map'=>'Mapa','social_links'=>'Redes sociais'],
                ] as $group=>$types)
                    <div>
                        <p class="mb-2 px-1 text-[10px] font-black uppercase tracking-wider text-zinc-400">{{ $group }}</p>
                        <div class="grid gap-1.5">
                            @foreach($types as $type=>$label)
                                <button type="button" wire:click="addSection('{{ $type }}')" class="group flex items-center justify-between rounded-xl border border-zinc-200 bg-white px-3 py-3 text-left transition hover:border-indigo-300 hover:bg-indigo-50 dark:border-zinc-700 dark:bg-zinc-900 dark:hover:bg-zinc-800">
                                    <span><span class="block text-xs font-bold">{{ $label }}</span><span class="block text-[10px] text-zinc-400">Adicionar ao website</span></span><span class="text-zinc-300 group-hover:text-indigo-600">+</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </aside>

        <main class="min-h-0 min-w-0 flex-1 overflow-y-auto overscroll-contain">
            <div class="border-b border-zinc-200 bg-white px-4 py-3 dark:border-zinc-800 dark:bg-zinc-900">
                <div class="mx-auto flex max-w-[1440px] flex-wrap items-center gap-3">
                    <div class="min-w-0 flex-1"><div class="flex items-center gap-2"><input wire:model.live.debounce.500ms="pageName" aria-label="Nome da página" class="min-w-0 border-0 bg-transparent p-0 text-lg font-black outline-none focus:ring-0"><span class="rounded-full px-2 py-1 text-[9px] font-black uppercase {{ $pageStatus === 'published' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">{{ $pageStatus === 'published' ? 'Publicada' : 'Rascunho' }}</span></div><p class="text-xs text-zinc-400">/{{ trim($pageSlug, '/') }}</p></div>
                    <button type="button" wire:click="save" class="rounded-xl border px-4 py-2 text-xs font-black">Guardar agora</button>
                    <button type="button" wire:click="deletePage" class="rounded-xl border border-red-200 px-3 py-2 text-xs font-bold text-red-600">Eliminar página</button>
                </div>
            </div>

            <div class="mx-auto flex max-w-[1440px] justify-center p-4 lg:p-8">
                <div class="w-full max-w-[1200px] overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900" style="max-width: {{ $theme['content_width'] ?? '1200px' }}">
                    <div x-ref="canvas" class="min-h-[650px]">
                        @forelse($sections as $index=>$section)
                            @php($content=$section['content'] ?? [])
                            @php($items=$content['items'] ?? [])
                            <section
                                data-section-id="{{ $section['id'] ?? 'new-'.$index }}"
                                draggable="true"
                                wire:key="builder-section-{{ $section['id'] ?? 'new-'.$index }}"
                                @dragstart="dragging={{ $index }}"
                                @dragover.prevent
                                @drop.prevent="dropSection({{ $index }})"
                                wire:click="selectSection({{ $index }})"
                                class="group relative border-b border-zinc-100 transition dark:border-zinc-800 {{ $section['is_visible'] ? '' : 'opacity-40' }} {{ $selectedSection === $index ? 'ring-2 ring-inset ring-indigo-500' : 'hover:ring-1 hover:ring-indigo-300' }}"
                            >
                                <div class="absolute left-3 top-3 z-10 hidden items-center gap-1 rounded-lg border border-zinc-200 bg-white/95 p-1 shadow-sm group-hover:flex dark:border-zinc-700 dark:bg-zinc-900/95">
                                    <button type="button" wire:click.stop="selectSection({{ $index }})" class="rounded px-2 py-1 text-[10px] font-bold">Editar</button>
                                    <button type="button" wire:click.stop="moveSection({{ $index }},'up')" class="rounded px-2 py-1 text-[10px]">↑</button>
                                    <button type="button" wire:click.stop="moveSection({{ $index }},'down')" class="rounded px-2 py-1 text-[10px]">↓</button>
                                    <button type="button" wire:click.stop="duplicateSection({{ $index }})" class="rounded px-2 py-1 text-[10px]">Duplicar</button>
                                    <button type="button" wire:click.stop="setSectionVisibility({{ $index }},{{ $section['is_visible'] ? 'false' : 'true' }})" class="rounded px-2 py-1 text-[10px]">{{ $section['is_visible'] ? 'Ocultar' : 'Mostrar' }}</button>
                                    <button type="button" wire:click.stop="removeSection({{ $index }})" class="rounded px-2 py-1 text-[10px] text-red-600">Eliminar</button>
                                </div>

                                <div class="px-6 py-14 lg:px-16">
                                    @if($section['type'] === 'hero')
                                        <div class="mx-auto max-w-4xl text-center">
                                            <p class="mb-3 text-xs font-black uppercase tracking-[.25em]" style="color: {{ $theme['primary'] ?? '#635bff' }}">{{ $content['subtitle'] ?? 'Destaque' }}</p>
                                            <h1 class="text-4xl font-black tracking-tight sm:text-6xl" style="color: {{ $theme['text'] ?? '#111827' }}">{{ $content['title'] ?? 'Título principal' }}</h1>
                                            <p class="mx-auto mt-5 max-w-2xl text-base leading-7 text-zinc-500">{{ $content['description'] ?? $content['body'] ?? 'Apresenta aqui a tua empresa, produto ou serviço.' }}</p>
                                            @if(!empty($content['button_label']))<span class="mt-7 inline-flex rounded-xl px-5 py-3 text-sm font-black text-white" style="background: {{ $theme['primary'] ?? '#635bff' }}">{{ $content['button_label'] }}</span>@endif
                                        </div>
                                    @elseif($section['type'] === 'text')
                                        <div class="mx-auto max-w-3xl"><h2 class="text-3xl font-black">{{ $content['title'] ?? 'Título' }}</h2><p class="mt-4 whitespace-pre-line leading-7 text-zinc-500">{{ $content['body'] ?? $content['description'] ?? '' }}</p></div>
                                    @elseif($section['type'] === 'image')
                                        <div class="mx-auto max-w-4xl text-center">@if(!empty($content['url']))<img src="{{ $content['url'] }}" alt="{{ $content['alt'] ?? '' }}" class="mx-auto max-h-[480px] rounded-2xl object-cover shadow-sm">@else<div class="rounded-2xl border-2 border-dashed border-zinc-200 py-24 text-sm text-zinc-400">Adiciona o URL de uma imagem no painel da direita.</div>@endif@if(!empty($content['caption']))<p class="mt-3 text-xs text-zinc-400">{{ $content['caption'] }}</p>@endif</div>
                                    @elseif($section['type'] === 'cta')
                                        <div class="rounded-3xl p-8 text-center sm:p-14" style="background: {{ $theme['background'] ?? '#f4f4f5' }}"><h2 class="text-3xl font-black">{{ $content['title'] ?? 'Pronto para começar?' }}</h2><p class="mx-auto mt-3 max-w-2xl text-zinc-500">{{ $content['description'] ?? '' }}</p><span class="mt-6 inline-flex rounded-xl px-5 py-3 text-sm font-black text-white" style="background: {{ $theme['primary'] ?? '#635bff' }}">{{ $content['button_label'] ?? 'Começar' }}</span></div>
                                    @elseif(in_array($section['type'], ['feature_grid','card','testimonials','faq','pricing','gallery','product_grid','product_card','blog_posts','social_links'], true))
                                        <div><h2 class="text-center text-3xl font-black">{{ $content['title'] ?? \Illuminate\Support\Str::headline($section['type']) }}</h2>@if(!empty($content['description']))<p class="mx-auto mt-3 max-w-2xl text-center text-zinc-500">{{ $content['description'] }}</p>@endif<div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">@forelse($items as $item)<div class="rounded-2xl border border-zinc-200 p-5 dark:border-zinc-700"><p class="font-black">{{ $item['title'] ?? $item['name'] ?? $item['question'] ?? $item['label'] ?? 'Item' }}</p><p class="mt-2 text-sm leading-6 text-zinc-500">{{ $item['description'] ?? $item['quote'] ?? $item['answer'] ?? $item['excerpt'] ?? $item['price'] ?? '' }}</p></div>@empty<div class="col-span-full rounded-2xl border-2 border-dashed border-zinc-200 py-12 text-center text-sm text-zinc-400">Ainda não existem itens nesta secção.</div>@endforelse</div></div>
                                    @else
                                        <div class="mx-auto max-w-3xl text-center"><h2 class="text-3xl font-black">{{ $content['title'] ?? \Illuminate\Support\Str::headline($section['type']) }}</h2><p class="mt-4 text-zinc-500">{{ $content['description'] ?? $content['body'] ?? $content['address'] ?? 'Personaliza esta secção no painel da direita.' }}</p></div>
                                    @endif
                                </div>
                            </section>
                        @empty
                            <div class="flex min-h-[650px] items-center justify-center p-8 text-center"><div><div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 text-2xl">✦</div><h2 class="mt-5 text-xl font-black">Começa a construir a tua página</h2><p class="mx-auto mt-2 max-w-md text-sm leading-6 text-zinc-500">Escolhe uma secção no painel esquerdo. O conteúdo aparece aqui e pode ser personalizado sem código.</p></div></div>
                        @endforelse
                    </div>
                </div>
            </div>
        </main>

        <aside class="builder-scroll hidden h-full w-80 shrink-0 overflow-y-auto overscroll-contain border-l border-zinc-200 bg-white lg:block dark:border-zinc-800 dark:bg-zinc-900">
            <div class="border-b border-zinc-100 p-4 dark:border-zinc-800"><p class="text-[10px] font-black uppercase tracking-widest text-indigo-600">3 · Personalizar</p><h2 class="mt-1 text-lg font-black">Inspector</h2><p class="mt-1 text-xs text-zinc-500">Seleciona uma secção para editar o conteúdo.</p></div>
            @if($selectedSection !== null && isset($sections[$selectedSection]))
                @php($selected=$sections[$selectedSection])
                @php($selectedContent=$selected['content'] ?? [])
                <div class="space-y-5 p-4">
                    <div><p class="text-[10px] font-black uppercase tracking-widest text-zinc-400">Secção</p><p class="mt-1 font-black">{{ \Illuminate\Support\Str::headline($selected['type']) }}</p></div>
                    <div class="space-y-3">
                        <label class="block"><span class="text-xs font-bold">Título</span><input wire:model.live.debounce.500ms="sections.{{ $selectedSection }}.content.title" class="mt-1 w-full rounded-xl border-zinc-200 text-sm dark:border-zinc-700 dark:bg-zinc-800"></label>
                        <label class="block"><span class="text-xs font-bold">Subtítulo</span><input wire:model.live.debounce.500ms="sections.{{ $selectedSection }}.content.subtitle" class="mt-1 w-full rounded-xl border-zinc-200 text-sm dark:border-zinc-700 dark:bg-zinc-800"></label>
                        <label class="block"><span class="text-xs font-bold">Descrição / texto</span><textarea wire:model.live.debounce.500ms="sections.{{ $selectedSection }}.content.description" rows="5" class="mt-1 w-full rounded-xl border-zinc-200 text-sm dark:border-zinc-700 dark:bg-zinc-800"></textarea></label>
                        <label class="block"><span class="text-xs font-bold">Texto do botão</span><input wire:model.live.debounce.500ms="sections.{{ $selectedSection }}.content.button_label" class="mt-1 w-full rounded-xl border-zinc-200 text-sm dark:border-zinc-700 dark:bg-zinc-800"></label>
                        <label class="block"><span class="text-xs font-bold">URL</span><input wire:model.live.debounce.500ms="sections.{{ $selectedSection }}.content.url" class="mt-1 w-full rounded-xl border-zinc-200 text-sm dark:border-zinc-700 dark:bg-zinc-800"></label>
                        <label class="block"><span class="text-xs font-bold">Imagem / URL</span><input wire:model.live.debounce.500ms="sections.{{ $selectedSection }}.content.image" class="mt-1 w-full rounded-xl border-zinc-200 text-sm dark:border-zinc-700 dark:bg-zinc-800"></label>
                        <label class="block"><span class="text-xs font-bold">Legenda</span><input wire:model.live.debounce.500ms="sections.{{ $selectedSection }}.content.caption" class="mt-1 w-full rounded-xl border-zinc-200 text-sm dark:border-zinc-700 dark:bg-zinc-800"></label>
                        <label class="block"><span class="text-xs font-bold">Alt da imagem</span><input wire:model.live.debounce.500ms="sections.{{ $selectedSection }}.content.alt" class="mt-1 w-full rounded-xl border-zinc-200 text-sm dark:border-zinc-700 dark:bg-zinc-800"></label>
                    </div>
                    @if(!empty($selectedContent['items']) && is_array($selectedContent['items']))
                        <div><div class="mb-2 flex items-center justify-between"><span class="text-xs font-black">Itens</span><button type="button" wire:click="addItem({{ $selectedSection }})" class="rounded-lg bg-zinc-950 px-2 py-1 text-[10px] font-bold text-white">+ Item</button></div><div class="space-y-2">@foreach($selectedContent['items'] as $itemIndex=>$item)<div class="rounded-xl border border-zinc-200 p-3 dark:border-zinc-700"><input wire:model.live.debounce.500ms="sections.{{ $selectedSection }}.content.items.{{ $itemIndex }}.title" placeholder="Título" class="w-full rounded-lg border-zinc-200 text-xs dark:border-zinc-700 dark:bg-zinc-800"><textarea wire:model.live.debounce.500ms="sections.{{ $selectedSection }}.content.items.{{ $itemIndex }}.description" placeholder="Descrição" rows="2" class="mt-2 w-full rounded-lg border-zinc-200 text-xs dark:border-zinc-700 dark:bg-zinc-800"></textarea><div class="mt-2 flex gap-1"><button type="button" wire:click="moveItem({{ $selectedSection }},{{ $itemIndex }},'up')" class="rounded border px-2 py-1 text-[10px]">↑</button><button type="button" wire:click="moveItem({{ $selectedSection }},{{ $itemIndex }},'down')" class="rounded border px-2 py-1 text-[10px]">↓</button><button type="button" wire:click="removeItem({{ $selectedSection }},{{ $itemIndex }})" class="ml-auto rounded border border-red-200 px-2 py-1 text-[10px] text-red-600">Eliminar</button></div></div>@endforeach</div></div>
                    @endif
                    <div class="flex gap-2"><button type="button" wire:click="moveSection({{ $selectedSection }},'up')" class="flex-1 rounded-xl border px-3 py-2 text-xs font-bold">↑ Subir</button><button type="button" wire:click="moveSection({{ $selectedSection }},'down')" class="flex-1 rounded-xl border px-3 py-2 text-xs font-bold">↓ Descer</button></div>
                    <div class="flex gap-2"><button type="button" wire:click="duplicateSection({{ $selectedSection }})" class="flex-1 rounded-xl border px-3 py-2 text-xs font-bold">Duplicar</button><button type="button" wire:click="removeSection({{ $selectedSection }})" class="flex-1 rounded-xl border border-red-200 px-3 py-2 text-xs font-bold text-red-600">Eliminar</button></div>
                </div>
            @else
                <div class="p-5 text-sm leading-6 text-zinc-500">Clica numa secção no canvas para abrir as opções de edição.</div>
            @endif
        </aside>
    </div>

    <div class="shrink-0 border-t border-zinc-200 bg-white/95 p-2 backdrop-blur lg:hidden dark:border-zinc-800 dark:bg-zinc-900/95">
        <div class="grid grid-cols-3 gap-2">
            <button type="button" @click="add=true" class="rounded-xl border px-3 py-2 text-xs font-black">+ Adicionar</button>
            <button type="button" @click="settings=true" class="rounded-xl border px-3 py-2 text-xs font-black">Definições</button>
            <a href="{{ route('site.public', ['site' => $site, 'preview' => 1]) }}" target="_blank" class="rounded-xl bg-zinc-950 px-3 py-2 text-center text-xs font-black text-white dark:bg-white dark:text-zinc-950">Pré-visualizar</a>
        </div>
    </div>

    <div x-show="add" x-cloak class="fixed inset-0 z-[100] flex items-end bg-black/50 p-3 lg:hidden" @click.self="add=false">
        <div class="max-h-[85vh] w-full overflow-y-auto rounded-3xl bg-white p-5 shadow-2xl dark:bg-zinc-900">
            <div class="mb-4 flex items-center justify-between"><h2 class="text-lg font-black">Adicionar secção</h2><button type="button" @click="add=false" class="text-xl">×</button></div>
            <div class="grid gap-2">@foreach(['hero'=>'Hero / destaque','text'=>'Texto','image'=>'Imagem','button'=>'Botão','card'=>'Cartão','feature_grid'=>'Benefícios','gallery'=>'Galeria','testimonials'=>'Testemunhos','faq'=>'Perguntas frequentes','pricing'=>'Preços','contact_form'=>'Formulário de contacto','product_grid'=>'Produtos','blog_posts'=>'Artigos','newsletter'=>'Newsletter','cta'=>'Call to action','video'=>'Vídeo','map'=>'Mapa','social_links'=>'Redes sociais'] as $type=>$label)<button type="button" @click="$wire.addSection('{{ $type }}'); add=false" class="flex items-center justify-between rounded-xl border p-3 text-left text-sm font-bold">{{ $label }} <span>+</span></button>@endforeach</div>
        </div>
    </div>

    <div x-show="settings" x-cloak class="fixed inset-0 z-[100] flex items-end bg-black/50 p-3 lg:hidden" @click.self="settings=false">
        <div class="w-full rounded-3xl bg-white p-5 shadow-2xl dark:bg-zinc-900">
            <div class="mb-4 flex items-center justify-between"><h2 class="text-lg font-black">Definições</h2><button type="button" @click="settings=false" class="text-xl">×</button></div>
            <div class="space-y-3"><button type="button" wire:click="save" class="w-full rounded-xl border px-4 py-3 text-sm font-black">Guardar agora</button><button type="button" wire:click="publish" class="w-full rounded-xl bg-zinc-950 px-4 py-3 text-sm font-black text-white dark:bg-white dark:text-zinc-950">Publicar</button></div>
        </div>
    </div>
</div>
