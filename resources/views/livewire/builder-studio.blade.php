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

        <main class="min-h-0 min-w-0 overflow-y-auto bg-[#f4f5f7] p-3 sm:p-5 xl:p-7 lg:max-h-[calc(100vh-68px)]">
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
                    </div>
                    <button type="button" wire:click="createPage" @click="sidebar=false" class="mt-2 flex w-full items-center justify-center rounded-xl border border-dashed border-zinc-300 px-3 py-3 text-xs font-bold text-zinc-500">+ Nova página</button>
                </div>

                <div class="rounded-2xl border border-zinc-200 bg-white p-2 shadow-sm sm:p-3">
                    <div class="flex items-center justify-between border-b border-zinc-100 px-2 py-2">
                        <div class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-emerald-500"></span><span class="text-[10px] font-bold uppercase tracking-wider text-zinc-400">Canvas</span></div>
                        <span class="text-[10px] font-semibold text-zinc-400">{{ ucfirst($device) }}</span>
                    </div>
                    <div class="overflow-hidden rounded-xl border border-zinc-100 bg-zinc-50">
                        @foreach($sections as $i => $section)
                            @include('livewire.builder-document-renderer', ['section' => $section, 'sectionIndex' => $i])
                        @endforeach
                    </div>
                </div>
            </div>
        </main>

        <aside class="hidden border-l border-zinc-200 bg-white lg:block">
            <div class="sticky top-[68px] max-h-[calc(100vh-68px)] overflow-y-auto p-5">
                <div class="mb-5 flex items-center justify-between"><div><p class="text-[9px] font-black uppercase tracking-[0.2em] text-zinc-400">Inspector</p><h2 class="mt-1 text-base font-black">Editar</h2></div><span class="rounded-lg bg-zinc-100 px-2 py-1 text-[9px] font-bold text-zinc-400">{{ $selectedSection !== null ? 'Secção' : 'Página' }}</span></div>
                @if($selectedSection !== null && isset($sections[$selectedSection]))
                    @php($selected = $sections[$selectedSection])
                    <div class="space-y-4">
                        <label class="block text-xs font-bold">Nome da secção<input value="{{ $selected['label'] ?? '' }}" wire:blur="updateSectionLabel({{ $selectedSection }}, $event.target.value)" class="mt-1.5 w-full rounded-xl border-zinc-200 text-sm"></label>
                        <div class="rounded-xl border border-zinc-200 p-3"><p class="text-[9px] font-black uppercase tracking-wider text-zinc-400">Tipo</p><p class="mt-1 text-sm font-bold">{{ $selected['type'] }}</p></div>
                        <div class="grid grid-cols-2 gap-2"><button type="button" wire:click="duplicateSection({{ $selectedSection }})" class="rounded-xl border border-zinc-200 py-2.5 text-xs font-bold">Duplicar</button><button type="button" wire:click="deleteSection({{ $selectedSection }})" wire:confirm="Eliminar esta secção?" class="rounded-xl border border-red-100 py-2.5 text-xs font-bold text-red-600">Eliminar</button></div>
                        <button type="button" wire:click="save" class="w-full rounded-xl bg-zinc-950 py-3 text-xs font-black text-white">Guardar alterações</button>
                    </div>
                @else
                    <div class="rounded-2xl bg-zinc-50 p-4 text-xs leading-5 text-zinc-500">Selecciona uma secção no canvas para editar o conteúdo.</div>
                @endif
            </div>
        </aside>
    </div>
</div>
