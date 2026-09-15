@use('App\Support\WebsiteBuilder\ElementRegistry')
@use('App\Support\WebsiteBuilder\WebsiteTemplates')
@use('App\Support\WebsiteBuilder\SiteSectionDocument')
@use('App\Models\SiteSection')
@use('Illuminate\Support\Str')

<div class="min-h-screen bg-slate-100 text-slate-900" x-data="{ sectionDrag: null }" x-on:open-builder-preview.window="window.open($event.detail.url, '_blank', 'noopener,noreferrer')">
    <header class="sticky top-0 z-50 border-b border-slate-200 bg-white/95 backdrop-blur">
        <div class="flex min-h-16 items-center gap-3 px-4 lg:px-6">
            <a href="{{ route('dashboard') }}" class="rounded-xl px-2 py-2 text-sm font-semibold hover:bg-slate-100">← <span class="hidden sm:inline">Dashboard</span></a>
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2"><b class="truncate">{{ $site->name }}</b><span class="rounded-full px-2 py-0.5 text-[11px] font-bold {{ $site->is_published ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">{{ $site->is_published ? 'Online' : 'Rascunho' }}</span></div>
                <div class="truncate text-xs text-slate-500">{{ $this->currentPage()['name'] ?? 'Página' }} · {{ $statusMessage }}</div>
            </div>
            <div class="hidden rounded-xl border bg-slate-50 p-1 md:flex">
                @foreach(['desktop'=>'Computador','tablet'=>'Tablet','mobile'=>'Telemóvel'] as $key=>$label)
                    <button wire:click="$set('device','{{ $key }}')" class="rounded-lg px-3 py-1.5 text-xs font-semibold {{ $device === $key ? 'bg-white shadow-sm' : 'text-slate-500' }}">{{ $label }}</button>
                @endforeach
            </div>
            <button wire:click="$set('showVersions',true)" class="hidden rounded-xl border px-3 py-2 text-sm font-semibold sm:block">Histórico</button>
            <button wire:click="openPreview" class="rounded-xl border px-3 py-2 text-sm font-semibold">Pré-visualizar</button>
            <button wire:click="runPublishChecks" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white">{{ $site->is_published ? 'Publicar alterações' : 'Publicar' }}</button>
        </div>
    </header>

    <div class="grid min-h-[calc(100vh-4rem)] lg:grid-cols-[250px_minmax(0,1fr)_340px]">
        <aside class="hidden border-r bg-white lg:block">
            <div class="border-b p-3"><div class="grid grid-cols-3 gap-1 rounded-xl bg-slate-100 p-1">
                @foreach(['pages'=>'Páginas','elements'=>'Secções','design'=>'Design'] as $key=>$label)
                    <button wire:click="$set('panel','{{ $key }}')" class="rounded-lg px-2 py-2 text-[11px] font-bold {{ $panel === $key ? 'bg-white shadow-sm' : 'text-slate-500' }}">{{ $label }}</button>
                @endforeach
            </div></div>

            @if($panel === 'pages')
                <div class="p-3">
                    <div class="mb-3 flex items-center justify-between"><div><b class="text-sm">Páginas</b><div class="text-[11px] text-slate-500">Estrutura e navegação</div></div><button wire:click="createPage" class="rounded-lg bg-slate-900 px-2.5 py-1.5 text-xs font-bold text-white">+ Página</button></div>
                    <div class="space-y-1">
                        @foreach($pages as $page)
                            <div class="flex items-center gap-1 rounded-xl border px-2 py-2 {{ $pageId === $page['id'] ? 'border-blue-200 bg-blue-50' : 'border-transparent' }}">
                                <button wire:click="loadPage({{ $page['id'] }})" class="min-w-0 flex-1 text-left"><div class="truncate text-sm font-semibold">{{ $page['name'] }}</div><div class="truncate text-[10px] text-slate-500">/{{ $page['slug'] }} @if($page['is_homepage']) · Home @endif</div></button>
                                @if(!$page['is_homepage'])<button wire:click="setHomepage({{ $page['id'] }})" class="rounded px-2 py-1 text-xs" title="Definir como homepage">⌂</button>@endif
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4 grid grid-cols-2 gap-2"><button wire:click="duplicatePage" class="rounded-xl border px-2 py-2 text-xs font-bold">Duplicar</button><button wire:click="deletePage" wire:confirm="Eliminar esta página?" class="rounded-xl border border-red-100 px-2 py-2 text-xs font-bold text-red-600">Eliminar</button></div>
                    <div class="mt-5 rounded-2xl bg-slate-900 p-4 text-white"><b class="text-xs">Começa mais rápido</b><p class="mt-1 text-[11px] leading-4 text-slate-300">Template profissional ou criação por IA.</p><div class="mt-3 grid grid-cols-2 gap-2"><button wire:click="$set('showTemplates',true)" class="rounded-lg bg-white/10 py-2 text-[11px] font-bold">Templates</button><button wire:click="$set('showAi',true)" class="rounded-lg bg-white py-2 text-[11px] font-bold text-slate-900">Criar com IA</button></div></div>
                </div>
            @elseif($panel === 'elements')
                <div class="p-3"><b class="text-sm">Secções</b><p class="mb-3 text-[11px] text-slate-500">Blocos prontos para construir a página.</p>
                    @foreach(['hero'=>'Hero','text'=>'Texto','image'=>'Imagem','button'=>'Botão','feature_grid'=>'Benefícios','card'=>'Cartão','testimonials'=>'Testemunhos','faq'=>'FAQ','gallery'=>'Galeria','contact_form'=>'Formulário','pricing'=>'Preços','product_grid'=>'Produtos','blog_posts'=>'Artigos','social_links'=>'Redes sociais','video'=>'Vídeo','map'=>'Mapa','newsletter'=>'Newsletter','cta'=>'CTA'] as $type=>$label)
                        <button wire:click="addSection('{{ $type }}')" class="mb-1.5 flex w-full justify-between rounded-xl border bg-white px-3 py-2.5 text-left text-xs font-bold hover:border-blue-300"><span>{{ $label }}</span><span class="text-blue-600">+</span></button>
                    @endforeach
                </div>
            @else
                <div class="p-3"><b class="text-sm">Design global</b><p class="mb-4 text-[11px] text-slate-500">Uma identidade consistente em todo o website.</p>
                    @foreach(['primary'=>'Cor principal','secondary'=>'Cor secundária','background'=>'Fundo','text'=>'Cor do texto'] as $key=>$label)
                        <label class="mb-3 block text-xs font-bold">{{ $label }}<input wire:change="updateTheme('{{ $key }}',$event.target.value)" value="{{ $theme[$key] ?? '' }}" class="mt-1.5 w-full rounded-xl border-slate-200 text-sm"></label>
                    @endforeach
                    <label class="mb-3 block text-xs font-bold">Tipografia<select wire:change="updateTheme('font_body',$event.target.value)" class="mt-1.5 w-full rounded-xl border-slate-200 text-sm"><option @selected(($theme['font_body'] ?? 'Inter')==='Inter')>Inter</option><option @selected(($theme['font_body'] ?? '')==='Manrope')>Manrope</option><option @selected(($theme['font_body'] ?? '')==='DM Sans')>DM Sans</option><option @selected(($theme['font_body'] ?? '')==='Plus Jakarta Sans')>Plus Jakarta Sans</option></select></label>
                    <label class="mb-3 block text-xs font-bold">Raio dos cantos<input wire:change="updateTheme('radius',$event.target.value)" value="{{ $theme['radius'] ?? '1rem' }}" class="mt-1.5 w-full rounded-xl border-slate-200 text-sm"></label>
                    <button wire:click="save" class="w-full rounded-xl bg-slate-900 py-2.5 text-xs font-bold text-white">Guardar design</button>
                </div>
            @endif
        </aside>

        <main class="min-w-0 overflow-auto bg-slate-100">
            <div class="flex items-center justify-between border-b bg-white px-4 py-2 lg:hidden"><select wire:change="loadPage($event.target.value)" class="rounded-lg border-slate-200 text-sm font-bold">@foreach($pages as $page)<option value="{{ $page['id'] }}" @selected($pageId===$page['id'])>{{ $page['name'] }}</option>@endforeach</select><div class="flex gap-1">@foreach(['desktop','tablet','mobile'] as $key)<button wire:click="$set('device','{{ $key }}')" class="rounded-lg px-2 py-1 text-xs {{ $device===$key?'bg-slate-900 text-white':'bg-slate-100' }}">{{ ucfirst($key) }}</button>@endforeach</div></div>
            <div class="flex min-h-[calc(100vh-7rem)] justify-center p-4 md:p-8"><div class="w-full overflow-hidden rounded-2xl bg-white shadow-xl ring-1 ring-slate-200 {{ $device === 'mobile' ? 'max-w-[390px]' : ($device === 'tablet' ? 'max-w-[768px]' : 'max-w-[1280px]') }}" style="--finder-primary:{{ $theme['primary'] ?? '#635bff' }};--finder-secondary:{{ $theme['secondary'] ?? '#111827' }};--finder-bg:{{ $theme['background'] ?? '#ffffff' }};--finder-text:{{ $theme['text'] ?? '#111827' }}">
                <div class="flex items-center justify-between border-b px-5 py-3 text-xs text-slate-400"><span>{{ $this->currentPage()['name'] ?? 'Página' }}</span><span>{{ ucfirst($device) }}</span></div>
                @forelse($sections as $i=>$section)
                    @php($sectionModel = new SiteSection($section))
                    @php($sectionModel->id = (int) ($section['id'] ?? 0))
                    @php($document = SiteSectionDocument::fromSection($sectionModel))
                    <section draggable="true" x-on:dragstart="sectionDrag={{ $i }}" x-on:dragover.prevent x-on:drop="if(sectionDrag!==null && sectionDrag!=={{ $i }}) { $wire.moveSection(sectionDrag, sectionDrag < {{ $i }} ? 'down' : 'up'); sectionDrag=null }" wire:click="selectSection({{ $i }})" class="group relative border-2 {{ $selectedSection === $i ? 'border-blue-500 ring-2 ring-blue-100' : 'border-transparent hover:border-blue-200' }} {{ !$section['is_visible'] ? 'opacity-40' : '' }}">
                        <div class="absolute right-3 top-3 z-20 hidden gap-1 rounded-xl border bg-white p-1 shadow-lg group-hover:flex"><button wire:click.stop="moveSection({{ $i }},'up')">↑</button><button wire:click.stop="moveSection({{ $i }},'down')">↓</button><button wire:click.stop="duplicateSection({{ $i }})">⧉</button><button wire:click.stop="toggleSection({{ $i }})">{{ $section['is_visible'] ? '◌' : '●' }}</button><button wire:click.stop="deleteSection({{ $i }})" class="text-red-600">×</button></div>
                        <div class="min-h-28 px-6 py-10 md:px-12">
                            @if(is_array($section['settings']['builder_document'] ?? null))
                                @include('livewire.builder-document-renderer', ['document' => $section['settings']['builder_document'], 'device' => $device])
                            @else
                                @switch($section['type'])
                                    @case('hero')<div class="mx-auto max-w-3xl text-center"><h1 class="text-4xl font-black tracking-tight md:text-6xl">{{ $section['content']['title'] ?? 'Título principal' }}</h1><p class="mt-4 text-lg text-slate-600">{{ $section['content']['subtitle'] ?? '' }}</p><span class="mt-7 inline-block rounded-xl px-5 py-3 text-sm font-bold text-white" style="background:var(--finder-primary)">{{ $section['content']['button_label'] ?? 'Saber mais' }}</span></div>@break
                                    @case('text')<div class="mx-auto max-w-3xl"><h2 class="text-3xl font-black">{{ $section['content']['title'] ?? 'Título' }}</h2><p class="mt-4 whitespace-pre-line leading-7 text-slate-600">{{ $section['content']['body'] ?? 'Texto da secção.' }}</p></div>@break
                                    @case('image')<div class="mx-auto flex min-h-56 max-w-4xl items-center justify-center overflow-hidden rounded-2xl bg-slate-100 text-sm text-slate-400">@if(!empty($section['content']['url']))<img src="{{ $section['content']['url'] }}" alt="{{ $section['content']['alt'] ?? '' }}" class="max-h-[500px] w-full object-cover">@else Adiciona uma imagem @endif</div>@break
                                    @case('button')<div class="text-center"><span class="inline-block rounded-xl px-5 py-3 text-sm font-bold text-white" style="background:var(--finder-primary)">{{ $section['content']['label'] ?? 'Botão' }}</span></div>@break
                                    @case('feature_grid')<div class="mx-auto max-w-5xl"><h2 class="text-center text-3xl font-black">{{ $section['content']['title'] ?? 'Benefícios' }}</h2><div class="mt-8 grid gap-4 md:grid-cols-3">@foreach(($section['content']['items'] ?? []) as $item)<div class="rounded-2xl border p-6"><b>{{ $item['title'] ?? 'Benefício' }}</b><p class="mt-2 text-sm text-slate-500">{{ $item['description'] ?? '' }}</p></div>@endforeach</div></div>@break
                                    @case('testimonials')<div class="mx-auto max-w-4xl"><h2 class="text-center text-3xl font-black">{{ $section['content']['title'] ?? 'Testemunhos' }}</h2><div class="mt-7 grid gap-4 md:grid-cols-2">@foreach(($section['content']['items'] ?? []) as $item)<blockquote class="rounded-2xl bg-slate-50 p-6"><p class="text-slate-600">“{{ $item['quote'] ?? '' }}”</p><b class="mt-4 block text-sm">{{ $item['name'] ?? 'Cliente' }}</b></blockquote>@endforeach</div></div>@break
                                    @case('faq')<div class="mx-auto max-w-3xl"><h2 class="text-3xl font-black">{{ $section['content']['title'] ?? 'Perguntas frequentes' }}</h2><div class="mt-6 space-y-3">@foreach(($section['content']['items'] ?? []) as $item)<div class="rounded-xl border p-4"><b>{{ $item['question'] ?? 'Pergunta?' }}</b><p class="mt-2 text-sm text-slate-500">{{ $item['answer'] ?? '' }}</p></div>@endforeach</div></div>@break
                                    @case('pricing')<div class="mx-auto max-w-5xl"><h2 class="text-center text-3xl font-black">{{ $section['content']['title'] ?? 'Planos' }}</h2><div class="mt-8 grid gap-4 md:grid-cols-3">@foreach(($section['content']['items'] ?? []) as $item)<div class="rounded-2xl border p-6"><b>{{ $item['name'] ?? 'Plano' }}</b><div class="mt-3 text-3xl font-black">{{ $item['price'] ?? '' }}</div><p class="mt-2 text-sm text-slate-500">{{ $item['description'] ?? '' }}</p></div>@endforeach</div></div>@break
                                    @case('cta')<div class="mx-auto max-w-4xl rounded-3xl p-10 text-center text-white" style="background:var(--finder-secondary)"><h2 class="text-3xl font-black">{{ $section['content']['title'] ?? 'Vamos falar?' }}</h2><p class="mt-3 text-white/70">{{ $section['content']['description'] ?? '' }}</p></div>@break
                                    @default<div class="mx-auto max-w-4xl rounded-2xl border border-dashed p-10 text-center"><div class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ $section['label'] }}</div><div class="mt-2 text-lg font-bold">{{ Str::headline($section['type']) }}</div></div>
                                @endswitch
                            @endif
                        </div>
                    </section>
                @empty
                    <div class="flex min-h-[500px] items-center justify-center p-8"><div class="max-w-md text-center"><div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-blue-50 text-2xl">✦</div><h2 class="mt-5 text-2xl font-black">Começa a criar o teu website</h2><p class="mt-2 text-sm leading-6 text-slate-500">Escolhe um template, usa a IA ou adiciona uma secção.</p><div class="mt-5 flex justify-center gap-2"><button wire:click="$set('showTemplates',true)" class="rounded-xl border px-4 py-2 text-sm font-bold">Templates</button><button wire:click="$set('showAi',true)" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white">Criar com IA</button></div></div></div>
                @endforelse
            </div></div>
        </main>

        <aside class="hidden border-l bg-white lg:block">
            @php($selected = $selectedSection !== null ? ($sections[$selectedSection] ?? null) : null)
            @php($selectedModel = $selected ? new SiteSection($selected) : null)
            @php($selectedModel?->setAttribute('id', (int) ($selected['id'] ?? 0)))
            @php($selectedDocument = $selectedModel ? SiteSectionDocument::fromSection($selectedModel) : null)
            @php($selectedElement = $selectedDocument ? collect($selectedDocument['nodes'][0]['children'] ?? [])->firstWhere('id', $selectedElementId) : null)
            <div class="border-b px-4 py-3"><b class="text-sm">{{ $selected ? ($selected['label'] ?: Str::headline($selected['type'])) : 'Inspector' }}</b><div class="text-[11px] text-slate-500">Seleciona uma secção ou elemento para editar.</div></div>
            @if($selected)
                <div class="space-y-5 p-4">
                    <label class="block text-xs font-bold">Nome da secção<input wire:model.live="sections.{{ $selectedSection }}.label" wire:change="$set('dirty',true)" class="mt-1.5 w-full rounded-xl border-slate-200 text-sm"></label>

                    <div><div class="flex items-center justify-between"><b class="text-xs">Elementos</b><span class="text-[10px] text-slate-400">Clica para editar</span></div><div class="mt-2 space-y-1">
                        @foreach(($selectedDocument['nodes'][0]['children'] ?? []) as $element)
                            <button wire:click="selectElement('{{ $element['id'] }}')" class="flex w-full items-center gap-1 rounded-xl border p-2 text-left {{ $selectedElementId === $element['id'] ? 'border-blue-300 bg-blue-50' : 'bg-slate-50' }}"><span class="min-w-0 flex-1 truncate text-xs font-bold">{{ ElementRegistry::get($element['type'])['label'] }}</span><span class="text-[10px] text-slate-400">{{ $element['type'] }}</span></button>
                        @endforeach
                    </div></div>

                    @if($selectedElement)
                        <div class="rounded-2xl border border-blue-100 bg-blue-50/50 p-3"><div class="mb-2 flex items-center justify-between"><b class="text-xs">Editar elemento</b><button wire:click="$set('selectedElementId',null)" class="text-xs text-slate-400">×</button></div>
                            @foreach($selectedElement['content'] ?? [] as $key=>$value)
                                @if(is_string($value) && in_array($key, ['text','label','url','alt','caption','title','author','button_label','button_url'], true))
                                    <label class="mt-3 block text-[11px] font-bold capitalize">{{ str_replace('_',' ',$key) }}<textarea wire:change="updateSelectedElement('{{ $key }}',$event.target.value)" rows="{{ strlen($value) > 120 ? 4 : 2 }}" class="mt-1 w-full rounded-xl border-slate-200 bg-white text-xs">{{ $value }}</textarea></label>
                                @endif
                            @endforeach
                        </div>
                    @endif

                    <div><div class="flex items-center justify-between"><b class="text-xs">Adicionar elemento</b><span class="text-[10px] text-slate-400">Blocos internos</span></div><div class="mt-2 grid grid-cols-2 gap-2">
                        @foreach(ElementRegistry::all() as $type=>$definition)<button wire:click="addElement('{{ $type }}')" class="rounded-xl border px-2 py-2 text-left text-[11px] font-semibold hover:border-blue-300">{{ $definition['label'] }}</button>@endforeach
                    </div></div>

                    <div><b class="text-xs">Conteúdo da secção</b>@foreach($selected['content'] ?? [] as $key=>$value)@if(is_string($value))<label class="mt-3 block text-[11px] font-bold capitalize">{{ str_replace('_',' ',$key) }}<textarea wire:model.live="sections.{{ $selectedSection }}.content.{{ $key }}" wire:change="$set('dirty',true)" rows="{{ strlen($value)>100 ? 4 : 2 }}" class="mt-1 w-full rounded-xl border-slate-200 text-xs"></textarea></label>@endif @endforeach</div>

                    <div class="border-t pt-4"><b class="text-xs">SEO da página</b>
                        <label class="mt-3 block text-[11px] font-bold">Título SEO<input wire:change="updatePageSeo('title',$event.target.value)" value="{{ $pageSeo['title'] ?? '' }}" placeholder="Título para motores de pesquisa" class="mt-1 w-full rounded-xl border-slate-200 text-xs"></label>
                        <label class="mt-3 block text-[11px] font-bold">Meta descrição<textarea wire:change="updatePageSeo('description',$event.target.value)" rows="3" placeholder="Descrição curta da página" class="mt-1 w-full rounded-xl border-slate-200 text-xs">{{ $pageSeo['description'] ?? '' }}</textarea></label>
                    </div>

                    <button wire:click="save" class="w-full rounded-xl bg-slate-900 py-2.5 text-xs font-bold text-white">{{ $dirty ? 'Guardar alterações' : 'Tudo guardado' }}</button>
                </div>
            @else
                <div class="p-6 text-center text-sm text-slate-500">Seleciona uma secção no canvas para abrir o inspector.</div>
            @endif
        </aside>
    </div>

    @if($showTemplates)<div class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/50 p-4" wire:click.self="$set('showTemplates',false)"><div class="max-h-[85vh] w-full max-w-4xl overflow-auto rounded-3xl bg-white p-6 shadow-2xl"><div class="flex justify-between"><div><h2 class="text-2xl font-black">Templates profissionais</h2><p class="mt-1 text-sm text-slate-500">Estrutura pronta a personalizar.</p></div><button wire:click="$set('showTemplates',false)" class="text-2xl text-slate-400">×</button></div><div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">@foreach(WebsiteTemplates::all() as $category=>$template)<button wire:click="applyTemplate('{{ $category }}')" class="rounded-2xl border p-5 text-left hover:border-blue-300 hover:shadow-lg"><b class="text-sm">{{ $template['name'] ?? Str::headline($category) }}</b><p class="mt-1 text-xs leading-5 text-slate-500">{{ $template['description'] ?? 'Website profissional pronto a editar.' }}</p><div class="mt-3 text-[10px] font-bold uppercase text-blue-600">{{ count($template['pages'] ?? []) }} páginas</div></button>@endforeach</div></div></div>@endif

    @if($showAi)<div class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/50 p-4" wire:click.self="$set('showAi',false)"><div class="w-full max-w-2xl rounded-3xl bg-white p-6 shadow-2xl"><div class="flex justify-between"><div><h2 class="text-2xl font-black">Criar website com IA</h2><p class="mt-1 text-sm text-slate-500">Descreve o negócio, estilo e objectivo. A IA cria a estrutura.</p></div><button wire:click="$set('showAi',false)" class="text-2xl text-slate-400">×</button></div><textarea wire:model="aiBrief" rows="7" class="mt-6 w-full rounded-2xl border-slate-200 p-4 text-sm" placeholder="Quero um site para uma barbearia moderna em Lisboa..."></textarea><div class="mt-4 flex justify-end gap-2"><button wire:click="$set('showAi',false)" class="rounded-xl border px-4 py-2 text-sm font-bold">Cancelar</button><button wire:click="generateWithAi" wire:loading.attr="disabled" class="rounded-xl bg-slate-900 px-5 py-2 text-sm font-bold">Criar website</button></div></div></div>@endif

    @if($showPublish)<div class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/50 p-4" wire:click.self="$set('showPublish',false)"><div class="w-full max-w-2xl rounded-3xl bg-white p-6 shadow-2xl"><div class="flex justify-between"><div><h2 class="text-2xl font-black">Verificar e publicar</h2><p class="mt-1 text-sm text-slate-500">Erros impedem a publicação; recomendações não impedem.</p></div><button wire:click="$set('showPublish',false)" class="text-2xl text-slate-400">×</button></div><div class="mt-6 space-y-2">@foreach($publishChecks as $check)<div class="rounded-xl border p-3 {{ $check['level'] === 'error' ? 'border-red-200 bg-red-50' : ($check['level'] === 'success' ? 'border-emerald-200 bg-emerald-50' : 'border-amber-200 bg-amber-50') }}"><b class="text-sm">{{ $check['label'] }}</b><p class="mt-1 text-xs text-slate-600">{{ $check['message'] }}</p></div>@endforeach</div><div class="mt-6 flex justify-end gap-2"><button wire:click="$set('showPublish',false)" class="rounded-xl border px-4 py-2 text-sm font-bold">Fechar</button><button wire:click="publish" wire:loading.attr="disabled" class="rounded-xl bg-slate-900 px-5 py-2 text-sm font-bold text-white">Publicar agora</button></div></div></div>@endif

    @if($showVersions)<div class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/50 p-4" wire:click.self="$set('showVersions',false)"><div class="w-full max-w-2xl rounded-3xl bg-white p-6 shadow-2xl"><div class="flex justify-between"><div><h2 class="text-2xl font-black">Histórico</h2><p class="mt-1 text-sm text-slate-500">Recupera versões anteriores do website.</p></div><button wire:click="$set('showVersions',false)" class="text-2xl text-slate-400">×</button></div><button wire:click="saveVersion('Versão manual')" class="mt-5 w-full rounded-xl bg-slate-900 py-2.5 text-sm font-bold text-white">Guardar versão atual</button><div class="mt-5 max-h-80 space-y-2 overflow-auto">@forelse($versions as $version)<div class="flex items-center justify-between rounded-xl border p-3"><div><b class="text-sm">v{{ $version['version_number'] }} · {{ $version['label'] }}</b><div class="text-xs text-slate-500">{{ $version['created_at'] }} @if($version['created_by']) · {{ $version['created_by'] }} @endif</div></div><button wire:click="restoreVersion({{ $version['id'] }})" wire:confirm="Restaurar esta versão? As alterações actuais serão substituídas." class="rounded-lg border px-3 py-1.5 text-xs font-bold">Restaurar</button></div>@empty<div class="rounded-xl bg-slate-50 p-5 text-center text-sm text-slate-500">Sem versões guardadas.</div>@endforelse</div></div></div>@endif
</div>
