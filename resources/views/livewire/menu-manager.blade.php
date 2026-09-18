<div class="mx-auto max-w-7xl space-y-6 p-6 lg:p-8">
    <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-indigo-600">{{ $site->name }}</p>
            <h1 class="mt-1 text-3xl font-semibold tracking-tight text-zinc-950">Menus</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-zinc-500">Define a navegação do website de forma simples. Neste website estão disponíveis apenas <strong class="font-semibold text-zinc-700">Início, Sobre mim e Contactos</strong>.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-semibold {{ $menuApplied ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-amber-200 bg-amber-50 text-amber-700' }}">
                <span class="size-1.5 rounded-full {{ $menuApplied ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                {{ $menuApplied ? 'Aplicado no site' : 'Alterações por aplicar' }}
            </span>
            <button type="button" wire:click="applyMenu" @disabled(! $menuId) class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-40">
                <flux:icon name="check-circle" class="size-4" />
                Aplicar no site
            </button>
        </div>
    </div>

    @if (session('status'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('status') }}</div>
    @endif
    @if (session('menu-applied'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('menu-applied') }}</div>
    @endif

    <div class="grid gap-6 lg:grid-cols-[.8fr_1.2fr]">
        <form wire:submit="saveMenu" class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm">
            <div class="flex items-start gap-3">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-2xl bg-zinc-100 text-zinc-700">
                    <flux:icon name="bars-3" class="size-5" />
                </div>
                <div>
                    <h2 class="font-semibold text-zinc-950">Menu principal</h2>
                    <p class="mt-1 text-xs leading-5 text-zinc-500">O menu que aparece no cabeçalho do website.</p>
                </div>
            </div>

            <div class="mt-6 space-y-4">
                <div>
                    <label class="text-sm font-medium text-zinc-800">Nome</label>
                    <input wire:model="menuName" class="mt-1.5 w-full rounded-xl border border-zinc-200 bg-white px-3.5 py-2.5 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10" />
                    @error('menuName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-sm font-medium text-zinc-800">Localização</label>
                    <select wire:model="menuLocation" class="mt-1.5 w-full rounded-xl border border-zinc-200 bg-white px-3.5 py-2.5 text-sm">
                        <option value="header">Cabeçalho</option>
                    </select>
                    <p class="mt-1.5 text-xs text-zinc-400">Neste website, a navegação é apresentada no cabeçalho.</p>
                </div>
            </div>

            <button class="mt-6 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-zinc-950 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-zinc-800">
                <flux:icon name="check" class="size-4" />
                Guardar menu
            </button>
        </form>

        <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm">
            <div class="flex items-start gap-3">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                    <flux:icon name="plus" class="size-5" />
                </div>
                <div>
                    <h2 class="font-semibold text-zinc-950">Páginas disponíveis</h2>
                    <p class="mt-1 text-xs leading-5 text-zinc-500">Início, Sobre mim e Contactos já fazem parte do menu. Podes mudar o nome de cada uma, mas não as podes eliminar.</p>
                </div>
            </div>

            <div class="mt-6 space-y-3">
                @foreach ($pages as $page)
                    @php($requiredItemId = $requiredItemIds[$page->slug] ?? null)
                    @php($requiredItem = $requiredItemId ? $items->firstWhere('id', $requiredItemId) : null)
                    <div class="flex flex-col gap-3 rounded-2xl border border-zinc-200 bg-zinc-50/70 p-4 sm:flex-row sm:items-center">
                        <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-white text-zinc-500 shadow-sm">
                            <flux:icon name="{{ match($page->slug) { 'home' => 'home', 'about' => 'user', 'contact' => 'envelope', default => 'document-text' } }}" class="size-5" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-400">{{ $this->pageLabel($page->slug) }}</p>
                            <input wire:model="requiredLabels.{{ $page->slug }}" class="mt-1 w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm font-semibold text-zinc-900" />
                        </div>
                        <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700">
                            <span class="size-1.5 rounded-full bg-emerald-500"></span>
                            Obrigatório
                        </span>
                    </div>
                @endforeach
            </div>

            <p class="mt-4 text-xs leading-5 text-zinc-400">Estas três páginas são criadas automaticamente e permanecem sempre no menu. Aqui só podes alterar o nome apresentado.</p>

            </div>
        </div>
    </div>

    <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-zinc-950">Estrutura da navegação</h2>
                <p class="mt-1 text-xs text-zinc-500">Organiza os links que vão aparecer no cabeçalho.</p>
            </div>
            <span class="text-xs font-semibold text-zinc-400">{{ $menus->count() }} {{ $menus->count() === 1 ? 'menu' : 'menus' }}</span>
        </div>
        <div class="mt-5 space-y-4">
            @forelse ($menus as $savedMenu)
                <div class="rounded-2xl border {{ $savedMenu->id === $menuId ? 'border-indigo-200 bg-indigo-50/30' : 'border-zinc-200 bg-zinc-50/70' }} p-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-white text-zinc-400 shadow-sm">
                                <flux:icon name="bars-3" class="size-4" />
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-zinc-900">{{ $savedMenu->name }}</p>
                                <p class="text-xs text-zinc-400">{{ $savedMenu->location === 'header' ? 'Cabeçalho' : $savedMenu->location }}</p>
                            </div>
                        </div>
                        <button type="button" wire:click="$set('menuId', {{ $savedMenu->id }})" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                            {{ $savedMenu->id === $menuId ? 'Em edição' : 'Editar menu' }}
                        </button>
                    </div>
                    @if ($savedMenu->items->isNotEmpty())
                        <div class="mt-3 space-y-2">
                            @foreach ($savedMenu->items->whereNull('parent_id') as $item)
                                <div class="flex items-center gap-3 rounded-xl border border-zinc-200 bg-white px-3 py-2.5">
                                    <div class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-zinc-50 text-zinc-400">
                                        <flux:icon name="bars-2" class="size-4" />
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-semibold text-zinc-900">{{ $item->label }}</p>
                                        <p class="text-xs text-zinc-400">{{ $item->page ? $this->pageLabel($item->page->slug) : ($item->url ?: 'Página') }}</p>
                                    </div>
                                    <span class="text-[11px] font-semibold {{ $item->is_visible ? 'text-emerald-600' : 'text-zinc-400' }}">{{ $item->is_visible ? 'Visível' : 'Oculto' }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="mt-3 text-xs text-zinc-400">Este menu ainda não tem itens.</p>
                    @endif
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-zinc-200 bg-zinc-50 p-10 text-center">
                    <div class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-white text-zinc-400 shadow-sm">
                        <flux:icon name="bars-3" class="size-5" />
                    </div>
                    <p class="mt-4 text-sm font-semibold text-zinc-800">Ainda não tens menus</p>
                    <p class="mt-1 text-xs text-zinc-500">Guarda um menu acima e este aparecerá automaticamente nesta estrutura.</p>
                </div>
            @endforelse
        </div>
    </div>

    <div class="sticky bottom-4 z-20 rounded-2xl border border-zinc-200 bg-white/95 p-3 shadow-xl shadow-zinc-900/10 backdrop-blur">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-zinc-900">{{ $menuApplied ? 'A navegação está aplicada no site.' : 'As alterações ainda não estão aplicadas.' }}</p>
                <p class="text-xs text-zinc-500">{{ $menuApplied ? 'Podes continuar a editar o menu e voltar a aplicar quando terminares.' : 'Guarda primeiro o menu e depois aplica-o no site.' }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('site.public', [$site, 'pageSlug' => null, 'preview' => 1]) }}" target="_blank" class="inline-flex items-center justify-center gap-2 rounded-xl border border-zinc-200 px-4 py-2.5 text-sm font-semibold text-zinc-700 hover:bg-zinc-50">
                    <flux:icon name="eye" class="size-4" />
                    Ver site
                </a>
                <button type="button" wire:click="applyMenu" @disabled(! $menuId) class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-40">
                    <flux:icon name="check-circle" class="size-4" />
                    Aplicar no site
                </button>
            </div>
        </div>
    </div>
</div>
