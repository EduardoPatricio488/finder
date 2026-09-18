<div class="mx-auto max-w-7xl space-y-6 p-6 lg:p-8">
    <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-indigo-600">{{ $site->name }}</p>
            <h1 class="mt-1 text-3xl font-semibold tracking-tight text-zinc-950">Menus</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-zinc-500">Define a navegação do website de forma simples. Neste website estão disponíveis apenas <strong class="font-semibold text-zinc-700">Início, Sobre mim e Contactos</strong>.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-semibold {{ $menuApplied ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-amber-200 bg-amber-50 text-amber-700' }}">
                <span class="size-1.5 rounded-full {{ $menuApplied ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                {{ $menuApplied ? 'Aplicado no site' : 'Alterações por aplicar' }}
            </span>
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
                    <h2 class="font-semibold text-zinc-950">Adicionar página</h2>
                    <p class="mt-1 text-xs leading-5 text-zinc-500">Escolhe uma das três páginas disponíveis para a navegação.</p>
                </div>
            </div>

            <div class="mt-6 grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="text-sm font-medium text-zinc-800">Texto apresentado no menu</label>
                    <input wire:model="label" placeholder="Ex.: Início" class="mt-1.5 w-full rounded-xl border border-zinc-200 px-3.5 py-2.5 text-sm" />
                    @error('label') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="text-sm font-medium text-zinc-800">Página</label>
                    <select wire:model="pageId" class="mt-1.5 w-full rounded-xl border border-zinc-200 bg-white px-3.5 py-2.5 text-sm">
                        <option value="">Selecionar página</option>
                        @foreach ($pages as $page)
                            <option value="{{ $page->id }}">{{ $this->pageLabel($page->slug) }}</option>
                        @endforeach
                    </select>
                    @error('pageId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="text-sm font-medium text-zinc-800">Tipo</label>
                    <select wire:model="parentId" class="mt-1.5 w-full rounded-xl border border-zinc-200 bg-white px-3.5 py-2.5 text-sm">
                        <option value="">Item principal</option>
                        @foreach ($items as $item)
                            @if (! $item->parent_id)
                                <option value="{{ $item->id }}">Submenu de: {{ $item->label }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-4 flex flex-wrap items-center justify-between gap-4">
                <label class="flex items-center gap-2 text-sm text-zinc-600">
                    <input type="checkbox" wire:model="isVisible" class="rounded border-zinc-300" />
                    Visível no website
                </label>
                <button type="button" wire:click="addItem" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700">
                    <flux:icon name="plus" class="size-4" />
                    Adicionar ao menu
                </button>
            </div>
        </div>
    </div>

    <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-zinc-950">Estrutura da navegação</h2>
                <p class="mt-1 text-xs text-zinc-500">Organiza os links que vão aparecer no cabeçalho.</p>
            </div>
            <span class="text-xs font-semibold text-zinc-400">{{ $items->count() }} {{ $items->count() === 1 ? 'item principal' : 'itens principais' }}</span>
        </div>

        <div class="mt-5 space-y-2">
            @forelse ($items->whereNull('parent_id') as $item)
                <div class="rounded-2xl border border-zinc-200 bg-zinc-50/70 p-3">
                    <div class="flex items-center gap-3">
                        <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-white text-zinc-400 shadow-sm">
                            <flux:icon name="bars-2" class="size-4" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-zinc-900">{{ $item->label }}</p>
                            <p class="text-xs text-zinc-400">{{ $item->page ? $this->pageLabel($item->page->slug) : 'Página' }}</p>
                        </div>
                        <span class="hidden rounded-full px-2.5 py-1 text-[11px] font-semibold sm:inline-flex {{ $item->is_visible ? 'bg-emerald-50 text-emerald-700' : 'bg-zinc-200 text-zinc-500' }}">
                            {{ $item->is_visible ? 'Visível' : 'Oculto' }}
                        </span>
                        <button type="button" wire:click="toggleItem({{ $item->id }})" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">{{ $item->is_visible ? 'Ocultar' : 'Mostrar' }}</button>
                        <button type="button" wire:click="removeItem({{ $item->id }})" wire:confirm="Eliminar este item?" class="text-xs font-semibold text-red-600 hover:text-red-800">Eliminar</button>
                    </div>

                    @foreach ($item->children as $child)
                        <div class="mt-2 ml-8 flex items-center gap-3 rounded-xl border border-dashed border-zinc-200 bg-white px-3 py-2.5">
                            <span class="text-zinc-300">↳</span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-zinc-800">{{ $child->label }}</p>
                                <p class="text-xs text-zinc-400">{{ $child->page ? $this->pageLabel($child->page->slug) : 'Página' }}</p>
                            </div>
                            <span class="text-[11px] font-semibold {{ $child->is_visible ? 'text-emerald-600' : 'text-zinc-400' }}">{{ $child->is_visible ? 'Visível' : 'Oculto' }}</span>
                            <button type="button" wire:click="toggleItem({{ $child->id }})" class="text-xs font-semibold text-indigo-600">{{ $child->is_visible ? 'Ocultar' : 'Mostrar' }}</button>
                            <button type="button" wire:click="removeItem({{ $child->id }})" wire:confirm="Eliminar este item?" class="text-xs font-semibold text-red-600">Eliminar</button>
                        </div>
                    @endforeach
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-zinc-200 bg-zinc-50 p-10 text-center">
                    <div class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-white text-zinc-400 shadow-sm">
                        <flux:icon name="bars-3" class="size-5" />
                    </div>
                    <p class="mt-4 text-sm font-semibold text-zinc-800">Ainda não tens páginas no menu</p>
                    <p class="mt-1 text-xs text-zinc-500">Adiciona Início, Sobre mim ou Contactos acima.</p>
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
