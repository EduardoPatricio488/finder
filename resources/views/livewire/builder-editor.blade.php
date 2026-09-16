<div class="min-h-screen bg-[#f4f5f7] text-zinc-950 antialiased" x-data="{ device: 'desktop', pages: false, editor: false }">
    <header class="sticky top-0 z-50 border-b border-zinc-200/80 bg-white/95 backdrop-blur-xl">
        <div class="mx-auto flex h-[68px] max-w-[1920px] items-center gap-2 px-3 sm:px-5">
            <a href="{{ route('admin.site.dashboard', $site) }}" class="flex h-10 shrink-0 items-center gap-2 rounded-xl px-2.5 text-sm font-semibold text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-950">
                <span class="text-lg">←</span><span class="hidden md:inline">Sair</span>
            </a>
            <div class="h-7 w-px bg-zinc-200"></div>
            <div class="flex min-w-0 items-center gap-2.5">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-zinc-950 text-sm font-black text-white">F</div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <p class="max-w-[180px] truncate text-sm font-bold sm:max-w-[260px]">{{ $site->name }}</p>
                        <span class="hidden rounded-full bg-amber-50 px-2 py-0.5 text-[9px] font-black uppercase tracking-wider text-amber-700 sm:inline-flex">{{ $pageStatus === 'published' ? 'Publicado' : 'Rascunho' }}</span>
                    </div>
                    <p class="hidden text-[11px] text-zinc-400 sm:block">Website Builder · {{ $dirty ? 'Alterações por guardar' : 'Tudo guardado' }}</p>
                </div>
            </div>
            <div class="ml-auto flex items-center gap-1.5 sm:gap-2">
                <a href="{{ route('site.public', ['site' => $site, 'preview' => 1]) }}" target="_blank" class="hidden h-10 items-center gap-2 rounded-xl border border-zinc-200 bg-white px-3 text-xs font-bold text-zinc-700 transition hover:bg-zinc-50 md:flex">◉ Pré-visualizar</a>
                <button type="button" wire:click="save" class="hidden h-10 rounded-xl border border-zinc-200 bg-white px-4 text-xs font-bold text-zinc-700 transition hover:bg-zinc-50 sm:block">Guardar</button>
                <button type="button" wire:click="publish" class="h-10 rounded-xl bg-zinc-950 px-4 text-xs font-black text-white shadow-lg shadow-zinc-950/15 transition hover:-translate-y-0.5 hover:bg-zinc-800 sm:px-5">Publicar</button>
            </div>
        </div>
    </header>

    <div class="mx-auto grid min-h-[calc(100vh-68px)] max-w-[1920px] grid-cols-1 lg:grid-cols-[250px_minmax(0,1fr)_350px]">
        <aside class="hidden border-r border-zinc-200 bg-white lg:block">
            <div class="sticky top-[68px] max-h-[calc(100vh-68px)] overflow-y-auto p-5">
                <div class="mb-6 flex items-end justify-between">
                    <div><p class="text-[9px] font-black uppercase tracking-[0.2em] text-zinc-400">Website</p><h2 class="mt-1 text-base font-black tracking-tight">Páginas</h2></div>
                    <span class="rounded-lg bg-zinc-100 px-2 py-1 text-[10px] font-bold text-zinc-500">{{ $site->pages->count() }}</span>
                </div>
                <div class="space-y-1.5">
                    @foreach($site->pages->sortBy('sort_order') as $page)
                        <button type="button" wire:click="selectPage({{ $page->id }})" class="group flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left transition {{ $pageId === $page->id ? 'bg-zinc-950 text-white shadow-md' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-950' }}">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-xs font-bold {{ $pageId === $page->id ? 'bg-white/10' : 'bg-zinc-100 group-hover:bg-white' }}">{{ $page->is_homepage ? '⌂' : '○' }}</span>
                            <span class="min-w-0 flex-1 truncate text-sm font-semibold">{{ $page->name }}</span>
                            @if($page->is_homepage)<span class="text-[8px] font-black uppercase tracking-wider opacity-50">Home</span>@endif
                        </button>
                    @endforeach
                </div>
                <button type="button" wire:click="createPage" class="mt-2 flex w-full items-center justify-center rounded-xl border border-dashed border-zinc-300 px-3 py-2.5 text-xs font-bold text-zinc-500 transition hover:border-zinc-500 hover:bg-zinc-50 hover:text-zinc-950">+ Nova página</button>

                <div class="my-6 h-px bg-zinc-100"></div>
                <p class="text-[9px] font-black uppercase tracking-[0.2em] text-zinc-400">Conteúdo</p>
                <a href="{{ route('products') }}" class="mt-2 flex items-center gap-3 rounded-xl border border-zinc-200 bg-white p-3 transition hover:-translate-y-0.5 hover:border-zinc-300 hover:shadow-md">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-zinc-100">▦</span>
                    <span class="min-w-0 flex-1"><span class="block text-xs font-black">Produtos</span><span class="mt-0.5 block text-[10px] text-zinc-400">Adicionar e gerir</span></span><span class="text-zinc-300">→</span>
                </a>
                <div class="mt-4 rounded-2xl bg-zinc-950 p-4 text-white shadow-xl">
                    <span class="rounded-full bg-white/10 px-2 py-1 text-[8px] font-black uppercase tracking-[0.18em] text-white/60">Finder Premium</span>
                    <p class="mt-3 text-sm font-black tracking-tight">O website já está quase pronto.</p>
                    <p class="mt-1 text-[11px] leading-5 text-white/50">Personaliza textos, imagens e produtos. O design fica protegido.</p>
                </div>
            </div>
        </aside>

        <main class="min-w-0 bg-[#f4f5f7] p-3 sm:p-5 xl:p-7">
            <div class="mx-auto max-w-[1180px]">
                <div class="mb-3 flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <input wire:model.live.debounce.700ms="pageName" class="w-full max-w-sm border-0 bg-transparent p-0 text-lg font-black tracking-tight outline-none focus:ring-0 sm:text-xl" aria-label="Nome da página">
                        <p class="mt-0.5 text-[11px] text-zinc-400">/{{ trim($pageSlug, '/') }}</p>
                    </div>
                    <div class="flex shrink-0 items-center gap-1.5">
                        <span class="hidden rounded-full bg-white px-3 py-1.5 text-[10px] font-bold text-zinc-400 shadow-sm sm:inline-flex">{{ $dirty ? '● Alterações por guardar' : '✓ Guardado' }}</span>
                        <button type="button" @click="pages=!pages" class="flex h-9 items-center rounded-xl border border-zinc-200 bg-white px-3 text-xs font-bold lg:hidden">Páginas</button>
                        <button type="button" @click="editor=!editor" class="flex h-9 items-center rounded-xl border border-zinc-200 bg-white px-3 text-xs font-bold lg:hidden">Editar</button>
                    </div>
                </div>

                <div x-show="pages" x-cloak class="mb-3 rounded-2xl border border-zinc-200 bg-white p-4 shadow-lg lg:hidden">
                    <div class="space-y-1.5">
                        @foreach($site->pages->sortBy('sort_order') as $page)
                            <button type="button" wire:click="selectPage({{ $page->id }})" @click="pages=false" class="flex w-full items-center gap-3 rounded-xl px-3 py-3 text-left {{ $pageId === $page->id ? 'bg-zinc-950 text-white' : 'hover:bg-zinc-100' }}"><span class="font-bold">{{ $page->is_homepage ? '⌂' : '○' }}</span><span class="text-sm font-semibold">{{ $page->name }}</span></button>
                        @endforeach
                        <button type="button" wire:click="createPage" class="w-full rounded-xl border border-dashed border-zinc-300 px-3 py-3 text-xs font-bold text-zinc-500">+ Nova página</button>
                    </div>
                </div>

                <div class="mb-3 flex items-center justify-center gap-1 rounded-xl border border-zinc-200 bg-white p-1 shadow-sm">
                    <button type="button" @click="device='desktop'" :class="device==='desktop' ? 'bg-zinc-950 text-white shadow-sm' : 'text-zinc-500 hover:bg-zinc-100'" class="flex flex-1 items-center justify-center rounded-lg px-3 py-2 text-[10px] font-black sm:text-xs">▣ <span class="ml-1.5 hidden sm:inline">Computador</span></button>
                    <button type="button" @click="device='tablet'" :class="device==='tablet' ? 'bg-zinc-950 text-white shadow-sm' : 'text-zinc-500 hover:bg-zinc-100'" class="flex flex-1 items-center justify-center rounded-lg px-3 py-2 text-[10px] font-black sm:text-xs">▤ <span class="ml-1.5 hidden sm:inline">Tablet</span></button>
                    <button type="button" @click="device='mobile'" :class="device==='mobile' ? 'bg-zinc-950 text-white shadow-sm' : 'text-zinc-500 hover:bg-zinc-100'" class="flex flex-1 items-center justify-center rounded-lg px-3 py-2 text-[10px] font-black sm:text-xs">▯ <span class="ml-1.5 hidden sm:inline">Telemóvel</span></button>
                </div>

                <div class="flex justify-center">
                    <div class="w-full overflow-hidden rounded-[24px] border border-zinc-200 bg-white shadow-[0_25px_80px_-35px_rgba(0,0,0,.3)] transition-all duration-300" :class="device==='desktop' ? 'max-w-[1180px]' : (device==='tablet' ? 'max-w-[760px]' : 'max-w-[390px]')">
                        <div class="flex h-10 items-center gap-1.5 border-b border-zinc-100 bg-zinc-50 px-3">
                            <span class="h-2.5 w-2.5 rounded-full bg-red-300"></span><span class="h-2.5 w-2.5 rounded-full bg-amber-300"></span><span class="h-2.5 w-2.5 rounded-full bg-emerald-300"></span>
                            <div class="mx-auto flex h-6 max-w-md flex-1 items-center justify-center rounded-md border border-zinc-200 bg-white px-2 text-[9px] text-zinc-400">{{ $site->slug }} · Finder</div>
                        </div>

                        @forelse($sections as $index => $section)
                            @php($content = is_array($section['content'] ?? null) ? $section['content'] : [])
                            @php($items = is_array($content['items'] ?? null) ? $content['items'] : [])
                            @php($type = $section['type'] ?? 'text')
                            <section wire:key="customer-builder-section-{{ $section['id'] ?? 'new-'.$index }}" wire:click="selectSection({{ $index }})" class="group relative border-b border-zinc-100 px-6 py-14 transition sm:px-10 sm:py-16 {{ $section['is_visible'] ? '' : 'opacity-40' }} {{ $selectedSection === $index ? 'z-10 ring-2 ring-inset ring-zinc-950' : 'hover:bg-zinc-50/60' }}">
                                @if($selectedSection === $index)<span class="absolute right-4 top-4 rounded-full bg-zinc-950 px-2.5 py-1 text-[8px] font-black uppercase tracking-wider text-white">A editar</span>@endif
                                <div class="mx-auto max-w-4xl">
                                    @if($type === 'hero')
                                        <div class="text-center">
                                            @if(!empty($content['subtitle']))<p class="mb-3 text-[9px] font-black uppercase tracking-[0.24em]" style="color: {{ $theme['primary'] ?? '#635bff' }}">{{ $content['subtitle'] }}</p>@endif
                                            <h1 class="text-4xl font-black tracking-[-0.045em] sm:text-5xl lg:text-6xl">{{ $content['title'] ?? 'Título principal' }}</h1>
                                            <p class="mx-auto mt-5 max-w-2xl whitespace-pre-line text-sm leading-7 text-zinc-500 sm:text-base">{{ $content['description'] ?? $content['body'] ?? '' }}</p>
                                            @if(!empty($content['button_label']))<span class="mt-7 inline-flex rounded-xl bg-zinc-950 px-5 py-3 text-xs font-black text-white shadow-lg">{{ $content['button_label'] }}</span>@endif
                                        </div>
                                    @elseif($type === 'text')
                                        <h2 class="text-3xl font-black tracking-tight">{{ $content['title'] ?? 'Título' }}</h2>
                                        <p class="mt-4 whitespace-pre-line text-base leading-7 text-zinc-500">{{ $content['body'] ?? $content['description'] ?? '' }}</p>
                                    @elseif($type === 'product_grid')
                                        <div class="text-center"><h2 class="text-3xl font-black tracking-tight">{{ $content['title'] ?? 'Os nossos produtos' }}</h2><p class="mx-auto mt-2 max-w-xl text-sm leading-6 text-zinc-500">{{ $content['description'] ?? 'Produtos escolhidos para os teus clientes.' }}</p></div>
                                        <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                                            @forelse($items as $item)
                                                <article class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
                                                    @if(!empty($item['image_url']))<img src="{{ $item['image_url'] }}" alt="{{ $item['name'] ?? '' }}" class="h-48 w-full object-cover">@else<div class="flex h-48 items-center justify-center bg-zinc-100 text-xs text-zinc-400">Sem imagem</div>@endif
                                                    <div class="p-5"><h3 class="font-bold tracking-tight">{{ $item['name'] ?? $item['title'] ?? 'Produto' }}</h3><p class="mt-1.5 line-clamp-2 text-sm leading-6 text-zinc-500">{{ $item['description'] ?? '' }}</p>@if(isset($item['price']))<p class="mt-4 text-lg font-black">€ {{ number_format((float) $item['price'], 2, ',', '.') }}</p>@endif</div>
                                                </article>
                                            @empty
                                                <div class="col-span-full rounded-2xl border border-dashed border-zinc-300 bg-zinc-50 p-10 text-center"><p class="font-bold">Ainda não tens produtos.</p><p class="mt-1 text-sm text-zinc-400">Adiciona produtos e eles aparecem automaticamente aqui.</p><a href="{{ route('products') }}" class="mt-5 inline-flex rounded-xl bg-zinc-950 px-5 py-3 text-xs font-black text-white">Adicionar produtos →</a></div>
                                            @endforelse
                                        </div>
                                    @elseif($type === 'cta')
                                        <div class="rounded-3xl bg-zinc-950 p-8 text-center text-white sm:p-14"><h2 class="text-3xl font-black">{{ $content['title'] ?? 'Fala connosco' }}</h2><p class="mx-auto mt-3 max-w-2xl text-sm leading-6 text-white/55">{{ $content['description'] ?? '' }}</p>@if(!empty($content['button_label']))<span class="mt-6 inline-flex rounded-xl bg-white px-5 py-3 text-sm font-black text-zinc-950">{{ $content['button_label'] }}</span>@endif</div>
                                    @else
                                        <h2 class="text-3xl font-black tracking-tight">{{ $content['title'] ?? \Illuminate\Support\Str::headline($type) }}</h2>
                                        @if(!empty($content['description']))<p class="mt-3 whitespace-pre-line text-zinc-500">{{ $content['description'] }}</p>@endif
                                        @if(count($items))<div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">@foreach($items as $item)<article class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm"><p class="font-black">{{ $item['title'] ?? $item['name'] ?? $item['question'] ?? $item['label'] ?? 'Conteúdo' }}</p><p class="mt-2 text-sm leading-6 text-zinc-500">{{ $item['description'] ?? $item['quote'] ?? $item['answer'] ?? $item['excerpt'] ?? $item['price'] ?? '' }}</p></article>@endforeach</div>@endif
                                    @endif
                                </div>
                            </section>
                        @empty
                            <div class="flex min-h-[650px] items-center justify-center p-8 text-center"><div class="max-w-md"><div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-zinc-100 text-2xl">✦</div><h2 class="mt-5 text-2xl font-black">O teu website está pronto</h2><p class="mt-2 text-sm leading-6 text-zinc-500">Personaliza o conteúdo e publica quando estiveres satisfeito.</p></div></div>
                        @endforelse
                    </div>
                </div>
            </div>
        </main>

        <aside x-show="editor || window.innerWidth >= 1024" x-transition class="fixed inset-x-0 bottom-0 z-40 max-h-[78vh] overflow-y-auto border-t border-zinc-200 bg-white shadow-2xl lg:sticky lg:top-[68px] lg:block lg:max-h-[calc(100vh-68px)] lg:border-l lg:border-t-0 lg:shadow-none">
            <div class="p-5 sm:p-6">
                <div class="mb-5 flex items-center justify-between lg:hidden"><div><p class="text-[9px] font-black uppercase tracking-[0.2em] text-zinc-400">Editor</p><h2 class="mt-1 text-lg font-black">Personalizar</h2></div><button type="button" @click="editor=false" class="h-9 w-9 rounded-xl bg-zinc-100">×</button></div>
                @if($selectedSection !== null && isset($sections[$selectedSection]))
                    @php($selectedContent = is_array($sections[$selectedSection]['content'] ?? null) ? $sections[$selectedSection]['content'] : [])
                    @php($selectedType = $sections[$selectedSection]['type'] ?? 'text')
                    <div class="mb-6 border-b border-zinc-100 pb-5"><p class="text-[9px] font-black uppercase tracking-[0.2em] text-zinc-400">Personalizar</p><h2 class="mt-1 text-lg font-black tracking-tight">{{ $sections[$selectedSection]['label'] ?? 'Secção' }}</h2><p class="mt-2 text-[11px] leading-5 text-zinc-400">Altera o conteúdo. O design e a estrutura ficam protegidos.</p></div>
                    <div class="space-y-4">
                        @foreach(['title'=>'Título','subtitle'=>'Subtítulo','description'=>'Descrição','body'=>'Texto','button_label'=>'Botão','button_url'=>'Ligação'] as $field => $label)
                            @if(array_key_exists($field, $selectedContent))
                                <div><label class="text-[10px] font-black uppercase tracking-wider text-zinc-500">{{ $label }}</label>@if(in_array($field, ['description','body'], true))<textarea wire:model.live.debounce.700ms="sections.{{ $selectedSection }}.content.{{ $field }}" rows="4" class="mt-1.5 w-full resize-y rounded-xl border-zinc-200 bg-zinc-50 px-3 py-2.5 text-sm leading-6 outline-none focus:border-zinc-900 focus:bg-white focus:ring-2 focus:ring-zinc-900/5"></textarea>@else<input wire:model.live.debounce.700ms="sections.{{ $selectedSection }}.content.{{ $field }}" class="mt-1.5 w-full rounded-xl border-zinc-200 bg-zinc-50 px-3 py-2.5 text-sm outline-none focus:border-zinc-900 focus:bg-white focus:ring-2 focus:ring-zinc-900/5">@endif</div>
                            @endif
                        @endforeach

                        @if($selectedType === 'product_grid')
                            <div class="rounded-2xl bg-zinc-950 p-4 text-white"><p class="text-sm font-black">Catálogo</p><p class="mt-1 text-[11px] leading-5 text-white/50">Os produtos activos são sincronizados automaticamente.</p><a href="{{ route('products') }}" class="mt-3 flex w-full items-center justify-center rounded-xl bg-white px-3 py-3 text-xs font-black text-zinc-950">Adicionar / gerir produtos →</a></div>
                        @endif

                        <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4"><p class="text-xs font-black">Página</p><p class="mt-1 text-[11px] leading-5 text-zinc-400">{{ $pageStatus === 'published' ? 'Esta página está publicada.' : 'Esta página está em rascunho.' }}</p></div>
                        <button type="button" wire:click="save" class="w-full rounded-xl bg-zinc-950 px-4 py-3.5 text-sm font-black text-white shadow-lg transition hover:-translate-y-0.5 hover:bg-zinc-800">Guardar alterações</button>
                    </div>
                @else
                    <div class="py-16 text-center"><div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-zinc-100 text-xl">✦</div><p class="mt-4 text-sm font-black">Começa a personalizar</p><p class="mt-1 text-xs leading-5 text-zinc-400">Clica numa área do website para editar.</p></div>
                @endif
            </div>
        </aside>
    </div>

    <div wire:loading.flex class="fixed inset-0 z-[100] items-center justify-center bg-zinc-950/20 backdrop-blur-[2px]"><div class="flex items-center gap-3 rounded-2xl bg-white px-5 py-4 text-sm font-bold shadow-2xl"><span class="h-4 w-4 animate-spin rounded-full border-2 border-zinc-300 border-t-zinc-950"></span>A guardar...</div></div>

    @if($showOnboarding)
        <div class="fixed inset-0 z-[90] flex items-center justify-center bg-zinc-950/40 p-4 backdrop-blur-sm">
            <div class="w-full max-w-lg rounded-3xl bg-white p-7 shadow-2xl sm:p-9">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-zinc-950 text-xl text-white">✦</div>
                <p class="mt-6 text-[9px] font-black uppercase tracking-[0.2em] text-zinc-400">Finder Website Builder</p>
                <h2 class="mt-2 text-2xl font-black tracking-tight sm:text-3xl">O teu website já está quase pronto.</h2>
                <p class="mt-3 text-sm leading-6 text-zinc-500">Não precisas de aprender um editor complicado. Escolhe uma página, altera os textos que quiseres, adiciona produtos e publica.</p>
                <div class="mt-6 grid gap-3 sm:grid-cols-3"><div class="rounded-2xl bg-zinc-50 p-4"><p class="text-xs font-black">1 · Edita</p><p class="mt-1 text-[11px] text-zinc-400">Textos e conteúdo</p></div><div class="rounded-2xl bg-zinc-50 p-4"><p class="text-xs font-black">2 · Produtos</p><p class="mt-1 text-[11px] text-zinc-400">Adiciona o catálogo</p></div><div class="rounded-2xl bg-zinc-50 p-4"><p class="text-xs font-black">3 · Publica</p><p class="mt-1 text-[11px] text-zinc-400">Fica online</p></div></div>
                <button type="button" wire:click="dismissOnboarding" class="mt-7 w-full rounded-xl bg-zinc-950 px-5 py-3.5 text-sm font-black text-white">Começar a personalizar →</button>
            </div>
        </div>
    @endif
</div>
