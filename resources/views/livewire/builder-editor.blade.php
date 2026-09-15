<div
    x-data="{
        leftOpen: false,
        rightOpen: false,
        inspectorTab: 'content',
        viewport: 'desktop',
        saveState: @js($dirty ? 'unsaved' : 'saved'),
        saveMessage: @js($dirty ? 'Alterações por guardar' : 'Guardado'),
        init() {
            window.addEventListener('builder-dirty', () => { this.saveState = 'unsaved'; this.saveMessage = 'Alterações por guardar'; });
            window.addEventListener('builder-saved', () => { this.saveState = 'saved'; this.saveMessage = 'Guardado agora'; });
            window.addEventListener('builder-published', () => { this.saveState = 'saved'; this.saveMessage = 'Website publicado'; });
            window.addEventListener('builder-unpublished', () => { this.saveState = 'saved'; this.saveMessage = 'Publicação removida'; });
            window.addEventListener('builder-section-selected', () => { this.rightOpen = true; });
        },
        canvasClass() {
            return { desktop: 'w-full', tablet: 'w-[768px] max-w-full', mobile: 'w-[390px] max-w-full' }[this.viewport];
        },
        async recommendedStart() {
            await $wire.addSection('hero');
            await $wire.addSection('feature_grid');
            await $wire.addSection('testimonials');
            await $wire.addSection('cta');
            await $wire.addSection('contact_form');
            await $wire.dismissOnboarding();
        }
    }"
    class="flex h-screen min-h-0 flex-col overflow-hidden bg-zinc-100 text-zinc-950 dark:bg-zinc-950 dark:text-zinc-100"
>
    <header class="z-50 shrink-0 border-b border-zinc-200 bg-white/95 backdrop-blur-xl dark:border-zinc-800 dark:bg-zinc-950/95">
        <div class="flex min-h-[68px] items-center gap-2 px-3 sm:px-4 lg:px-6">
            <a href="{{ route('admin.site.dashboard', $site) }}" aria-label="Sair do Builder" class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-zinc-200 text-lg font-bold transition hover:bg-zinc-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-zinc-700 dark:hover:bg-zinc-900">←</a>
            <div class="min-w-0">
                <div class="flex items-center gap-2"><span class="hidden text-[10px] font-black uppercase tracking-[.18em] text-indigo-600 sm:inline">Finder Builder</span><span class="hidden text-zinc-300 sm:inline">/</span><span class="truncate text-sm font-black">{{ $site->name }}</span></div>
                <p class="truncate text-[11px] text-zinc-400">{{ $pageName ?: 'Página sem título' }}</p>
            </div>
            <div class="ml-auto flex items-center gap-1.5 sm:gap-2">
                <div class="hidden min-w-[150px] items-center gap-2 rounded-xl bg-zinc-50 px-3 py-2 text-xs dark:bg-zinc-900 md:flex"><span class="h-2 w-2 shrink-0 rounded-full" :class="saveState === 'saved' ? 'bg-emerald-500' : 'bg-amber-500'"></span><span x-text="saveMessage" class="font-bold text-zinc-600 dark:text-zinc-300"></span></div>
                <button type="button" wire:click="undo" @disabled(!$this->canUndo) aria-label="Desfazer" title="Desfazer" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-zinc-200 text-sm font-black transition hover:bg-zinc-50 disabled:cursor-not-allowed disabled:opacity-35 dark:border-zinc-700 dark:hover:bg-zinc-900">↶</button>
                <button type="button" wire:click="redo" @disabled(!$this->canRedo) aria-label="Refazer" title="Refazer" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-zinc-200 text-sm font-black transition hover:bg-zinc-50 disabled:cursor-not-allowed disabled:opacity-35 dark:border-zinc-700 dark:hover:bg-zinc-900">↷</button>
                <a href="{{ route('site.public', ['site' => $site, 'preview' => 1]) }}" target="_blank" class="hidden h-10 items-center rounded-xl border border-zinc-200 px-3 text-xs font-black transition hover:bg-zinc-50 md:inline-flex dark:border-zinc-700 dark:hover:bg-zinc-900">Pré-visualizar</a>
                <button type="button" wire:click="publish" wire:loading.attr="disabled" wire:target="publish" class="inline-flex h-10 items-center gap-2 rounded-xl bg-zinc-950 px-3.5 text-xs font-black text-white shadow-sm transition hover:bg-zinc-800 disabled:opacity-60 dark:bg-white dark:text-zinc-950 dark:hover:bg-zinc-200"><span wire:loading.remove wire:target="publish">Publicar website</span><span wire:loading wire:target="publish">A publicar...</span></button>
            </div>
        </div>
    </header>

    <div class="relative flex min-h-0 flex-1 overflow-hidden">
        <aside class="absolute inset-y-0 left-0 z-40 w-[300px] shrink-0 overflow-y-auto border-r border-zinc-200 bg-white shadow-2xl transition-transform duration-200 lg:relative lg:z-20 lg:block lg:w-[280px] lg:translate-x-0 lg:shadow-none dark:border-zinc-800 dark:bg-zinc-950" :class="leftOpen ? 'translate-x-0' : '-translate-x-full'">
            <div class="sticky top-0 z-10 border-b border-zinc-200 bg-white/95 p-4 backdrop-blur dark:border-zinc-800 dark:bg-zinc-950/95">
                <div class="flex items-start justify-between gap-3"><div><p class="text-[10px] font-black uppercase tracking-[.18em] text-indigo-600">Editor</p><h2 class="mt-1 text-base font-black">Constrói a tua página</h2><p class="mt-1 text-xs leading-5 text-zinc-500">Escolhe o que queres adicionar ou gerir.</p></div><button type="button" @click="leftOpen = false" class="inline-flex h-8 w-8 items-center justify-center rounded-lg hover:bg-zinc-100 lg:hidden dark:hover:bg-zinc-900" aria-label="Fechar navegação">✕</button></div>
            </div>
            <div class="space-y-6 p-4 pb-8">
                <section>
                    <div class="mb-2 flex items-center justify-between px-1"><p class="text-[10px] font-black uppercase tracking-wider text-zinc-400">Páginas</p><button type="button" wire:click="createPage" class="text-[11px] font-black text-indigo-600 hover:underline">+ Nova</button></div>
                    <div class="space-y-1">
                        @foreach($site->pages->sortBy('sort_order') as $page)
                            <div class="group flex items-center gap-1 rounded-xl {{ $pageId === $page->id ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300' : 'hover:bg-zinc-50 dark:hover:bg-zinc-900' }}">
                                <button type="button" wire:click="selectPage({{ $page->id }})" @click="leftOpen = false" class="flex min-w-0 flex-1 items-center gap-2 px-3 py-2.5 text-left"><span class="text-xs">{{ $page->is_homepage ? '⌂' : '○' }}</span><span class="truncate text-xs font-bold">{{ $page->name }}</span>@if($page->is_homepage)<span class="ml-auto shrink-0 rounded-full bg-white/70 px-1.5 py-0.5 text-[8px] font-black uppercase dark:bg-zinc-900/50">Home</span>@endif</button>
                                @if($pageId === $page->id)<button type="button" wire:click="duplicatePage" class="mr-1 hidden h-7 w-7 items-center justify-center rounded-lg text-[10px] group-hover:flex hover:bg-white dark:hover:bg-zinc-800" title="Duplicar página" aria-label="Duplicar página">＋</button>@endif
                            </div>
                        @endforeach
                    </div>
                </section>

                <section>
                    <p class="mb-2 px-1 text-[10px] font-black uppercase tracking-wider text-zinc-400">Secções</p>
                    <div class="grid gap-1.5">
                        @foreach(['Destaque'=>['hero'=>'Destaque principal','cta'=>'Chamada para ação'],'Conteúdo'=>['text'=>'Texto','image'=>'Imagem','button'=>'Botão','card'=>'Cartão','feature_grid'=>'Benefícios'],'Social'=>['testimonials'=>'Testemunhos','gallery'=>'Galeria','social_links'=>'Redes sociais'],'Conversão'=>['pricing'=>'Preços','faq'=>'Perguntas frequentes','contact_form'=>'Formulário de contacto','newsletter'=>'Newsletter'],'Negócio'=>['product_grid'=>'Produtos','blog_posts'=>'Artigos'],'Avançado'=>['video'=>'Vídeo','map'=>'Mapa']] as $group => $types)
                            <details class="group/details rounded-xl border border-zinc-100 dark:border-zinc-800"><summary class="flex cursor-pointer list-none items-center justify-between px-3 py-2.5 text-xs font-black marker:hidden"><span>{{ $group }}</span><span class="text-zinc-400 transition group-open/details:rotate-45">＋</span></summary><div class="grid gap-1 px-2 pb-2">@foreach($types as $type => $label)<button type="button" wire:click="addSection('{{ $type }}')" @click="leftOpen = false" class="group flex items-center gap-2 rounded-lg px-2.5 py-2 text-left text-xs font-bold transition hover:bg-indigo-50 hover:text-indigo-700 dark:hover:bg-indigo-950/30"><span class="flex h-6 w-6 items-center justify-center rounded-md bg-zinc-100 text-[10px] text-zinc-500 group-hover:bg-white group-hover:text-indigo-600 dark:bg-zinc-900">+</span><span>{{ $label }}</span></button>@endforeach</div></details>
                        @endforeach
                    </div>
                </section>

                <section class="rounded-2xl border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-800 dark:bg-zinc-900"><p class="text-[10px] font-black uppercase tracking-wider text-zinc-400">Recursos</p><div class="mt-2 grid gap-1"><button type="button" @click="document.querySelector('[data-builder-template-trigger]')?.click(); leftOpen = false" class="flex items-center gap-2 rounded-lg px-2 py-2 text-left text-xs font-bold hover:bg-white dark:hover:bg-zinc-800"><span>🎨</span> Modelos</button><a href="{{ route('admin.site.media', $site) }}" class="flex items-center gap-2 rounded-lg px-2 py-2 text-left text-xs font-bold hover:bg-white dark:hover:bg-zinc-800"><span>🖼</span> Media</a></div></section>
            </div>
        </aside>

        <button type="button" x-show="leftOpen || rightOpen" x-cloak @click="leftOpen = false; rightOpen = false" class="fixed inset-0 z-30 bg-zinc-950/40 backdrop-blur-sm lg:hidden" aria-label="Fechar painéis"></button>

        <main class="min-w-0 flex-1 overflow-hidden bg-zinc-100 dark:bg-zinc-900">
            <div class="flex h-full min-h-0 flex-col">
                <div class="shrink-0 border-b border-zinc-200 bg-white/95 px-3 py-2.5 backdrop-blur dark:border-zinc-800 dark:bg-zinc-950/95">
                    <div class="flex items-center gap-2">
                        <button type="button" @click="leftOpen = true" class="inline-flex h-9 items-center gap-1.5 rounded-lg border border-zinc-200 px-2.5 text-xs font-black lg:hidden dark:border-zinc-700">☰ <span>Editar</span></button>
                        <div class="min-w-0 flex-1"><div class="flex items-center gap-2"><input wire:model.live.debounce.500ms="pageName" aria-label="Nome da página" class="min-w-0 max-w-[260px] truncate border-0 bg-transparent p-0 text-sm font-black outline-none ring-0 focus:ring-0" /><span class="shrink-0 rounded-full px-2 py-1 text-[9px] font-black uppercase {{ $pageStatus === 'published' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">{{ $pageStatus === 'published' ? 'Publicada' : 'Rascunho' }}</span></div><p class="truncate text-[10px] text-zinc-400">/{{ trim($pageSlug, '/') }}</p></div>
                        <div class="hidden items-center gap-1 md:flex"><livewire:builder-templates :site="$site" :page-id="$pageId" :key="'builder-templates-'.$pageId" /><button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save" class="h-9 rounded-lg border border-zinc-200 px-3 text-[10px] font-black dark:border-zinc-700"><span wire:loading.remove wire:target="save">Guardar</span><span wire:loading wire:target="save">A guardar...</span></button></div>
                        <button type="button" @click="rightOpen = true" class="inline-flex h-9 items-center gap-1.5 rounded-lg border border-zinc-200 px-2.5 text-xs font-black lg:hidden dark:border-zinc-700">Ajustar</button>
                    </div>
                </div>

                <div class="flex min-h-0 flex-1 flex-col overflow-hidden">
                    <div class="min-h-0 flex-1 overflow-auto p-3 sm:p-5 lg:p-8">
                        <div class="mx-auto flex min-h-full justify-center transition-all duration-200" :class="canvasClass()">
                            <div class="w-full overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-950" style="min-height: 650px">
                                @if($sections)
                                    @foreach($sections as $index => $section)
                                        @php($content = $section['content'] ?? []) @php($items = $content['items'] ?? [])
                                        <section data-section-id="{{ $section['id'] ?? 'new-'.$index }}" wire:key="builder-section-{{ $section['id'] ?? 'new-'.$index }}" wire:click="selectSection({{ $index }})" class="group/section relative border-b border-zinc-100 transition dark:border-zinc-800 {{ $section['is_visible'] ? '' : 'opacity-40' }} {{ $selectedSection === $index ? 'ring-2 ring-inset ring-indigo-500' : 'hover:ring-1 hover:ring-indigo-300' }}">
                                            <div class="pointer-events-none absolute inset-x-0 top-0 z-20 flex justify-center opacity-0 transition group-hover/section:opacity-100 {{ $selectedSection === $index ? '!opacity-100' : '' }}"><div class="pointer-events-auto mt-2 flex items-center gap-1 rounded-xl border border-zinc-200 bg-white/95 p-1 shadow-lg backdrop-blur dark:border-zinc-700 dark:bg-zinc-900/95"><span class="px-2 text-[9px] font-black text-indigo-600">{{ $section['label'] ?? 'Secção' }}</span><button type="button" wire:click.stop="selectSection({{ $index }})" class="rounded-lg px-2 py-1 text-[10px] font-bold hover:bg-zinc-100 dark:hover:bg-zinc-800">Editar</button><button type="button" wire:click.stop="moveSection({{ $index }},'up')" class="rounded-lg px-2 py-1 text-[10px] hover:bg-zinc-100 dark:hover:bg-zinc-800" title="Mover para cima">↑</button><button type="button" wire:click.stop="moveSection({{ $index }},'down')" class="rounded-lg px-2 py-1 text-[10px] hover:bg-zinc-100 dark:hover:bg-zinc-800" title="Mover para baixo">↓</button><button type="button" wire:click.stop="duplicateSection({{ $index }})" class="rounded-lg px-2 py-1 text-[10px] hover:bg-zinc-100 dark:hover:bg-zinc-800">Duplicar</button><button type="button" wire:click.stop="setSectionVisibility({{ $index }},{{ $section['is_visible'] ? 'false' : 'true' }})" class="rounded-lg px-2 py-1 text-[10px] hover:bg-zinc-100 dark:hover:bg-zinc-800">{{ $section['is_visible'] ? 'Ocultar' : 'Mostrar' }}</button><button type="button" wire:click.stop="removeSection({{ $index }})" class="rounded-lg px-2 py-1 text-[10px] text-red-600 hover:bg-red-50 dark:hover:bg-red-950/30">Eliminar</button></div></div>
                                            <div class="px-6 py-16 sm:px-10 lg:px-16">
                                                @if($section['type'] === 'hero')
                                                    <div class="mx-auto max-w-4xl text-center"><p class="mb-3 text-xs font-black uppercase tracking-[.25em]" style="color: {{ $theme['primary'] ?? '#635bff' }}">{{ $content['subtitle'] ?? 'Destaque' }}</p><h1 contenteditable="true" spellcheck="true" wire:click.stop x-on:blur="$wire.updateInline({{ $index }}, 'title', $event.target.innerText)" class="cursor-text rounded-lg px-2 text-4xl font-black tracking-tight outline-none hover:bg-zinc-50 focus:bg-indigo-50 focus:ring-2 focus:ring-indigo-200 sm:text-6xl dark:hover:bg-zinc-900 dark:focus:bg-indigo-950/40">{{ $content['title'] ?? 'Título principal' }}</h1><p class="mx-auto mt-5 max-w-2xl text-base leading-7 text-zinc-500">{{ $content['description'] ?? $content['body'] ?? 'Apresenta aqui a tua empresa, produto ou serviço.' }}</p>@if(!empty($content['button_label']))<span class="mt-7 inline-flex rounded-xl px-5 py-3 text-sm font-black text-white" style="background: {{ $theme['primary'] ?? '#635bff' }}">{{ $content['button_label'] }}</span>@endif</div>
                                                @elseif($section['type'] === 'text')
                                                    <div class="mx-auto max-w-3xl"><h2 contenteditable="true" spellcheck="true" wire:click.stop x-on:blur="$wire.updateInline({{ $index }}, 'title', $event.target.innerText)" class="cursor-text rounded-lg px-2 text-3xl font-black outline-none hover:bg-zinc-50 focus:bg-indigo-50 focus:ring-2 focus:ring-indigo-200 dark:hover:bg-zinc-900 dark:focus:bg-indigo-950/40">{{ $content['title'] ?? 'Título' }}</h2><p class="mt-4 whitespace-pre-line leading-7 text-zinc-500">{{ $content['body'] ?? $content['description'] ?? '' }}</p></div>
                                                @elseif($section['type'] === 'image')
                                                    <div class="mx-auto max-w-4xl text-center">@if(!empty($content['url']))<img src="{{ $content['url'] }}" alt="{{ $content['alt'] ?? '' }}" class="mx-auto max-h-[480px] rounded-2xl object-cover shadow-sm">@else<div class="rounded-2xl border-2 border-dashed border-zinc-200 py-24 text-sm text-zinc-400 dark:border-zinc-700">Seleciona esta secção para adicionar uma imagem.</div>@endif</div>
                                                @elseif($section['type'] === 'cta')
                                                    <div class="rounded-3xl p-8 text-center sm:p-14" style="background: {{ $theme['background'] ?? '#f4f4f5' }}"><h2 contenteditable="true" spellcheck="true" wire:click.stop x-on:blur="$wire.updateInline({{ $index }}, 'title', $event.target.innerText)" class="cursor-text rounded-lg px-2 text-3xl font-black outline-none hover:bg-white/50 focus:bg-white/70 focus:ring-2 focus:ring-indigo-200">{{ $content['title'] ?? 'Pronto para começar?' }}</h2><p class="mx-auto mt-3 max-w-2xl text-zinc-500">{{ $content['description'] ?? '' }}</p><span class="mt-6 inline-flex rounded-xl px-5 py-3 text-sm font-black text-white" style="background: {{ $theme['primary'] ?? '#635bff' }}">{{ $content['button_label'] ?? 'Começar' }}</span></div>
                                                @else
                                                    <div class="mx-auto max-w-4xl"><h2 class="text-3xl font-black">{{ $content['title'] ?? \Illuminate\Support\Str::headline($section['type']) }}</h2>@if(!empty($content['description']))<p class="mt-4 text-zinc-500">{{ $content['description'] }}</p>@endif<div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">@forelse($items as $item)<article class="rounded-2xl border border-zinc-200 bg-white p-5 transition hover:border-indigo-200 hover:shadow-sm dark:border-zinc-700 dark:bg-zinc-900"><p contenteditable="true" spellcheck="true" wire:click.stop x-on:blur="$wire.updateInline({{ $index }}, 'title', $event.target.innerText, {{ $loop->index }})" class="cursor-text rounded-md font-black outline-none hover:bg-zinc-50 focus:bg-indigo-50 focus:ring-2 focus:ring-indigo-200 dark:hover:bg-zinc-800 dark:focus:bg-indigo-950/40">{{ $item['title'] ?? $item['name'] ?? $item['question'] ?? $item['label'] ?? 'Item' }}</p><p class="mt-2 text-sm leading-6 text-zinc-500">{{ $item['description'] ?? $item['quote'] ?? $item['answer'] ?? $item['excerpt'] ?? $item['price'] ?? '' }}</p></article>@empty<div class="col-span-full rounded-2xl border-2 border-dashed border-zinc-200 py-12 text-center dark:border-zinc-700"><p class="text-sm font-bold text-zinc-500">Esta secção ainda está vazia.</p><button type="button" wire:click.stop="addItem({{ $index }})" class="mt-3 rounded-lg bg-zinc-950 px-3 py-2 text-xs font-black text-white dark:bg-white dark:text-zinc-950">Adicionar item</button></div>@endforelse</div></div>
                                                @endif
                                            </div>
                                        </section>
                                    @endforeach
                                @else
                                    <div class="flex min-h-[620px] items-center justify-center p-6 text-center"><div class="max-w-md"><div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-indigo-50 text-2xl dark:bg-indigo-950/40">✦</div><h2 class="mt-5 text-2xl font-black">Esta página ainda está vazia</h2><p class="mt-2 text-sm leading-6 text-zinc-500">Começa por adicionar uma secção, escolher um modelo ou usar uma estrutura recomendada.</p><div class="mt-6 flex flex-wrap justify-center gap-2"><button type="button" @click="leftOpen = true" class="rounded-xl bg-zinc-950 px-4 py-2.5 text-xs font-black text-white dark:bg-white dark:text-zinc-950">Adicionar secção</button><button type="button" @click="document.querySelector('[data-builder-template-trigger]')?.click()" class="rounded-xl border border-zinc-200 px-4 py-2.5 text-xs font-black dark:border-zinc-700">Escolher modelo</button></div></div></div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="shrink-0 border-t border-zinc-200 bg-white px-3 py-2.5 dark:border-zinc-800 dark:bg-zinc-950"><div class="mx-auto flex max-w-md items-center justify-center rounded-xl bg-zinc-100 p-1 dark:bg-zinc-900">@foreach(['desktop'=>'Desktop','tablet'=>'Tablet','mobile'=>'Mobile'] as $key => $label)<button type="button" @click="viewport = '{{ $key }}'" :class="viewport === '{{ $key }}' ? 'bg-white text-zinc-950 shadow-sm dark:bg-zinc-800 dark:text-white' : 'text-zinc-500'" class="flex-1 rounded-lg px-3 py-1.5 text-[11px] font-black transition focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ $label }}</button>@endforeach</div></div>
                </div>
            </div>
        </main>

        <aside class="absolute inset-y-0 right-0 z-40 w-[360px] shrink-0 overflow-y-auto border-l border-zinc-200 bg-white shadow-2xl transition-transform duration-200 lg:relative lg:z-20 lg:block lg:w-[360px] lg:translate-x-0 lg:shadow-none dark:border-zinc-800 dark:bg-zinc-950" :class="rightOpen ? 'translate-x-0' : 'translate-x-full'">
            <div class="sticky top-0 z-10 border-b border-zinc-200 bg-white/95 backdrop-blur dark:border-zinc-800 dark:bg-zinc-950/95">
                <div class="flex items-start justify-between gap-3 p-4"><div><p class="text-[10px] font-black uppercase tracking-[.18em] text-indigo-600">Inspector</p>@if($selectedSection !== null && isset($sections[$selectedSection]))<h2 class="mt-1 text-base font-black">{{ $sections[$selectedSection]['label'] ?? 'Secção' }}</h2><p class="mt-1 text-xs text-zinc-500">Edita o conteúdo e o aspecto desta secção.</p>@else<h2 class="mt-1 text-base font-black">Personalização</h2><p class="mt-1 text-xs leading-5 text-zinc-500">Seleciona uma secção para começares a editar.</p>@endif</div><button type="button" @click="rightOpen = false" class="inline-flex h-8 w-8 items-center justify-center rounded-lg hover:bg-zinc-100 lg:hidden dark:hover:bg-zinc-900" aria-label="Fechar inspector">✕</button></div>
                @if($selectedSection !== null && isset($sections[$selectedSection]))<div class="flex border-t border-zinc-100 px-2 dark:border-zinc-800">@foreach(['content'=>'Conteúdo','design'=>'Design','layout'=>'Layout','advanced'=>'Avançado'] as $key => $label)<button type="button" @click="inspectorTab = '{{ $key }}'" :class="inspectorTab === '{{ $key }}' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-zinc-500'" class="flex-1 border-b-2 px-2 py-2.5 text-[10px] font-black">{{ $label }}</button>@endforeach</div>@endif
            </div>

            @if($selectedSection !== null && isset($sections[$selectedSection]))
                @php($selectedContent = $sections[$selectedSection]['content'] ?? []) @php($selectedItems = $selectedContent['items'] ?? [])
                <div class="p-4">
                    <div x-show="inspectorTab === 'content'" x-cloak class="space-y-4">
                        <div class="rounded-xl bg-zinc-50 p-3 text-xs leading-5 text-zinc-500 dark:bg-zinc-900"><strong class="text-zinc-800 dark:text-zinc-200">Dica:</strong> também podes clicar directamente nos títulos do canvas para os editar.</div>
                        @foreach($selectedContent as $fieldKey => $fieldValue)
                            @continue($fieldKey === 'items')
                            @if(is_scalar($fieldValue) || $fieldValue === null)
                                <div><label class="text-[11px] font-black text-zinc-700 dark:text-zinc-300">{{ \Illuminate\Support\Str::headline(str_replace('_', ' ', (string) $fieldKey)) }}</label>@if(strlen((string) $fieldValue) > 100)<textarea wire:model.live.debounce.500ms="sections.{{ $selectedSection }}.content.{{ $fieldKey }}" rows="5" class="mt-2 w-full rounded-xl border-zinc-200 bg-white px-3 py-3 text-sm outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 dark:border-zinc-700 dark:bg-zinc-900"></textarea>@else<input wire:model.live.debounce.500ms="sections.{{ $selectedSection }}.content.{{ $fieldKey }}" class="mt-2 w-full rounded-xl border-zinc-200 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 dark:border-zinc-700 dark:bg-zinc-900" />@endif</div>
                            @endif
                        @endforeach
                        @if(!empty($selectedItems))<div class="border-t border-zinc-100 pt-4 dark:border-zinc-800"><div class="mb-3 flex items-center justify-between"><p class="text-xs font-black">Itens</p><button type="button" wire:click="addItem({{ $selectedSection }})" class="rounded-lg bg-zinc-950 px-2.5 py-1.5 text-[10px] font-black text-white dark:bg-white dark:text-zinc-950">+ Adicionar</button></div><div class="space-y-2">@foreach($selectedItems as $itemIndex => $item)<div class="rounded-xl border border-zinc-200 p-3 dark:border-zinc-700"><div class="mb-2 flex items-center justify-between"><span class="text-[10px] font-black text-zinc-400">Item {{ $itemIndex + 1 }}</span><button type="button" wire:click="removeItem({{ $selectedSection }}, {{ $itemIndex }})" class="text-[10px] font-bold text-red-600">Eliminar</button></div>@foreach($item as $itemKey => $itemValue)@if(is_scalar($itemValue) || $itemValue === null)<input wire:model.live.debounce.500ms="sections.{{ $selectedSection }}.content.items.{{ $itemIndex }}.{{ $itemKey }}" class="mb-2 w-full rounded-lg border-zinc-200 px-3 py-2 text-xs dark:border-zinc-700 dark:bg-zinc-900" placeholder="{{ \Illuminate\Support\Str::headline(str_replace('_', ' ', (string) $itemKey)) }}" />@endif @endforeach</div>@endforeach</div></div>@endif
                    </div>
                    <div x-show="inspectorTab === 'design'" x-cloak class="space-y-4"><div class="rounded-2xl border border-zinc-200 p-4 dark:border-zinc-700"><p class="text-xs font-black">Aspecto</p><p class="mt-1 text-xs leading-5 text-zinc-500">A base visual utiliza o tema global do website. O editor avançado de estilos será expandido na próxima fase.</p></div><div class="grid gap-3 sm:grid-cols-2"><div class="rounded-xl bg-zinc-50 p-3 dark:bg-zinc-900"><p class="text-[10px] font-black uppercase text-zinc-400">Cor principal</p><p class="mt-1 text-sm font-bold">{{ $theme['primary'] ?? '#635bff' }}</p></div><div class="rounded-xl bg-zinc-50 p-3 dark:bg-zinc-900"><p class="text-[10px] font-black uppercase text-zinc-400">Largura</p><p class="mt-1 text-sm font-bold">{{ $theme['content_width'] ?? '1200px' }}</p></div></div></div>
                    <div x-show="inspectorTab === 'layout'" x-cloak class="space-y-3"><div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700"><p class="text-xs font-black">Espaçamento</p><p class="mt-1 text-xs text-zinc-500">O espaçamento base desta secção é mantido pela estrutura actual do Finder.</p></div><div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700"><p class="text-xs font-black">Alinhamento</p><p class="mt-1 text-xs text-zinc-500">Os valores existentes da secção continuam a ser preservados.</p></div></div>
                    <div x-show="inspectorTab === 'advanced'" x-cloak class="space-y-3"><div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700"><p class="text-xs font-black">Visibilidade</p><button type="button" wire:click="setSectionVisibility({{ $selectedSection }}, {{ $sections[$selectedSection]['is_visible'] ? 'false' : 'true' }})" class="mt-3 w-full rounded-xl border px-3 py-2 text-xs font-black">{{ $sections[$selectedSection]['is_visible'] ? 'Ocultar secção' : 'Mostrar secção' }}</button></div><button type="button" wire:click="duplicateSection({{ $selectedSection }})" class="w-full rounded-xl border border-zinc-200 px-3 py-2.5 text-xs font-black dark:border-zinc-700">Duplicar secção</button><button type="button" wire:click="removeSection({{ $selectedSection }})" class="w-full rounded-xl border border-red-200 px-3 py-2.5 text-xs font-black text-red-600">Eliminar secção</button></div>
                </div>
            @else
                <div class="flex min-h-[460px] items-center justify-center p-8 text-center"><div><div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-zinc-100 text-xl dark:bg-zinc-900">✦</div><h3 class="mt-4 font-black">Nada seleccionado</h3><p class="mx-auto mt-2 max-w-xs text-xs leading-5 text-zinc-500">Clica numa secção no canvas para veres as opções de edição.</p></div></div>
            @endif
        </aside>
    </div>

    @if($showOnboarding)
        <div class="fixed inset-0 z-[100] flex items-center justify-center bg-zinc-950/60 p-4 backdrop-blur-md" role="dialog" aria-modal="true" aria-labelledby="builder-welcome-title">
            <div class="w-full max-w-4xl overflow-hidden rounded-3xl border border-white/10 bg-white shadow-2xl dark:bg-zinc-950">
                <div class="border-b border-zinc-200 p-6 sm:p-8 dark:border-zinc-800"><div class="flex items-start justify-between gap-4"><div><span class="inline-flex rounded-full bg-indigo-50 px-3 py-1 text-[10px] font-black uppercase tracking-[.16em] text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300">Primeiros passos</span><h2 id="builder-welcome-title" class="mt-3 text-2xl font-black tracking-tight sm:text-3xl">Vamos criar o teu website</h2><p class="mt-2 max-w-2xl text-sm leading-6 text-zinc-500">Não precisas de saber programar. Escolhe uma forma de começar e depois podes personalizar tudo no Builder.</p></div><button type="button" wire:click="dismissOnboarding" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-zinc-200 text-sm font-black hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-900" aria-label="Fechar">✕</button></div></div>
                <div class="grid gap-4 p-6 sm:grid-cols-3 sm:p-8">
                    <a href="{{ route('site.create') }}" class="group rounded-2xl border border-zinc-200 p-5 transition hover:-translate-y-0.5 hover:border-indigo-400 hover:shadow-lg dark:border-zinc-700"><div class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-50 text-xl dark:bg-indigo-950/40">🤖</div><h3 class="mt-4 font-black">Criar com IA</h3><p class="mt-2 text-xs leading-5 text-zinc-500">Descreve o teu negócio e usa o criador de websites com IA já existente no Finder.</p><span class="mt-4 inline-flex text-xs font-black text-indigo-600">Abrir criador →</span></a>
                    <button type="button" @click="document.querySelector('[data-builder-template-trigger]')?.click(); $wire.dismissOnboarding();" class="group rounded-2xl border border-zinc-200 p-5 text-left transition hover:-translate-y-0.5 hover:border-indigo-400 hover:shadow-lg dark:border-zinc-700"><div class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-50 text-xl dark:bg-amber-950/30">🎨</div><h3 class="mt-4 font-black">Começar por um modelo</h3><p class="mt-2 text-xs leading-5 text-zinc-500">Escolhe uma estrutura visual pronta e continua a personalizá-la.</p><span class="mt-4 inline-flex text-xs font-black text-indigo-600">Ver modelos →</span></button>
                    <button type="button" @click="recommendedStart()" class="group rounded-2xl border border-zinc-200 p-5 text-left transition hover:-translate-y-0.5 hover:border-indigo-400 hover:shadow-lg dark:border-zinc-700"><div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-xl dark:bg-emerald-950/30">✨</div><h3 class="mt-4 font-black">Estrutura recomendada</h3><p class="mt-2 text-xs leading-5 text-zinc-500">Cria uma base profissional com destaque, benefícios, prova social, CTA e contacto.</p><span class="mt-4 inline-flex text-xs font-black text-indigo-600">Começar agora →</span></button>
                </div>
                <div class="flex items-center justify-between gap-4 border-t border-zinc-200 bg-zinc-50 px-6 py-4 dark:border-zinc-800 dark:bg-zinc-900"><p class="text-[11px] text-zinc-500">Podes mudar de estratégia a qualquer momento.</p><button type="button" wire:click="dismissOnboarding" class="rounded-xl px-3 py-2 text-xs font-black text-zinc-600 hover:bg-white dark:text-zinc-300 dark:hover:bg-zinc-800">Começar manualmente</button></div>
            </div>
        </div>
    @endif
</div>