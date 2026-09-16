<div class="min-h-screen bg-zinc-50 text-zinc-900">
    <header class="sticky top-0 z-50 border-b border-zinc-200 bg-white/95 backdrop-blur">
        <div class="mx-auto flex h-16 max-w-[1500px] items-center gap-4 px-4 sm:px-6">
            <a href="{{ route('admin.site.dashboard', $site) }}" class="shrink-0 rounded-lg px-2 py-1 text-sm font-medium text-zinc-500 hover:bg-zinc-100 hover:text-zinc-900">← Sair</a>
            <div class="min-w-0">
                <p class="truncate text-sm font-semibold">{{ $site->name }}</p>
                <p class="text-xs text-zinc-400">Editor do website</p>
            </div>
            <div class="ml-auto flex items-center gap-2">
                <span class="hidden text-xs text-zinc-400 sm:inline">{{ $statusMessage }}</span>
                <a href="{{ route('site.public', ['site' => $site, 'preview' => 1]) }}" target="_blank" class="rounded-lg border border-zinc-200 bg-white px-3 py-2 text-xs font-semibold hover:bg-zinc-50">Pré-visualizar</a>
                <button type="button" wire:click="save" class="rounded-lg bg-zinc-900 px-4 py-2 text-xs font-semibold text-white hover:bg-zinc-800">Guardar</button>
                <button type="button" wire:click="publish" class="rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-700">Publicar</button>
            </div>
        </div>
    </header>

    <div class="mx-auto grid min-h-[calc(100vh-4rem)] max-w-[1500px] grid-cols-1 lg:grid-cols-[220px_minmax(0,1fr)_300px]">
        <aside class="border-b border-zinc-200 bg-white p-4 lg:border-b-0 lg:border-r">
            <div class="mb-5">
                <p class="text-xs font-semibold text-zinc-400">PÁGINAS</p>
                <h2 class="mt-1 text-base font-bold">O teu website</h2>
            </div>

            <div class="space-y-1">
                @foreach($pages as $page)
                    <button type="button" wire:click="loadPage({{ $page['id'] }})" class="flex w-full items-center justify-between rounded-lg px-3 py-2.5 text-left text-sm {{ $pageId === $page['id'] ? 'bg-zinc-100 font-semibold text-zinc-900' : 'text-zinc-600 hover:bg-zinc-50' }}">
                        <span class="truncate">{{ $page['name'] }}</span>
                        @if($page['is_homepage'])<span class="text-[10px] text-zinc-400">Home</span>@endif
                    </button>
                @endforeach
            </div>

            <button type="button" wire:click="createPage" class="mt-4 w-full rounded-lg border border-zinc-200 px-3 py-2 text-xs font-semibold hover:bg-zinc-50">+ Nova página</button>

            <div class="mt-8 border-t border-zinc-100 pt-5">
                <p class="text-xs font-semibold text-zinc-400">CONTEÚDO</p>
                <a href="{{ route('products') }}" class="mt-2 flex items-center justify-between rounded-lg bg-zinc-50 px-3 py-3 text-sm font-semibold hover:bg-zinc-100">
                    <span>Produtos</span>
                    <span class="text-zinc-400">→</span>
                </a>
                <p class="mt-2 text-xs leading-5 text-zinc-400">Adiciona aqui os produtos que queres vender no website.</p>
            </div>
        </aside>

        <main class="min-w-0 bg-zinc-100 p-3 sm:p-6">
            <div class="mx-auto max-w-5xl overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-zinc-100 px-4 py-3">
                    <div class="min-w-0">
                        <input value="{{ $this->currentPage()['name'] ?? 'Página' }}" wire:blur="updatePageName($event.target.value)" class="w-full border-0 bg-transparent p-0 text-sm font-semibold outline-none focus:ring-0" aria-label="Nome da página">
                        <p class="text-xs text-zinc-400">/{{ $this->currentPage()['slug'] ?? '' }}</p>
                    </div>
                    @if($dirty)<span class="rounded-full bg-amber-50 px-2.5 py-1 text-[10px] font-semibold text-amber-700">Alterações por guardar</span>@endif
                </div>

                <div class="min-h-[calc(100vh-10rem)] bg-white">
                    @forelse($sections as $index => $section)
                        @php($content = is_array($section['content'] ?? null) ? $section['content'] : [])
                        @php($items = is_array($content['items'] ?? null) ? $content['items'] : [])
                        <section wire:key="simple-section-{{ $section['id'] ?? 'new-'.$index }}" wire:click="selectSection({{ $index }})" class="relative cursor-pointer border-b border-zinc-100 px-6 py-14 transition hover:bg-zinc-50/60 sm:px-12 {{ $selectedSection === $index ? 'ring-2 ring-inset ring-indigo-500' : '' }}">
                            <div class="mx-auto max-w-4xl">
                                @if(!empty($content['subtitle']))<p class="mb-3 text-center text-xs font-bold uppercase tracking-[.18em] text-indigo-600">{{ $content['subtitle'] }}</p>@endif
                                @if(!empty($content['title']))<h1 class="text-center text-3xl font-bold tracking-tight sm:text-5xl">{{ $content['title'] }}</h1>@endif
                                @if(!empty($content['description']) || !empty($content['body']))<p class="mx-auto mt-4 max-w-2xl whitespace-pre-line text-center leading-7 text-zinc-500">{{ $content['description'] ?? $content['body'] }}</p>@endif

                                @if($section['type'] === 'product_grid')
                                    @php($products = $site->products()->where('is_active', true)->latest()->limit(6)->get())
                                    <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                        @forelse($products as $product)
                                            <article class="overflow-hidden rounded-xl border border-zinc-200 bg-white">
                                                @if($product->image_url)<img src="{{ asset('storage/'.$product->image_url) }}" alt="{{ $product->name }}" class="h-44 w-full object-cover">@else<div class="flex h-44 items-center justify-center bg-zinc-100 text-xs text-zinc-400">Sem imagem</div>@endif
                                                <div class="p-4"><h3 class="font-semibold">{{ $product->name }}</h3><p class="mt-1 text-sm text-zinc-500">{{ $product->description }}</p><p class="mt-3 font-bold">€ {{ number_format((float) $product->price, 2, ',', '.') }}</p></div>
                                            </article>
                                        @empty
                                            <div class="col-span-full rounded-xl border border-dashed border-zinc-300 p-10 text-center"><p class="font-semibold">Ainda não tens produtos</p><a href="{{ route('products') }}" class="mt-3 inline-block text-sm font-semibold text-indigo-600">Adicionar produtos →</a></div>
                                        @endforelse
                                    </div>
                                @elseif(!empty($items))
                                    <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                        @foreach($items as $item)
                                            <div class="rounded-xl border border-zinc-200 p-5">
                                                <h3 class="font-semibold">{{ $item['title'] ?? $item['name'] ?? $item['question'] ?? $item['label'] ?? 'Conteúdo' }}</h3>
                                                @if(!empty($item['description']) || !empty($item['quote']) || !empty($item['answer']) || !empty($item['excerpt']))<p class="mt-2 text-sm leading-6 text-zinc-500">{{ $item['description'] ?? $item['quote'] ?? $item['answer'] ?? $item['excerpt'] }}</p>@endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                @if(empty($content['title']) && empty($content['description']) && empty($content['body']) && empty($items) && $section['type'] !== 'product_grid')
                                    <p class="text-center text-sm text-zinc-400">Clica aqui para editar esta secção.</p>
                                @endif
                            </div>
                        </section>
                    @empty
                        <div class="flex min-h-[650px] items-center justify-center p-8 text-center">
                            <div class="max-w-md">
                                <h2 class="text-xl font-bold">O teu website está pronto para personalizar</h2>
                                <p class="mt-2 text-sm leading-6 text-zinc-500">O modelo escolhido será apresentado aqui. Se precisares de conteúdo adicional, podes adicioná-lo através do painel de edição.</p>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </main>

        <aside class="border-t border-zinc-200 bg-white lg:border-l lg:border-t-0">
            <div class="sticky top-16 p-5">
                @if($selectedSection !== null && isset($sections[$selectedSection]))
                    @php($selectedContent = is_array($sections[$selectedSection]['content'] ?? null) ? $sections[$selectedSection]['content'] : [])
                    <div class="mb-6">
                        <p class="text-xs font-semibold text-indigo-600">EDITAR</p>
                        <h2 class="mt-1 text-lg font-bold">{{ $sections[$selectedSection]['label'] ?? 'Secção' }}</h2>
                        <p class="mt-1 text-xs leading-5 text-zinc-400">Altera apenas o conteúdo. O design do modelo mantém-se.</p>
                    </div>

                    <div class="space-y-4">
                        @foreach(['title'=>'Título','subtitle'=>'Subtítulo','description'=>'Descrição','body'=>'Texto','button_label'=>'Texto do botão','button_url'=>'Ligação do botão'] as $field => $label)
                            @if(array_key_exists($field, $selectedContent))
                                <div>
                                    <label class="text-xs font-semibold text-zinc-600">{{ $label }}</label>
                                    @if(in_array($field, ['description','body'], true))
                                        <textarea wire:model.live.debounce.500ms="sections.{{ $selectedSection }}.content.{{ $field }}" rows="4" class="mt-1.5 w-full rounded-lg border-zinc-200 bg-white px-3 py-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                    @else
                                        <input wire:model.live.debounce.500ms="sections.{{ $selectedSection }}.content.{{ $field }}" class="mt-1.5 w-full rounded-lg border-zinc-200 bg-white px-3 py-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @endif
                                </div>
                            @endif
                        @endforeach

                        @if($sections[$selectedSection]['type'] === 'product_grid')
                            <div class="rounded-xl bg-zinc-50 p-4">
                                <p class="text-sm font-semibold">Produtos</p>
                                <p class="mt-1 text-xs leading-5 text-zinc-500">Os produtos activos da tua loja aparecem automaticamente nesta secção.</p>
                                <a href="{{ route('products') }}" class="mt-3 inline-flex w-full items-center justify-center rounded-lg bg-zinc-900 px-3 py-2.5 text-xs font-semibold text-white">Gerir produtos</a>
                            </div>
                        @endif

                        <button type="button" wire:click="save" class="w-full rounded-lg bg-zinc-900 px-4 py-3 text-sm font-semibold text-white hover:bg-zinc-800">Guardar alterações</button>
                    </div>
                @else
                    <div class="py-12 text-center">
                        <p class="text-sm font-semibold">Edita o teu website</p>
                        <p class="mt-2 text-xs leading-5 text-zinc-400">Clica numa parte do website para alterar o texto.</p>
                    </div>
                @endif
            </div>
        </aside>
    </div>
</div>
