<div class="min-h-screen bg-[#f5f6f8] text-zinc-950 antialiased">
    <header class="sticky top-0 z-50 border-b border-zinc-200/80 bg-white/95 backdrop-blur-xl">
        <div class="flex h-[72px] items-center gap-3 px-4 lg:px-6">
            <a href="{{ route('admin.site.dashboard', $site) }}" class="flex h-10 items-center gap-2 rounded-xl px-3 text-sm font-semibold text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-900">
                <span class="text-lg">←</span><span class="hidden sm:inline">Sair</span>
            </a>
            <div class="h-8 w-px bg-zinc-200"></div>
            <div class="flex min-w-0 items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-zinc-950 text-sm font-black text-white shadow-sm">F</div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <p class="truncate text-sm font-bold">{{ $site->name }}</p>
                        <span class="rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-amber-700">Draft</span>
                    </div>
                    <p class="text-xs text-zinc-400">Website Builder <span class="mx-1">·</span> {{ $statusMessage ?: 'Tudo guardado' }}</p>
                </div>
            </div>
            <div class="ml-auto flex items-center gap-2">
                <a href="{{ route('site.public', ['site' => $site, 'preview' => 1]) }}" target="_blank" class="hidden h-10 items-center gap-2 rounded-xl border border-zinc-200 bg-white px-4 text-xs font-bold text-zinc-700 shadow-sm transition hover:border-zinc-300 hover:bg-zinc-50 sm:flex">
                    <span>◉</span> Pré-visualizar
                </a>
                <button type="button" wire:click="save" class="h-10 rounded-xl border border-zinc-200 bg-white px-4 text-xs font-bold text-zinc-700 shadow-sm transition hover:bg-zinc-50">Guardar</button>
                <button type="button" wire:click="publish" class="h-10 rounded-xl bg-zinc-950 px-5 text-xs font-bold text-white shadow-lg shadow-zinc-950/15 transition hover:-translate-y-0.5 hover:bg-zinc-800">Publicar website</button>
            </div>
        </div>
    </header>

    <div class="mx-auto grid min-h-[calc(100vh-72px)] max-w-[1800px] grid-cols-1 lg:grid-cols-[245px_minmax(0,1fr)_340px]">
        <aside class="border-b border-zinc-200 bg-white lg:border-b-0 lg:border-r">
            <div class="sticky top-[72px] p-5">
                <div class="mb-7">
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-zinc-400">Website</p>
                    <div class="mt-2 flex items-center justify-between">
                        <h2 class="text-base font-extrabold tracking-tight">Estrutura</h2>
                        <span class="rounded-lg bg-zinc-100 px-2 py-1 text-[10px] font-bold text-zinc-500">{{ count($pages) }} páginas</span>
                    </div>
                </div>

                <div class="space-y-1.5">
                    @foreach($pages as $page)
                        <button type="button" wire:click="loadPage({{ $page['id'] }})" class="group flex w-full items-center gap-3 rounded-xl px-3 py-3 text-left transition {{ $pageId === $page['id'] ? 'bg-zinc-950 text-white shadow-lg shadow-zinc-950/10' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-950' }}">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-xs font-bold {{ $pageId === $page['id'] ? 'bg-white/10 text-white' : 'bg-zinc-100 text-zinc-500 group-hover:bg-white' }}">{{ $page['is_homepage'] ? '⌂' : '○' }}</span>
                            <span class="min-w-0 flex-1 truncate text-sm font-semibold">{{ $page['name'] }}</span>
                            @if($page['is_homepage'])<span class="text-[9px] font-bold uppercase tracking-wider opacity-60">Home</span>@endif
                        </button>
                    @endforeach
                </div>

                <button type="button" wire:click="createPage" class="mt-3 flex w-full items-center justify-center gap-2 rounded-xl border border-dashed border-zinc-300 px-3 py-3 text-xs font-bold text-zinc-500 transition hover:border-zinc-500 hover:bg-zinc-50 hover:text-zinc-900">+ Nova página</button>

                <div class="my-7 h-px bg-zinc-100"></div>
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-zinc-400">Conteúdo</p>
                <a href="{{ route('products') }}" class="mt-3 flex items-center gap-3 rounded-xl border border-zinc-200 bg-white p-3.5 shadow-sm transition hover:-translate-y-0.5 hover:border-zinc-300 hover:shadow-md">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-zinc-100 text-base">▦</span>
                    <span class="min-w-0 flex-1"><span class="block text-sm font-bold">Produtos</span><span class="block text-[11px] text-zinc-400">Gerir catálogo</span></span>
                    <span class="text-zinc-300">→</span>
                </a>

                <div class="mt-5 rounded-2xl bg-gradient-to-br from-zinc-950 to-zinc-800 p-4 text-white shadow-xl shadow-zinc-950/10">
                    <p class="text-[10px] font-black uppercase tracking-[0.16em] text-white/45">Finder Premium</p>
                    <p class="mt-2 text-sm font-bold">O teu website, sem complicações.</p>
                    <p class="mt-1 text-[11px] leading-5 text-white/55">Escolhe um modelo profissional e altera apenas o que precisas.</p>
                </div>
            </div>
        </aside>

        <main class="min-w-0 bg-[#f5f6f8] p-4 sm:p-6 xl:p-8">
            <div class="mx-auto max-w-[1100px]">
                <div class="mb-4 flex items-center justify-between">
                    <div class="min-w-0">
                        <input value="{{ $this->currentPage()['name'] ?? 'Página' }}" wire:blur="updatePageName($event.target.value)" class="w-full max-w-md border-0 bg-transparent p-0 text-lg font-extrabold tracking-tight outline-none focus:ring-0" aria-label="Nome da página">
                        <p class="mt-0.5 text-xs text-zinc-400">/{{ $this->currentPage()['slug'] ?? '' }}</p>
                    </div>
                    @if($dirty)<span class="rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5 text-[10px] font-bold text-amber-700">● Alterações por guardar</span>@else<span class="rounded-full bg-white px-3 py-1.5 text-[10px] font-bold text-zinc-400 shadow-sm">✓ Guardado</span>@endif
                </div>

                <div class="overflow-hidden rounded-[22px] border border-zinc-200/80 bg-white shadow-[0_20px_70px_-30px_rgba(0,0,0,.25)]">
                    <div class="flex h-11 items-center gap-2 border-b border-zinc-100 bg-zinc-50/80 px-4">
                        <span class="h-2.5 w-2.5 rounded-full bg-red-300"></span><span class="h-2.5 w-2.5 rounded-full bg-amber-300"></span><span class="h-2.5 w-2.5 rounded-full bg-emerald-300"></span>
                        <div class="mx-auto flex h-7 max-w-md flex-1 items-center justify-center rounded-lg border border-zinc-200 bg-white px-3 text-[10px] font-medium text-zinc-400">{{ $site->slug }} · Finder</div>
                    </div>

                    <div class="min-h-[calc(100vh-220px)] bg-white">
                        @forelse($sections as $index => $section)
                            @php($content = is_array($section['content'] ?? null) ? $section['content'] : [])
                            @php($items = is_array($content['items'] ?? null) ? $content['items'] : [])
                            <section wire:key="premium-section-{{ $section['id'] ?? 'new-'.$index }}" wire:click="selectSection({{ $index }})" class="group relative cursor-pointer border-b border-zinc-100 px-6 py-16 transition sm:px-12 lg:px-16 {{ $selectedSection === $index ? 'z-10 ring-2 ring-inset ring-zinc-950' : 'hover:bg-zinc-50/70' }}">
                                @if($selectedSection === $index)<div class="absolute right-4 top-4 rounded-full bg-zinc-950 px-2.5 py-1 text-[9px] font-black uppercase tracking-wider text-white">A editar</div>@endif
                                <div class="mx-auto max-w-4xl">
                                    @if(!empty($content['subtitle']))<p class="mb-4 text-center text-[10px] font-black uppercase tracking-[0.22em] text-zinc-400">{{ $content['subtitle'] }}</p>@endif
                                    @if(!empty($content['title']))<h1 class="text-center text-4xl font-black tracking-[-0.04em] text-zinc-950 sm:text-6xl">{{ $content['title'] }}</h1>@endif
                                    @if(!empty($content['description']) || !empty($content['body']))<p class="mx-auto mt-5 max-w-2xl whitespace-pre-line text-center text-base leading-7 text-zinc-500">{{ $content['description'] ?? $content['body'] }}</p>@endif

                                    @if($section['type'] === 'product_grid')
                                        @php($products = $site->products()->where('is_active', true)->latest()->limit(6)->get())
                                        <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                                            @forelse($products as $product)
                                                <article class="group/product overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
                                                    @if($product->image_url)<img src="{{ asset('storage/'.$product->image_url) }}" alt="{{ $product->name }}" class="h-48 w-full object-cover transition duration-500 group-hover/product:scale-[1.02]">@else<div class="flex h-48 items-center justify-center bg-zinc-100 text-xs font-medium text-zinc-400">Sem imagem</div>@endif
                                                    <div class="p-5"><h3 class="font-bold tracking-tight">{{ $product->name }}</h3><p class="mt-1.5 line-clamp-2 text-sm leading-6 text-zinc-500">{{ $product->description }}</p><p class="mt-4 text-lg font-black">€ {{ number_format((float) $product->price, 2, ',', '.') }}</p></div>
                                                </article>
                                            @empty
                                                <div class="col-span-full rounded-2xl border border-dashed border-zinc-300 bg-zinc-50 p-12 text-center"><p class="font-bold">Ainda não tens produtos</p><p class="mt-1 text-sm text-zinc-400">Adiciona produtos para preencher automaticamente esta secção.</p><a href="{{ route('products') }}" class="mt-5 inline-flex rounded-xl bg-zinc-950 px-5 py-3 text-xs font-bold text-white">Adicionar produtos →</a></div>
                                            @endforelse
                                        </div>
                                    @elseif(!empty($items))
                                        <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                                            @foreach($items as $item)
                                                <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg">
                                                    <div class="mb-5 flex h-10 w-10 items-center justify-center rounded-xl bg-zinc-100 text-sm font-black">{{ $loop->iteration }}</div>
                                                    <h3 class="font-bold tracking-tight">{{ $item['title'] ?? $item['name'] ?? $item['question'] ?? $item['label'] ?? 'Conteúdo' }}</h3>
                                                    @if(!empty($item['description']) || !empty($item['quote']) || !empty($item['answer']) || !empty($item['excerpt']))<p class="mt-2 text-sm leading-6 text-zinc-500">{{ $item['description'] ?? $item['quote'] ?? $item['answer'] ?? $item['excerpt'] }}</p>@endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    @if(empty($content['title']) && empty($content['description']) && empty($content['body']) && empty($items) && $section['type'] !== 'product_grid')
                                        <p class="text-center text-sm text-zinc-400">Clica para editar esta secção.</p>
                                    @endif
                                </div>
                            </section>
                        @empty
                            <div class="flex min-h-[650px] items-center justify-center p-8 text-center"><div class="max-w-md"><div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-zinc-100 text-2xl">✦</div><h2 class="mt-5 text-2xl font-black tracking-tight">O teu website está pronto</h2><p class="mt-2 text-sm leading-6 text-zinc-500">Escolhe um modelo profissional e personaliza apenas o conteúdo que queres alterar.</p></div></div>
                        @endforelse
                    </div>
                </div>
            </div>
        </main>

        <aside class="border-t border-zinc-200 bg-white lg:border-l lg:border-t-0">
            <div class="sticky top-[72px] max-h-[calc(100vh-72px)] overflow-y-auto p-5">
                @if($selectedSection !== null && isset($sections[$selectedSection]))
                    @php($selectedContent = is_array($sections[$selectedSection]['content'] ?? null) ? $sections[$selectedSection]['content'] : [])
                    <div class="mb-7 border-b border-zinc-100 pb-5">
                        <div class="flex items-center justify-between"><div><p class="text-[10px] font-black uppercase tracking-[0.18em] text-zinc-400">Personalizar</p><h2 class="mt-1 text-lg font-black tracking-tight">{{ $sections[$selectedSection]['label'] ?? 'Secção' }}</h2></div><span class="rounded-lg bg-zinc-100 px-2 py-1 text-[9px] font-bold text-zinc-500">{{ ucfirst(str_replace('_', ' ', $sections[$selectedSection]['type'] ?? '')) }}</span></div>
                        <p class="mt-2 text-xs leading-5 text-zinc-400">O layout e o design ficam protegidos. Edita apenas o conteúdo.</p>
                    </div>

                    <div class="space-y-5">
                        @foreach(['title'=>'Título','subtitle'=>'Subtítulo','description'=>'Descrição','body'=>'Texto','button_label'=>'Texto do botão','button_url'=>'Ligação do botão'] as $field => $label)
                            @if(array_key_exists($field, $selectedContent))
                                <div>
                                    <label class="text-[11px] font-bold text-zinc-600">{{ $label }}</label>
                                    @if(in_array($field, ['description','body'], true))
                                        <textarea wire:model.live.debounce.500ms="sections.{{ $selectedSection }}.content.{{ $field }}" rows="5" class="mt-2 w-full resize-y rounded-xl border-zinc-200 bg-zinc-50 px-3.5 py-3 text-sm leading-6 outline-none transition focus:border-zinc-900 focus:bg-white focus:ring-2 focus:ring-zinc-900/5"></textarea>
                                    @else
                                        <input wire:model.live.debounce.500ms="sections.{{ $selectedSection }}.content.{{ $field }}" class="mt-2 w-full rounded-xl border-zinc-200 bg-zinc-50 px-3.5 py-3 text-sm outline-none transition focus:border-zinc-900 focus:bg-white focus:ring-2 focus:ring-zinc-900/5">
                                    @endif
                                </div>
                            @endif
                        @endforeach

                        @if($sections[$selectedSection]['type'] === 'product_grid')
                            <div class="rounded-2xl bg-zinc-950 p-5 text-white shadow-xl">
                                <p class="text-sm font-bold">Catálogo</p><p class="mt-1 text-xs leading-5 text-white/50">Os produtos activos aparecem automaticamente nesta secção.</p>
                                <a href="{{ route('products') }}" class="mt-4 flex w-full items-center justify-center rounded-xl bg-white px-3 py-3 text-xs font-black text-zinc-950 transition hover:bg-zinc-200">Gerir produtos →</a>
                            </div>
                        @endif

                        <button type="button" wire:click="save" class="w-full rounded-xl bg-zinc-950 px-4 py-3.5 text-sm font-bold text-white shadow-lg shadow-zinc-950/10 transition hover:-translate-y-0.5 hover:bg-zinc-800">Guardar alterações</button>
                    </div>
                @else
                    <div class="py-16 text-center"><div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-zinc-100 text-xl">✦</div><p class="mt-4 text-sm font-bold">Edita o teu website</p><p class="mt-1 text-xs leading-5 text-zinc-400">Clica numa secção no centro para começar.</p></div>
                @endif
            </div>
        </aside>
    </div>
</div>
