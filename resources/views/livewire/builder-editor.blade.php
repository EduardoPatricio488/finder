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
    class="flex min-h-screen flex-col bg-zinc-100 text-zinc-900 dark:bg-zinc-950 dark:text-zinc-100"
>
    <header class="sticky top-0 z-40 border-b border-zinc-200 bg-white/95 backdrop-blur dark:border-zinc-800 dark:bg-zinc-900/95">
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

    <div class="flex min-h-0 flex-1">
        <aside class="builder-scroll hidden w-72 shrink-0 overflow-y-auto border-r border-zinc-200 bg-white lg:block dark:border-zinc-800 dark:bg-zinc-900">
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

        <main class="min-w-0 flex-1 overflow-y-auto">
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
                                        <div><h2 class="text-center text-3xl font-black">{{ $content['title'] ?? Str::headline($section['type']) }}</h2>@if(!empty($content['description']))<p class="mx-auto mt-3 max-w-2xl text-center text-zinc-500">{{ $content['description'] }}</p>@endif<div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">@forelse($items as $item)<div class="rounded-2xl border border-zinc-200 p-5 dark:border-zinc-700"><p class="font-black">{{ $item['title'] ?? $item['name'] ?? $item['question'] ?? $item['label'] ?? 'Item' }}</p><p class="mt-2 text-sm leading-6 text-zinc-500">{{ $item['description'] ?? $item['quote'] ?? $item['answer'] ?? $item['excerpt'] ?? $item['price'] ?? '' }}</p></div>@empty<div class="col-span-full rounded-2xl border-2 border-dashed border-zinc-200 py-12 text-center text-sm text-zinc-400">Ainda não existem itens nesta secção.</div>@endforelse</div></div>
                                    @else
                                        <div class="mx-auto max-w-3xl text-center"><h2 class="text-3xl font-black">{{ $content['title'] ?? Str::headline($section['type']) }}</h2><p class="mt-4 text-zinc-500">{{ $content['description'] ?? $content['body'] ?? $content['address'] ?? 'Personaliza esta secção no painel da direita.' }}</p></div>
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

        <aside class="builder-scroll hidden w-80 shrink-0 overflow-y-auto border-l border-zinc-200 bg-white lg:block dark:border-zinc-800 dark:bg-zinc-900">
            <div class="border-b border-zinc-100 p-4 dark:border-zinc-800"><p class="text-[10px] font-black uppercase tracking-widest text-indigo-600">3 · Personalizar</p><h2 class="mt-1 font-black">{{ $sections[$selectedSection ?? 0]['label'] ?? 'Elemento seleccionado' }}</h2><p class="mt-1 text-xs leading-5 text-zinc-500">Altera o conteúdo aqui ou edita directamente no website.</p></div>
            <div class="space-y-6 p-4">
                @php($sIndex=$selectedSection ?? 0)
                @if(isset($sections[$sIndex]))
                    @php($s=$sections[$sIndex]) @php($c=$s['content']??[])
                    <div><label class="text-xs font-bold">Nome interno</label><input wire:model.live="sections.{{ $sIndex }}.label" class="mt-2 w-full rounded-xl border-zinc-200 text-sm dark:border-zinc-700 dark:bg-zinc-800"></div>
                    @foreach(['title'=>'Título','subtitle'=>'Subtítulo','body'=>'Texto','description'=>'Descrição','button_label'=>'Texto do botão','button_url'=>'Ligação / URL','caption'=>'Legenda','alt'=>'Texto alternativo','address'=>'Morada','url'=>'URL'] as $field=>$label)
                        @if(array_key_exists($field,$c))<div><label class="text-xs font-bold">{{ $label }}</label>@if(in_array($field,['body','description'],true))<textarea wire:model.live.debounce.300ms="sections.{{ $sIndex }}.content.{{ $field }}" rows="4" class="mt-2 w-full rounded-xl border-zinc-200 text-sm dark:border-zinc-700 dark:bg-zinc-800"></textarea>@else<input wire:model.live.debounce.300ms="sections.{{ $sIndex }}.content.{{ $field }}" class="mt-2 w-full rounded-xl border-zinc-200 text-sm dark:border-zinc-700 dark:bg-zinc-800">@endif</div>@endif
                    @endforeach
                    @if(isset($c['items']) && is_array($c['items']))
                        <div class="border-t border-zinc-100 pt-5 dark:border-zinc-800"><div class="flex items-center justify-between"><div><p class="text-xs font-black">Itens</p><p class="mt-1 text-[10px] text-zinc-400">Adiciona e organiza conteúdos repetidos.</p></div><button type="button" wire:click="addItem({{ $sIndex }})" class="rounded-lg bg-indigo-50 px-2.5 py-1.5 text-[10px] font-bold text-indigo-700">+ Adicionar</button></div><div class="mt-3 space-y-3">@foreach($c['items'] as $itemIndex=>$item)<div class="rounded-xl border border-zinc-200 p-3 dark:border-zinc-700"><div class="flex items-center justify-between"><span class="text-[10px] font-black text-zinc-400">Item {{ $itemIndex+1 }}</span><div class="flex gap-1"><button type="button" wire:click="moveItem({{ $sIndex }},{{ $itemIndex }},'up')" class="px-1 text-xs">↑</button><button type="button" wire:click="moveItem({{ $sIndex }},{{ $itemIndex }},'down')" class="px-1 text-xs">↓</button><button type="button" wire:click="removeItem({{ $sIndex }},{{ $itemIndex }})" class="px-1 text-xs text-red-600">×</button></div></div>@foreach($item as $key=>$value)@if(!is_array($value))<label class="mt-2 block text-[10px] font-bold text-zinc-500">{{ Str::headline($key) }}<input wire:model.live.debounce.300ms="sections.{{ $sIndex }}.content.items.{{ $itemIndex }}.{{ $key }}" class="mt-1 w-full rounded-lg border-zinc-200 text-xs dark:border-zinc-700 dark:bg-zinc-800"></label>@endif @endforeach</div>@endforeach</div></div>
                    @endif
                    <div class="border-t border-zinc-100 pt-5 dark:border-zinc-800"><p class="text-xs font-black">Secção</p><div class="mt-3 grid grid-cols-2 gap-2"><button type="button" wire:click="moveSection({{ $sIndex }},'up')" class="rounded-lg border p-2 text-xs">↑ Subir</button><button type="button" wire:click="moveSection({{ $sIndex }},'down')" class="rounded-lg border p-2 text-xs">↓ Descer</button><button type="button" wire:click="duplicateSection({{ $sIndex }})" class="rounded-lg border p-2 text-xs">Duplicar</button><button type="button" wire:click="removeSection({{ $sIndex }})" class="rounded-lg border border-red-200 p-2 text-xs text-red-600">Eliminar</button></div></div>
                @else
                    <div class="rounded-xl bg-zinc-50 p-4 text-xs leading-5 text-zinc-500 dark:bg-zinc-800">Seleciona uma secção no centro para a personalizares.</div>
                @endif
                <div class="border-t border-zinc-100 pt-5 dark:border-zinc-800"><p class="text-xs font-black">Design do website</p><p class="mt-1 text-[10px] leading-4 text-zinc-400">Cores e largura usadas como base visual.</p><div class="mt-3 grid grid-cols-2 gap-2">@foreach(['primary'=>'Principal','secondary'=>'Secundária','background'=>'Fundo','text'=>'Texto'] as $key=>$label)<label class="text-[10px] font-bold">{{ $label }}<input type="color" wire:model.live="theme.{{ $key }}" class="mt-1 h-9 w-full cursor-pointer rounded-lg border"></label>@endforeach</div><label class="mt-3 block text-[10px] font-bold">Largura do conteúdo<select wire:model.live="theme.content_width" class="mt-1 w-full rounded-lg border-zinc-200 text-xs dark:border-zinc-700 dark:bg-zinc-800"><option value="960px">Compacta</option><option value="1200px">Normal</option><option value="1440px">Grande</option></select></label></div>
            </div>
        </aside>
    </div>

    <div class="border-t border-zinc-200 bg-white px-3 py-2 lg:hidden dark:border-zinc-800 dark:bg-zinc-900"><div class="flex gap-2"><button type="button" @click="add=true" class="flex-1 rounded-xl bg-zinc-950 py-2 text-xs font-bold text-white">+ Adicionar</button><button type="button" @click="settings=true" class="flex-1 rounded-xl border py-2 text-xs font-bold">Personalizar</button><button type="button" wire:click="save" class="rounded-xl border px-4 py-2 text-xs font-bold">Guardar</button></div></div>

    @if($showOnboarding)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-zinc-950/60 p-4" x-cloak>
            <div class="w-full max-w-2xl rounded-3xl bg-white p-6 shadow-2xl dark:bg-zinc-900 sm:p-8">
                <div class="flex items-start justify-between gap-4"><div><span class="inline-flex rounded-full bg-indigo-50 px-3 py-1 text-[10px] font-black uppercase tracking-wider text-indigo-700">Bem-vindo ao Builder</span><h2 class="mt-3 text-2xl font-black">Constrói o teu website sem código.</h2><p class="mt-2 max-w-xl text-sm leading-6 text-zinc-500">Escolhe uma secção, personaliza o conteúdo e vê as alterações no centro. Podes reorganizar, duplicar, ocultar e publicar quando estiveres pronto.</p></div><button type="button" wire:click="dismissOnboarding" class="rounded-xl border px-3 py-2 text-lg">×</button></div>
                <div class="mt-7 grid gap-3 sm:grid-cols-3"><div class="rounded-2xl border p-4"><b class="text-sm">1 · Adicionar</b><p class="mt-1 text-xs leading-5 text-zinc-500">Escolhe Hero, Serviços, FAQ, Preços e muito mais.</p></div><div class="rounded-2xl border p-4"><b class="text-sm">2 · Editar</b><p class="mt-1 text-xs leading-5 text-zinc-500">Seleciona uma secção e altera textos, links, itens e design.</p></div><div class="rounded-2xl border p-4"><b class="text-sm">3 · Publicar</b><p class="mt-1 text-xs leading-5 text-zinc-500">O Builder guarda automaticamente e permite pré-visualizar antes de publicar.</p></div></div>
                <div class="mt-7 flex justify-end"><button type="button" wire:click="dismissOnboarding" class="rounded-xl bg-zinc-950 px-5 py-3 text-sm font-black text-white dark:bg-white dark:text-zinc-950">Começar a editar</button></div>
            </div>
        </div>
    @endif

    <div x-show="add" x-cloak class="fixed inset-0 z-50 bg-zinc-950/50 p-4 lg:hidden" @click.self="add=false"><div class="mx-auto mt-16 max-h-[80vh] max-w-md overflow-y-auto rounded-3xl bg-white p-5 dark:bg-zinc-900"><div class="flex items-center justify-between"><h2 class="font-black">Adicionar secção</h2><button @click="add=false" class="text-xl">×</button></div><div class="mt-4 grid gap-2">@foreach(['hero'=>'Hero','text'=>'Texto','image'=>'Imagem','feature_grid'=>'Benefícios','testimonials'=>'Testemunhos','faq'=>'FAQ','pricing'=>'Preços','gallery'=>'Galeria','contact_form'=>'Contacto','product_grid'=>'Produtos','blog_posts'=>'Artigos','newsletter'=>'Newsletter','cta'=>'Call to action'] as $type=>$label)<button type="button" @click="add=false" wire:click="addSection('{{ $type }}')" class="rounded-xl border p-3 text-left text-sm font-bold">{{ $label }}</button>@endforeach</div></div></div>

    <div x-show="settings" x-cloak class="fixed inset-0 z-50 bg-zinc-950/50 p-4 lg:hidden" @click.self="settings=false"><div class="mx-auto mt-10 max-h-[85vh] max-w-md overflow-y-auto rounded-3xl bg-white p-5 dark:bg-zinc-900"><div class="flex items-center justify-between"><h2 class="font-black">Personalizar</h2><button @click="settings=false" class="text-xl">×</button></div><div class="mt-5 space-y-4">@if(isset($sections[$selectedSection ?? 0])) @php($mobileIndex=$selectedSection ?? 0) @foreach(['title'=>'Título','subtitle'=>'Subtítulo','body'=>'Texto','description'=>'Descrição','button_label'=>'Botão','button_url'=>'URL'] as $field=>$label) @if(array_key_exists($field,$sections[$mobileIndex]['content']??[]))<label class="block text-xs font-bold">{{ $label }}<input wire:model.live="sections.{{ $mobileIndex }}.content.{{ $field }}" class="mt-1 w-full rounded-xl border-zinc-200 text-sm dark:border-zinc-700 dark:bg-zinc-800"></label>@endif @endforeach @endif</div></div></div>
</div>
