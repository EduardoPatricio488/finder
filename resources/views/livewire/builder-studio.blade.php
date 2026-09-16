<div class="min-h-screen bg-[#f4f5f7] text-zinc-950 antialiased" x-data="{ device: 'desktop', sidebar: false, editor: false }">
    <header class="sticky top-0 z-50 border-b border-zinc-200/80 bg-white/95 backdrop-blur-xl">
        <div class="mx-auto flex h-[68px] max-w-[1920px] items-center gap-2 px-3 sm:px-5">
            <a href="{{ route('admin.site.dashboard', $site) }}" class="flex h-10 shrink-0 items-center gap-2 rounded-xl px-2.5 text-sm font-semibold text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-950">
                <span class="text-lg">←</span><span class="hidden md:inline">Sair</span>
            </a>
            <div class="h-7 w-px bg-zinc-200"></div>
            <div class="flex min-w-0 items-center gap-2.5">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-zinc-950 text-sm font-black text-white shadow-sm">F</div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <p class="max-w-[180px] truncate text-sm font-bold sm:max-w-[260px]">{{ $site->name }}</p>
                        <span class="hidden rounded-full bg-amber-50 px-2 py-0.5 text-[9px] font-black uppercase tracking-wider text-amber-700 sm:inline-flex">Rascunho</span>
                    </div>
                    <p class="hidden text-[11px] text-zinc-400 sm:block">Website Builder · {{ $statusMessage ?: 'Tudo guardado' }}</p>
                </div>
            </div>
            <div class="ml-auto flex items-center gap-1.5 sm:gap-2">
                <a href="{{ route('site.public', ['site' => $site, 'preview' => 1]) }}" target="_blank" class="hidden h-10 items-center gap-2 rounded-xl border border-zinc-200 bg-white px-3 text-xs font-bold text-zinc-700 transition hover:bg-zinc-50 md:flex">
                    <span>◉</span> Pré-visualizar
                </a>
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
                    <span class="rounded-lg bg-zinc-100 px-2 py-1 text-[10px] font-bold text-zinc-500">{{ count($pages) }}</span>
                </div>
                <div class="space-y-1.5">
                    @foreach($pages as $page)
                        <button type="button" wire:click="loadPage({{ $page['id'] }})" class="group flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left transition {{ $pageId === $page['id'] ? 'bg-zinc-950 text-white shadow-md' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-950' }}">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-xs font-bold {{ $pageId === $page['id'] ? 'bg-white/10' : 'bg-zinc-100 group-hover:bg-white' }}">{{ $page['is_homepage'] ? '⌂' : '○' }}</span>
                            <span class="min-w-0 flex-1 truncate text-sm font-semibold">{{ $page['name'] }}</span>
                            @if($page['is_homepage'])<span class="text-[8px] font-black uppercase tracking-wider opacity-50">Home</span>@endif
                        </button>
                    @endforeach
                </div>
                <button type="button" wire:click="createPage" class="mt-2 flex w-full items-center justify-center rounded-xl border border-dashed border-zinc-300 px-3 py-2.5 text-xs font-bold text-zinc-500 transition hover:border-zinc-500 hover:bg-zinc-50 hover:text-zinc-950">+ Nova página</button>

                <div class="my-6 h-px bg-zinc-100"></div>
                <p class="text-[9px] font-black uppercase tracking-[0.2em] text-zinc-400">Conteúdo</p>
                <a href="{{ route('products') }}" class="mt-2 flex items-center gap-3 rounded-xl border border-zinc-200 bg-white p-3 transition hover:-translate-y-0.5 hover:border-zinc-300 hover:shadow-md">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-zinc-100">▦</span>
                    <span class="min-w-0 flex-1"><span class="block text-xs font-black">Produtos</span><span class="mt-0.5 block text-[10px] text-zinc-400">Adicionar e gerir</span></span>
                    <span class="text-zinc-300">→</span>
                </a>
                <div class="mt-4 rounded-2xl bg-zinc-950 p-4 text-white shadow-xl">
                    <span class="rounded-full bg-white/10 px-2 py-1 text-[8px] font-black uppercase tracking-[0.18em] text-white/60">Finder Premium</span>
                    <p class="mt-3 text-sm font-black tracking-tight">O website já está quase pronto.</p>
                    <p class="mt-1 text-[11px] leading-5 text-white/50">Só tens de personalizar o conteúdo e adicionar os teus produtos.</p>
                </div>
            </div>
        </aside>

        <main class="min-w-0 bg-[#f4f5f7] p-3 sm:p-5 xl:p-7">
            <div class="mx-auto max-w-[1180px]">
                <div class="mb-3 flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <input value="{{ $this->currentPage()['name'] ?? 'Página' }}" wire:blur="updatePageName($event.target.value)" class="w-full max-w-sm border-0 bg-transparent p-0 text-lg font-black tracking-tight outline-none focus:ring-0 sm:text-xl" aria-label="Nome da página">
                        <p class="mt-0.5 text-[11px] text-zinc-400">/{{ $this->currentPage()['slug'] ?? '' }}</p>
                    </div>
                    <div class="flex shrink-0 items-center gap-1.5">
                        <span class="hidden rounded-full bg-white px-3 py-1.5 text-[10px] font-bold text-zinc-400 shadow-sm sm:inline-flex">{{ $dirty ? '● Alterações por guardar' : '✓ Guardado' }}</span>
                        <button type="button" @click="sidebar = !sidebar" class="flex h-9 items-center rounded-xl border border-zinc-200 bg-white px-3 text-xs font-bold lg:hidden">Menu</button>
                        <button type="button" @click="editor = !editor" class="flex h-9 items-center rounded-xl border border-zinc-200 bg-white px-3 text-xs font-bold lg:hidden">Editar</button>
                    </div>
                </div>

                <div x-show="sidebar" x-cloak class="mb-3 rounded-2xl border border-zinc-200 bg-white p-4 shadow-lg lg:hidden">
                    <div class="space-y-1.5">
                        @foreach($pages as $page)
                            <button type="button" wire:click="loadPage({{ $page['id'] }})" @click="sidebar=false" class="flex w-full items-center gap-3 rounded-xl px-3 py-3 text-left {{ $pageId === $page['id'] ? 'bg-zinc-950 text-white' : 'hover:bg-zinc-100' }}">
                                <span class="font-bold">{{ $page['is_homepage'] ? '⌂' : '○' }}</span><span class="text-sm font-semibold">{{ $page['name'] }}</span>
                            </button>
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
                        <div class="bg-white">
                            @forelse($sections as $index => $section)
                                @php($content = is_array($section['content'] ?? null) ? $section['content'] : [])
                                @php($items = is_array($content['items'] ?? null) ? $content['items'] : [])
                                <section wire:key="premium-final-section-{{ $section['id'] ?? 'new-'.$index }}" wire:click="selectSection({{ $index }})" class="group relative cursor-pointer border-b border-zinc-100 px-6 py-14 transition sm:px-10 sm:py-16 {{ $selectedSection === $index ? 'z-10 ring-2 ring-inset ring-zinc-950' : 'hover:bg-zinc-50/60' }}">
                                    @if($selectedSection === $index)<span class="absolute right-4 top-4 rounded-full bg-zinc-950 px-2.5 py-1 text-[8px] font-black uppercase tracking-wider text-white">A editar</span>@endif
                                    <div class="mx-auto max-w-4xl">
                                        @if(!empty($content['subtitle']))<p class="mb-3 text-center text-[9px] font-black uppercase tracking-[0.24em] text-zinc-400">{{ $content['subtitle'] }}</p>@endif
                                        @if(!empty($content['title']))<h1 class="text-center text-4xl font-black tracking-[-0.045em] text-zinc-950 sm:text-5xl lg:text-6xl">{{ $content['title'] }}</h1>@endif
                                        @if(!empty($content['description']) || !empty($content['body']))<p class="mx-auto mt-5 max-w-2xl whitespace-pre-line text-center text-sm leading-7 text-zinc-500 sm:text-base">{{ $content['description'] ?? $content['body'] }}</p>@endif

                                        @if(!empty($content['button_label']))
                                            <div class="mt-7 flex justify-center"><span class="rounded-xl bg-zinc-950 px-5 py-3 text-xs font-black text-white shadow-lg">{{ $content['button_label'] }}</span></div>
                                        @endif

                                        @if($section['type'] === 'product_grid')
                                            @php($products = $site->products()->where('is_active', true)->latest()->limit((int) ($content['limit'] ?? 6))->get())
                                            <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                                                @forelse($products as $product)
                                                    <article class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
                                                        @if($product->image_url)<img src="{{ asset('storage/'.$product->image_url) }}" alt="{{ $product->name }}" class="h-48 w-full object-cover">@else<div class="flex h-48 items-center justify-center bg-zinc-100 text-xs font-medium text-zinc-400">Sem imagem</div>@endif
                                                        <div class="p-5"><h3 class="font-bold tracking-tight">{{ $product->name }}</h3><p class="mt-1.5 line-clamp-2 text-sm leading-6 text-zinc-500">{{ $product->description }}</p><p class="mt-4 text-lg font-black">€ {{ number_format((float) $product->price, 2, ',', '.') }}</p></div>
                                                    </article>
                                                @empty
                                                    <div class="col-span-full rounded-2xl border border-dashed border-zinc-300 bg-zinc-50 p-10 text-center"><p class="font-bold">O catálogo está vazio</p><p class="mt-1 text-sm text-zinc-400">Adiciona os teus produtos e eles aparecem aqui automaticamente.</p><a href="{{ route('products') }}" class="mt-5 inline-flex rounded-xl bg-zinc-950 px-5 py-3 text-xs font-black text-white">Adicionar produtos →</a></div>
                                                @endforelse
                                            </div>
                                        @elseif(!empty($items))
                                            <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                                                @foreach($items as $item)
                                                    <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm">
                                                        <div class="mb-4 flex h-9 w-9 items-center justify-center rounded-xl bg-zinc-100 text-xs font-black">{{ $loop->iteration }}</div>
                                                        <h3 class="font-bold tracking-tight">{{ $item['title'] ?? $item['name'] ?? $item['question'] ?? $item['label'] ?? 'Conteúdo' }}</h3>
                                                        @if(!empty($item['description']) || !empty($item['quote']) || !empty($item['answer']) || !empty($item['excerpt']))<p class="mt-2 text-sm leading-6 text-zinc-500">{{ $item['description'] ?? $item['quote'] ?? $item['answer'] ?? $item['excerpt'] }}</p>@endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif

                                        @if(empty($content['title']) && empty($content['description']) && empty($content['body']) && empty($items) && $section['type'] !== 'product_grid')
                                            <p class="text-center text-sm text-zinc-400">Clica para editar esta área.</p>
                                        @endif
                                    </div>
                                </section>
                            @empty
                                <div class="flex min-h-[650px] items-center justify-center p-8 text-center"><div class="max-w-md"><div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-zinc-100 text-2xl">✦</div><h2 class="mt-5 text-2xl font-black">O teu website está pronto</h2><p class="mt-2 text-sm leading-6 text-zinc-500">Personaliza o conteúdo e publica quando estiveres satisfeito.</p></div></div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <aside x-show="editor || window.innerWidth >= 1024" x-transition class="fixed inset-x-0 bottom-0 z-40 max-h-[75vh] overflow-y-auto border-t border-zinc-200 bg-white shadow-2xl lg:sticky lg:top-[68px] lg:block lg:max-h-[calc(100vh-68px)] lg:border-l lg:border-t-0 lg:shadow-none">
            <div class="p-5 sm:p-6">
                <div class="mb-5 flex items-center justify-between lg:hidden"><div><p class="text-[9px] font-black uppercase tracking-[0.2em] text-zinc-400">Editor</p><h2 class="mt-1 text-lg font-black">Personalizar</h2></div><button type="button" @click="editor=false" class="h-9 w-9 rounded-xl bg-zinc-100 text-zinc-500">×</button></div>
                @if($selectedSection !== null && isset($sections[$selectedSection]))
                    @php($selectedContent = is_array($sections[$selectedSection]['content'] ?? null) ? $sections[$selectedSection]['content'] : [])
                    <div class="mb-6 border-b border-zinc-100 pb-5">
                        <p class="text-[9px] font-black uppercase tracking-[0.2em] text-zinc-400">Personalizar</p>
                        <div class="mt-1 flex items-center justify-between gap-3"><h2 class="text-lg font-black tracking-tight">{{ $sections[$selectedSection]['label'] ?? 'Secção' }}</h2><span class="rounded-lg bg-zinc-100 px-2 py-1 text-[8px] font-bold text-zinc-500">{{ ucfirst(str_replace('_', ' ', $sections[$selectedSection]['type'] ?? '')) }}</span></div>
                        <p class="mt-2 text-[11px] leading-5 text-zinc-400">O design fica protegido. Altera apenas aquilo que os teus clientes vão ver.</p>
                    </div>
                    <div class="space-y-4">
                        @foreach(['title'=>'Título','subtitle'=>'Subtítulo','description'=>'Descrição','body'=>'Texto','button_label'=>'Botão','button_url'=>'Ligação'] as $field => $label)
                            @if(array_key_exists($field, $selectedContent))
                                <div>
                                    <label class="text-[10px] font-black uppercase tracking-wider text-zinc-500">{{ $label }}</label>
                                    @if(in_array($field, ['description','body'], true))
                                        <textarea wire:model.live.debounce.500ms="sections.{{ $selectedSection }}.content.{{ $field }}" rows="4" class="mt-1.5 w-full resize-y rounded-xl border-zinc-200 bg-zinc-50 px-3 py-2.5 text-sm leading-6 outline-none transition focus:border-zinc-900 focus:bg-white focus:ring-2 focus:ring-zinc-900/5"></textarea>
                                    @else
                                        <input wire:model.live.debounce.500ms="sections.{{ $selectedSection }}.content.{{ $field }}" class="mt-1.5 w-full rounded-xl border-zinc-200 bg-zinc-50 px-3 py-2.5 text-sm outline-none transition focus:border-zinc-900 focus:bg-white focus:ring-2 focus:ring-zinc-900/5">
                                    @endif
                                </div>
                            @endif
                        @endforeach

                        @if($sections[$selectedSection]['type'] === 'product_grid')
                            <div class="rounded-2xl bg-zinc-950 p-4 text-white">
                                <p class="text-sm font-black">Produtos</p><p class="mt-1 text-[11px] leading-5 text-white/50">O catálogo é sincronizado automaticamente com os produtos activos.</p>
                                <a href="{{ route('products') }}" class="mt-3 flex w-full items-center justify-center rounded-xl bg-white px-3 py-3 text-xs font-black text-zinc-950">Adicionar / gerir produtos →</a>
                            </div>
                        @endif
                        <button type="button" wire:click="save" class="w-full rounded-xl bg-zinc-950 px-4 py-3.5 text-sm font-black text-white shadow-lg transition hover:-translate-y-0.5 hover:bg-zinc-800">Guardar alterações</button>
                    </div>
                @else
                    <div class="py-16 text-center"><div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-zinc-100 text-xl">✦</div><p class="mt-4 text-sm font-black">Começa a personalizar</p><p class="mt-1 text-xs leading-5 text-zinc-400">Clica numa área do website para editar o conteúdo.</p></div>
                @endif
            </div>
        </aside>
    </div>

    <div wire:loading.flex class="fixed inset-0 z-[100] items-center justify-center bg-zinc-950/20 backdrop-blur-[2px]">
        <div class="flex items-center gap-3 rounded-2xl bg-white px-5 py-4 text-sm font-bold shadow-2xl"><span class="h-4 w-4 animate-spin rounded-full border-2 border-zinc-300 border-t-zinc-950"></span>A guardar...</div>
    </div>
</div>
