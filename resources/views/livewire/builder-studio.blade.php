<div class="min-h-screen bg-slate-100 text-slate-900" x-data="{ pageDrag:null, sectionDrag:null }" x-on:open-builder-preview.window="window.open($event.detail.url, '_blank', 'noopener,noreferrer')">
    <div class="sticky top-0 z-50 border-b border-slate-200 bg-white/95 backdrop-blur">
        <div class="flex h-16 items-center gap-3 px-4 lg:px-6">
            <a href="{{ route('dashboard') }}" class="flex shrink-0 items-center gap-2 rounded-lg px-2 py-1.5 text-sm font-semibold hover:bg-slate-100" aria-label="Voltar ao dashboard">
                <span class="text-lg">←</span><span class="hidden sm:inline">Sair do editor</span>
            </a>
            <div class="hidden h-7 w-px bg-slate-200 md:block"></div>
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2">
                    <span class="truncate font-semibold">{{ $site->name }}</span>
                    @if($site->is_published)
                        <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">Online</span>
                    @else
                        <span class="rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-semibold text-amber-700">Rascunho</span>
                    @endif
                </div>
                <div class="text-xs text-slate-500">{{ $currentPage()['name'] ?? 'Página' }} · {{ $statusMessage }}</div>
            </div>
            <div class="hidden items-center gap-1 rounded-xl border border-slate-200 bg-slate-50 p-1 md:flex">
                @foreach(['desktop'=>'Computador','tablet'=>'Tablet','mobile'=>'Telemóvel'] as $key=>$label)
                    <button wire:click="$set('device','{{ $key }}')" class="rounded-lg px-3 py-1.5 text-xs font-semibold {{ $device === $key ? 'bg-white shadow-sm text-slate-900' : 'text-slate-500' }}" title="{{ $label }}">{{ $key === 'desktop' ? '▣' : ($key === 'tablet' ? '▤' : '▥') }} {{ $label }}</button>
                @endforeach
            </div>
            <button wire:click="save" class="hidden rounded-xl px-3 py-2 text-sm font-semibold md:block {{ $dirty ? 'bg-slate-900 text-white hover:bg-slate-800' : 'text-slate-500' }}">{{ $dirty ? 'Guardar' : 'Guardado' }}</button>
            <button wire:click="openPreview" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold hover:bg-slate-50">Pré-visualizar</button>
            <button wire:click="$set('showPublish',true)" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">{{ $site->is_published ? 'Publicar alterações' : 'Publicar' }}</button>
        </div>
    </div>

    <div class="grid min-h-[calc(100vh-4rem)] grid-cols-1 lg:grid-cols-[240px_minmax(0,1fr)_320px]">
        <aside class="hidden border-r border-slate-200 bg-white lg:block">
            <div class="border-b border-slate-100 p-3">
                <div class="grid grid-cols-3 gap-1 rounded-xl bg-slate-100 p-1">
                    @foreach(['pages'=>'Páginas','elements'=>'Elementos','design'=>'Design'] as $key=>$label)
                        <button wire:click="$set('panel','{{ $key }}')" class="rounded-lg px-2 py-2 text-[11px] font-semibold {{ $panel === $key ? 'bg-white shadow-sm text-slate-900' : 'text-slate-500' }}">{{ $label }}</button>
                    @endforeach
                </div>
            </div>

            @if($panel === 'pages')
                <div class="p-3">
                    <div class="mb-3 flex items-center justify-between">
                        <div><div class="text-sm font-bold">Estrutura</div><div class="text-[11px] text-slate-500">Arrasta para reordenar</div></div>
                        <button wire:click="createPage" class="rounded-lg bg-slate-900 px-2.5 py-1.5 text-xs font-bold text-white">+ Página</button>
                    </div>
                    <div class="space-y-1" x-data="{ dragging:null }">
                        @foreach($pages as $page)
                            <div draggable="true" x-on:dragstart="dragging={{ $page['id'] }}" x-on:dragover.prevent x-on:drop="if(dragging && dragging !== {{ $page['id'] }}) { const ids=[...$el.parentElement.children].map(e=>Number(e.dataset.id)); const from=ids.indexOf(dragging); const to=ids.indexOf({{ $page['id'] }}); ids.splice(to,0,ids.splice(from,1)[0]); $wire.reorderPages(ids); }" data-id="{{ $page['id'] }}" class="group flex cursor-grab items-center gap-2 rounded-xl border px-2 py-2 {{ $pageId === $page['id'] ? 'border-slate-300 bg-slate-100' : 'border-transparent hover:border-slate-200 hover:bg-slate-50' }}">
                                <span class="text-slate-400">⠿</span>
                                <button wire:click="loadPage({{ $page['id'] }})" class="min-w-0 flex-1 text-left">
                                    <div class="truncate text-sm font-semibold">{{ $page['name'] }}</div>
                                    <div class="truncate text-[10px] text-slate-500">/{{ $page['slug'] }} @if($page['is_homepage']) · Home @endif</div>
                                </button>
                                <div class="hidden items-center gap-1 group-hover:flex">
                                    @if(!$page['is_homepage']) <button wire:click="setHomepage({{ $page['id'] }})" title="Definir como Home" class="rounded p-1 text-xs hover:bg-white">⌂</button>@endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4 grid grid-cols-2 gap-2">
                        <button wire:click="duplicatePage" class="rounded-xl border border-slate-200 px-2 py-2 text-xs font-semibold hover:bg-slate-50">Duplicar</button>
                        <button wire:click="deletePage" class="rounded-xl border border-red-100 px-2 py-2 text-xs font-semibold text-red-600 hover:bg-red-50">Eliminar</button>
                    </div>
                    <div class="mt-5 rounded-2xl bg-slate-900 p-3 text-white">
                        <div class="text-xs font-bold">Precisas de começar mais rápido?</div>
                        <div class="mt-1 text-[11px] leading-4 text-slate-300">Usa um template completo ou descreve o website à IA.</div>
                        <div class="mt-3 grid grid-cols-2 gap-2"><button wire:click="$set('showTemplates',true)" class="rounded-lg bg-white/10 px-2 py-2 text-[11px] font-bold hover:bg-white/20">Templates</button><button wire:click="$set('showAi',true)" class="rounded-lg bg-white px-2 py-2 text-[11px] font-bold text-slate-900">Criar com IA</button></div>
                    </div>
                </div>
            @elseif($panel === 'elements')
                <div class="p-3">
                    <div class="mb-3"><div class="text-sm font-bold">Elementos</div><div class="text-[11px] text-slate-500">Adiciona conteúdo à página</div></div>
                    @foreach(['hero'=>'Hero','text'=>'Texto','image'=>'Imagem','button'=>'Botão','feature_grid'=>'Benefícios','card'=>'Cartão','testimonials'=>'Testemunhos','faq'=>'FAQ','gallery'=>'Galeria','contact_form'=>'Formulário','pricing'=>'Preços','product_grid'=>'Produtos','blog_posts'=>'Artigos','video'=>'Vídeo','map'=>'Mapa','newsletter'=>'Newsletter','cta'=>'CTA'] as $type=>$label)
                        <button wire:click="addSection('{{ $type }}')" class="mb-1.5 flex w-full items-center justify-between rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-left text-xs font-semibold hover:border-slate-300 hover:bg-slate-50"><span>{{ $label }}</span><span class="text-slate-400">+</span></button>
                    @endforeach
                </div>
            @else
                <div class="p-3">
                    <div class="mb-4"><div class="text-sm font-bold">Design global</div><div class="text-[11px] text-slate-500">Uma alteração aqui aplica-se ao website.</div></div>
                    <label class="mb-3 block text-xs font-semibold">Cor principal<input wire:change="updateTheme('primary',$event.target.value)" type="text" value="{{ $theme['primary'] ?? '#2563eb' }}" class="mt-1.5 w-full rounded-xl border-slate-200 text-sm"></label>
                    <label class="mb-3 block text-xs font-semibold">Cor secundária<input wire:change="updateTheme('secondary',$event.target.value)" type="text" value="{{ $theme['secondary'] ?? '#0f172a' }}" class="mt-1.5 w-full rounded-xl border-slate-200 text-sm"></label>
                    <label class="mb-3 block text-xs font-semibold">Fundo<input wire:change="updateTheme('background',$event.target.value)" type="text" value="{{ $theme['background'] ?? '#ffffff' }}" class="mt-1.5 w-full rounded-xl border-slate-200 text-sm"></label>
                    <label class="mb-3 block text-xs font-semibold">Texto<input wire:change="updateTheme('text',$event.target.value)" type="text" value="{{ $theme['text'] ?? '#0f172a' }}" class="mt-1.5 w-full rounded-xl border-slate-200 text-sm"></label>
                    <label class="mb-3 block text-xs font-semibold">Tipografia<select wire:change="updateTheme('font',$event.target.value)" class="mt-1.5 w-full rounded-xl border-slate-200 text-sm"><option>Inter</option><option>Manrope</option><option>DM Sans</option><option>Plus Jakarta Sans</option></select></label>
                    <button wire:click="save" class="w-full rounded-xl bg-slate-900 py-2.5 text-xs font-bold text-white">Aplicar design</button>
                </div>
            @endif
        </aside>

        <main class="min-w-0 overflow-hidden bg-slate-100">
            <div class="flex items-center justify-between border-b border-slate-200 bg-white px-4 py-2 lg:hidden">
                <select wire:change="loadPage($event.target.value)" class="rounded-lg border-slate-200 text-sm font-semibold">@foreach($pages as $page)<option value="{{ $page['id'] }}" @selected($pageId===$page['id'])>{{ $page['name'] }}</option>@endforeach</select>
                <div class="flex gap-1">@foreach(['desktop','tablet','mobile'] as $key)<button wire:click="$set('device','{{ $key }}')" class="rounded-lg px-2 py-1 text-xs {{ $device===$key?'bg-slate-900 text-white':'bg-slate-100' }}">{{ ucfirst($key) }}</button>@endforeach</div>
            </div>
            <div class="flex min-h-[calc(100vh-7rem)] items-start justify-center overflow-auto p-4 md:p-8">
                <div class="w-full overflow-hidden rounded-2xl bg-white shadow-xl ring-1 ring-slate-200 transition-all {{ $device === 'mobile' ? 'max-w-[390px]' : ($device === 'tablet' ? 'max-w-[768px]' : 'max-w-[1280px]') }}" style="--finder-primary: {{ $theme['primary'] ?? '#2563eb' }}; --finder-secondary: {{ $theme['secondary'] ?? '#0f172a' }}; --finder-bg: {{ $theme['background'] ?? '#fff' }}; --finder-text: {{ $theme['text'] ?? '#0f172a' }}">
                    <div class="border-b border-slate-100 bg-white px-5 py-3 text-xs text-slate-400">Pré-visualização do website · {{ $currentPage()['name'] ?? 'Página' }}</div>
                    @forelse($sections as $i=>$section)
                        @if($section['is_visible'])
                            <section draggable="true" x-on:dragstart="sectionDrag={{ $i }}" x-on:dragover.prevent x-on:drop="if(sectionDrag!==null && sectionDrag!=={{ $i }}) { $wire.moveSection(sectionDrag, sectionDrag < {{ $i }} ? 'down' : 'up'); sectionDrag=null }" wire:click="selectSection({{ $i }})" class="group relative cursor-pointer border-2 transition {{ $selectedSection === $i ? 'border-dashed border-blue-500 ring-2 ring-blue-100' : 'border-transparent hover:border-blue-200' }}">
                                <div class="absolute right-3 top-3 z-10 hidden items-center gap-1 rounded-xl border border-slate-200 bg-white p-1 shadow-lg group-hover:flex">
                                    <button wire:click.stop="moveSection({{ $i }},'up')" class="rounded-lg px-2 py-1 text-xs hover:bg-slate-100">↑</button>
                                    <button wire:click.stop="moveSection({{ $i }},'down')" class="rounded-lg px-2 py-1 text-xs hover:bg-slate-100">↓</button>
                                    <button wire:click.stop="duplicateSection({{ $i }})" class="rounded-lg px-2 py-1 text-xs hover:bg-slate-100">⧉</button>
                                    <button wire:click.stop="toggleSection({{ $i }})" class="rounded-lg px-2 py-1 text-xs hover:bg-slate-100">◌</button>
                                    <button wire:click.stop="deleteSection({{ $i }})" class="rounded-lg px-2 py-1 text-xs text-red-600 hover:bg-red-50">×</button>
                                </div>
                                <div class="min-h-24 px-6 py-12 md:px-12">
                                    @switch($section['type'])
                                        @case('hero') <div class="mx-auto max-w-3xl text-center"><h1 class="text-4xl font-black tracking-tight md:text-6xl">{{ $section['content']['title'] ?? 'Título principal' }}</h1><p class="mx-auto mt-4 max-w-2xl text-lg text-slate-600">{{ $section['content']['subtitle'] ?? 'Explica aqui a proposta de valor.' }}</p><button class="mt-7 rounded-xl px-5 py-3 text-sm font-bold text-white" style="background:var(--finder-primary)">{{ $section['content']['button_label'] ?? 'Saber mais' }}</button></div>@break
                                        @case('text') <div class="mx-auto max-w-3xl"><h2 class="text-3xl font-black">{{ $section['content']['title'] ?? 'Título' }}</h2><p class="mt-4 whitespace-pre-line leading-7 text-slate-600">{{ $section['content']['body'] ?? 'Texto da secção.' }}</p></div>@break
                                        @case('image') <div class="mx-auto flex min-h-56 max-w-4xl items-center justify-center rounded-2xl bg-slate-100 text-sm text-slate-400">{{ $section['content']['url'] ? 'Imagem' : 'Adiciona uma imagem' }}</div>@break
                                        @case('button') <div class="text-center"><button class="rounded-xl px-5 py-3 text-sm font-bold text-white" style="background:var(--finder-primary)">{{ $section['content']['label'] ?? 'Botão' }}</button></div>@break
                                        @case('feature_grid') <div class="mx-auto max-w-5xl"><h2 class="text-center text-3xl font-black">{{ $section['content']['title'] ?? 'Benefícios' }}</h2><div class="mt-8 grid gap-4 md:grid-cols-3">@foreach(($section['content']['items'] ?? []) as $item)<div class="rounded-2xl border border-slate-200 p-6"><div class="font-bold">{{ $item['title'] ?? 'Benefício' }}</div><div class="mt-2 text-sm leading-6 text-slate-500">{{ $item['description'] ?? '' }}</div></div>@endforeach</div></div>@break
                                        @case('testimonials') <div class="mx-auto max-w-4xl text-center"><h2 class="text-3xl font-black">{{ $section['content']['title'] ?? 'Testemunhos' }}</h2><div class="mt-7 grid gap-4 md:grid-cols-2">@foreach(($section['content']['items'] ?? []) as $item)<blockquote class="rounded-2xl bg-slate-50 p-6 text-left"><p class="text-slate-600">“{{ $item['quote'] ?? '' }}”</p><footer class="mt-4 text-sm font-bold">{{ $item['name'] ?? 'Cliente' }} · {{ $item['role'] ?? '' }}</footer></blockquote>@endforeach</div></div>@break
                                        @case('faq') <div class="mx-auto max-w-3xl"><h2 class="text-3xl font-black">{{ $section['content']['title'] ?? 'Perguntas frequentes' }}</h2><div class="mt-6 space-y-3">@foreach(($section['content']['items'] ?? []) as $item)<div class="rounded-xl border border-slate-200 p-4"><div class="font-bold">{{ $item['question'] ?? 'Pergunta?' }}</div><div class="mt-2 text-sm text-slate-500">{{ $item['answer'] ?? '' }}</div></div>@endforeach</div></div>@break
                                        @case('pricing') <div class="mx-auto max-w-5xl"><h2 class="text-center text-3xl font-black">{{ $section['content']['title'] ?? 'Planos' }}</h2><div class="mt-8 grid gap-4 md:grid-cols-3">@foreach(($section['content']['items'] ?? []) as $item)<div class="rounded-2xl border border-slate-200 p-6"><div class="font-bold">{{ $item['name'] ?? 'Plano' }}</div><div class="mt-3 text-3xl font-black">{{ $item['price'] ?? '' }}</div><p class="mt-2 text-sm text-slate-500">{{ $item['description'] ?? '' }}</p></div>@endforeach</div></div>@break
                                        @case('contact_form') <div class="mx-auto max-w-3xl rounded-2xl border border-slate-200 p-6"><h2 class="text-3xl font-black">{{ $section['content']['title'] ?? 'Contacta-nos' }}</h2><p class="mt-2 text-slate-500">{{ $section['content']['description'] ?? '' }}</p><div class="mt-6 grid gap-3 md:grid-cols-2"><div class="h-11 rounded-xl bg-slate-100"></div><div class="h-11 rounded-xl bg-slate-100"></div><div class="h-24 rounded-xl bg-slate-100 md:col-span-2"></div></div></div>@break
                                        @case('cta') <div class="mx-auto max-w-4xl rounded-3xl p-10 text-center text-white" style="background:var(--finder-secondary)"><h2 class="text-3xl font-black">{{ $section['content']['title'] ?? 'Vamos falar?' }}</h2><p class="mt-3 text-white/70">{{ $section['content']['description'] ?? '' }}</p><button class="mt-6 rounded-xl bg-white px-5 py-3 text-sm font-bold text-slate-900">{{ $section['content']['button_label'] ?? 'Contactar' }}</button></div>@break
                                        @default <div class="mx-auto max-w-5xl text-center"><div class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ Str::headline($section['type']) }}</div><h2 class="mt-2 text-2xl font-black">{{ $section['content']['title'] ?? $section['label'] }}</h2><p class="mt-2 text-sm text-slate-500">Esta secção está pronta para ser personalizada.</p></div>
                                    @endswitch
                                </div>
                            </section>
                        @endif
                    @empty
                        <div class="flex min-h-[60vh] items-center justify-center p-10 text-center"><div><div class="text-5xl">✦</div><h2 class="mt-4 text-2xl font-black">Começa pelo que importa</h2><p class="mx-auto mt-2 max-w-md text-sm text-slate-500">Escolhe um template, cria com IA ou adiciona o primeiro elemento. O Finder trata do resto.</p><div class="mt-5 flex justify-center gap-2"><button wire:click="$set('showTemplates',true)" class="rounded-xl border px-4 py-2 text-sm font-bold">Templates</button><button wire:click="$set('showAi',true)" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white">Criar com IA</button></div></div></div>
                    @endforelse
                </div>
            </div>
        </main>

        <aside class="hidden border-l border-slate-200 bg-white lg:block">
            @if($selectedSection !== null && isset($sections[$selectedSection]))
                @php($selected = $sections[$selectedSection])
                <div class="border-b border-slate-100 p-4"><div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">A editar</div><div class="mt-1 text-lg font-black">{{ $selected['label'] }}</div><div class="text-xs text-slate-500">{{ Str::headline($selected['type']) }} · Secção {{ $selectedSection + 1 }}</div></div>
                <div class="space-y-5 p-4">
                    <div><label class="text-xs font-bold">Nome da secção</label><input wire:model.live="sections.{{ $selectedSection }}.label" class="mt-1.5 w-full rounded-xl border-slate-200 text-sm"></div>
                    @foreach(['title'=>'Título','subtitle'=>'Subtítulo','body'=>'Texto','description'=>'Descrição','button_label'=>'Texto do botão','button_url'=>'Link do botão','alt'=>'Texto alternativo','address'=>'Morada'] as $field=>$label)
                        @if(array_key_exists($field, $selected['content']))<div><label class="text-xs font-bold">{{ $label }}</label><textarea wire:model.live="sections.{{ $selectedSection }}.content.{{ $field }}" rows="{{ in_array($field,['body','description']) ? 4 : 2 }}" class="mt-1.5 w-full rounded-xl border-slate-200 text-sm"></textarea></div>@endif
                    @endforeach
                    <div class="rounded-2xl border border-slate-200 p-3"><div class="text-xs font-bold">Visibilidade</div><button wire:click="toggleSection({{ $selectedSection }})" class="mt-2 w-full rounded-xl bg-slate-100 px-3 py-2 text-xs font-bold">{{ $selected['is_visible'] ? 'Visível no website' : 'Oculto no website' }}</button></div>
                    <div class="grid grid-cols-2 gap-2"><button wire:click="moveSection({{ $selectedSection }},'up')" class="rounded-xl border py-2 text-xs font-bold">↑ Mover</button><button wire:click="duplicateSection({{ $selectedSection }})" class="rounded-xl border py-2 text-xs font-bold">⧉ Duplicar</button></div>
                    <button wire:click="deleteSection({{ $selectedSection }})" class="w-full rounded-xl border border-red-100 py-2 text-xs font-bold text-red-600">Eliminar secção</button>
                </div>
            @else
                <div class="p-5"><div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Página</div><h2 class="mt-1 text-xl font-black">{{ $currentPage()['name'] ?? 'Página' }}</h2><p class="mt-2 text-sm text-slate-500">Clica numa secção para editar. Tudo o que alterares aqui pode ser revisto antes de publicar.</p></div>
            @endif
            <div class="border-t border-slate-100 p-4"><div class="text-xs font-bold">SEO desta página</div><input wire:model.live="pageSeo.title" placeholder="Título SEO" class="mt-2 w-full rounded-xl border-slate-200 text-xs"><textarea wire:model.live="pageSeo.description" placeholder="Meta descrição" rows="3" class="mt-2 w-full rounded-xl border-slate-200 text-xs"></textarea><input wire:model.live="pageSeo.og_image" placeholder="Imagem social (URL)" class="mt-2 w-full rounded-xl border-slate-200 text-xs"></div>
        </aside>
    </div>

    <div class="fixed inset-x-0 bottom-0 z-40 border-t border-slate-200 bg-white/95 p-2 backdrop-blur lg:hidden"><div class="grid grid-cols-4 gap-1"><button wire:click="$set('panel','pages')" class="rounded-lg py-2 text-xs font-bold">Páginas</button><button wire:click="$set('panel','elements')" class="rounded-lg py-2 text-xs font-bold">Elementos</button><button wire:click="$set('panel','design')" class="rounded-lg py-2 text-xs font-bold">Design</button><button wire:click="$set('showPublish',true)" class="rounded-lg bg-slate-900 py-2 text-xs font-bold text-white">Publicar</button></div></div>

    @if($showAi)
        <div class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/60 p-4"><div class="w-full max-w-2xl rounded-3xl bg-white p-6 shadow-2xl"><div class="flex justify-between"><div><div class="text-xs font-bold uppercase tracking-wider text-blue-600">Finder AI</div><h2 class="mt-1 text-2xl font-black">Descreve o website que queres</h2><p class="mt-1 text-sm text-slate-500">A IA cria páginas, secções e conteúdo inicial. Depois podes editar tudo visualmente.</p></div><button wire:click="$set('showAi',false)" class="text-2xl text-slate-400">×</button></div><textarea wire:model="aiBrief" rows="7" placeholder="Ex.: Quero um site para uma barbearia moderna em Lisboa, com marca premium, serviços, preços, testemunhos e formulário de contacto." class="mt-5 w-full rounded-2xl border-slate-200 p-4 text-sm"></textarea><div class="mt-4 flex justify-end gap-2"><button wire:click="$set('showAi',false)" class="rounded-xl border px-4 py-2 text-sm font-bold">Cancelar</button><button wire:click="generateWithAi" wire:loading.attr="disabled" class="rounded-xl bg-slate-900 px-5 py-2 text-sm font-bold text-white"><span wire:loading wire:target="generateWithAi">A criar…</span><span wire:loading.remove wire:target="generateWithAi">Criar website</span></button></div></div></div>
    @endif

    @if($showTemplates)
        <div class="fixed inset-0 z-[100] overflow-auto bg-slate-950/60 p-4 md:p-8"><div class="mx-auto max-w-5xl rounded-3xl bg-white p-6 shadow-2xl"><div class="flex justify-between"><div><div class="text-xs font-bold uppercase tracking-wider text-blue-600">Biblioteca</div><h2 class="mt-1 text-2xl font-black">Começa com uma estrutura profissional</h2><p class="mt-1 text-sm text-slate-500">Cada opção cria páginas coerentes e prontas a personalizar.</p></div><button wire:click="$set('showTemplates',false)" class="text-2xl text-slate-400">×</button></div><div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">@foreach(['business'=>'Negócio','restaurant'=>'Restaurante','portfolio'=>'Portfólio','freelancer'=>'Freelancer','agency'=>'Agência','real-estate'=>'Imobiliário','landing'=>'Landing page','saas'=>'SaaS','shop'=>'Loja online','professional'=>'Serviços profissionais'] as $key=>$label)<button wire:click="applyTemplate('{{ $key }}')" class="group rounded-2xl border border-slate-200 p-5 text-left hover:border-slate-400 hover:shadow-md"><div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 font-black">{{ strtoupper(substr($label,0,1)) }}</div><div class="mt-4 font-bold">{{ $label }}</div><div class="mt-1 text-xs text-slate-500">Estrutura completa com páginas, secções e navegação.</div><div class="mt-4 text-xs font-bold opacity-0 transition group-hover:opacity-100">Usar template →</div></button>@endforeach</div></div></div>
    @endif

    @if($showPublish)
        @php($checks = $this->publishChecks())
        <div class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/60 p-4"><div class="w-full max-w-xl rounded-3xl bg-white p-6 shadow-2xl"><div class="flex justify-between"><div><div class="text-xs font-bold uppercase tracking-wider text-emerald-600">Publicação</div><h2 class="mt-1 text-2xl font-black">Verificar antes de publicar</h2><p class="mt-1 text-sm text-slate-500">Edita → Pré-visualiza → Verifica → Publica.</p></div><button wire:click="$set('showPublish',false)" class="text-2xl text-slate-400">×</button></div><div class="mt-5 space-y-2">@foreach(['Website com nome definido'=>trim((string)$site->name)!=='','Pelo menos uma página'=>$checks['pages']>0,'Todas as páginas têm conteúdo'=>count($checks['errors'])===0,'SEO configurável'=>'ok'] as $label=>$ok)<div class="flex items-center gap-3 rounded-xl bg-slate-50 p-3 text-sm"><span class="text-lg">{{ $ok ? '✓' : '!' }}</span><span class="font-semibold">{{ $label }}</span></div>@endforeach</div>@if(count($checks['errors']))<div class="mt-4 rounded-xl bg-red-50 p-3 text-xs text-red-700">@foreach($checks['errors'] as $error)<div>• {{ $error }}</div>@endforeach</div>@endif<div class="mt-5 flex justify-between gap-2">@if($site->is_published)<button wire:click="unpublish" class="rounded-xl border border-amber-200 px-4 py-2 text-sm font-bold text-amber-700">Voltar a rascunho</button>@else<div></div>@endif<button wire:click="publish" class="rounded-xl bg-slate-900 px-5 py-2 text-sm font-bold text-white">{{ $site->is_published ? 'Publicar alterações' : 'Publicar website' }}</button></div></div></div>
    @endif
</div>
