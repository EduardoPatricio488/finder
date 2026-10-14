<div x-data="{ showCreatePageModal: false }">
<div class="mx-auto max-w-7xl space-y-6 p-6 lg:p-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0 flex-1 py-1">
            <h1 class="text-3xl font-bold tracking-tight text-zinc-950 dark:text-white">Menus do website</h1>
            <p class="mt-3 max-w-2xl text-sm leading-6 text-zinc-500">Define a navegação do website selecionado. As páginas essenciais são Início, Sobre mim e Contactos.</p>
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
            <a href="{{ route('site.public', [$site, 'pageSlug' => null, 'preview' => 1]) }}" target="_blank" class="inline-flex items-center justify-center gap-2 rounded-xl border border-zinc-200 px-4 py-2.5 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-50">
                <flux:icon name="eye" class="size-4" />
                Ver site
            </a>
        </div>
    </div>



    <div class="mb-2">
        <x-site-workspace-selector :current-site="$site" route-name="menus" />
    </div>

    @if (session('status'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('status') }}</div>
    @endif
    @if (session('menu-applied'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('menu-applied') }}</div>
    @endif

    <div class="grid gap-6">
        <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm">
            <div class="flex items-start gap-3">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-2xl bg-zinc-100 text-zinc-700">
                    <flux:icon name="bars-3" class="size-5" />
                </div>
                <div>
                    <h2 class="font-semibold text-zinc-950">Sidebar</h2>
                    <p class="mt-1 text-xs leading-5 text-zinc-500">Escolhe a posição, o comportamento e as páginas apresentadas na sidebar.</p>
                </div>
            </div>

            <form wire:submit="saveSidebar" class="mt-6 space-y-5">
                <div>
                    <label for="sidebarPosition" class="text-sm font-semibold text-zinc-800">Posição da sidebar</label>
                    <select id="sidebarPosition" wire:model="sidebarPosition" class="mt-1.5 w-full rounded-xl border border-zinc-200 bg-white px-3.5 py-2.5 text-sm">
                        <option value="top">Em cima — horizontal</option>
                        <option value="left">À esquerda — vertical</option>
                        <option value="right">À direita — vertical</option>
                        <option value="bottom">Em baixo — horizontal</option>
                    </select>
                    @error('sidebarPosition') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="sidebarType" class="text-sm font-semibold text-zinc-800">Tipo de sidebar</label>
                    <select id="sidebarType" wire:model="sidebarType" class="mt-1.5 w-full rounded-xl border border-zinc-200 bg-white px-3.5 py-2.5 text-sm">
                        <option value="fixed">Fixa — sempre visível</option>
                        <option value="collapsible">Recolhível — pode ser aberta e fechada</option>
                        <option value="compact">Recolhida — só ícones</option>
                    </select>
                    @error('sidebarType') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-sm font-semibold text-zinc-800">Páginas na sidebar</p>
                        <span class="text-xs text-zinc-400">{{ count($sidebarPageIds) }} selecionadas</span>
                    </div>
                    <p class="mt-1 text-xs leading-5 text-zinc-500">Marca as páginas que queres apresentar na navegação lateral.</p>
                    <div class="mt-3 max-h-64 space-y-2 overflow-y-auto rounded-2xl border border-zinc-200 p-3">
                        @forelse($allPages as $sidebarPage)
                            <label for="sidebar-page-{{ $sidebarPage->id }}" class="flex cursor-pointer items-center gap-3 rounded-xl px-3 py-2.5 transition hover:bg-zinc-50">
                                <input id="sidebar-page-{{ $sidebarPage->id }}" type="checkbox" wire:model="sidebarPageIds" value="{{ $sidebarPage->id }}" class="size-4 rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-sm font-medium text-zinc-800">{{ $sidebarPage->name }}</span>
                                    <span class="block truncate text-xs text-zinc-400">/{{ $sidebarPage->slug }}</span>
                                </span>
                                <span class="rounded-full px-2 py-1 text-[10px] font-semibold {{ $sidebarPage->status === 'published' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">{{ $sidebarPage->status === 'published' ? 'Publicada' : 'Rascunho' }}</span>
                            </label>
                        @empty
                            <p class="p-3 text-sm text-zinc-500">Ainda não existem páginas neste website.</p>
                        @endforelse
                    </div>
                    @error('sidebarPageIds') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    @error('sidebarPageIds.*') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700">
                    <flux:icon name="check" class="size-4" /> Guardar sidebar
                </button>
            </form>
        </div>

    </div>

    <div x-cloak x-show="showCreatePageModal" x-transition.opacity @keydown.escape.window="showCreatePageModal = false" class="fixed inset-0 z-[100] flex items-center justify-center overflow-y-auto p-4 sm:p-6" style="display: none;">
        <div class="absolute inset-0 bg-zinc-950/60 backdrop-blur-sm" @click="showCreatePageModal = false"></div>
        <section x-transition.scale.origin.center class="relative z-10 w-full max-w-xl rounded-3xl border border-zinc-200 bg-white p-6 shadow-2xl sm:p-8" role="dialog" aria-modal="true" aria-labelledby="create-page-title">
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-start gap-3">
                    <div class="flex size-11 shrink-0 items-center justify-center rounded-2xl bg-indigo-600 text-white"><flux:icon name="plus" class="size-5" /></div>
                    <div>
                        <h2 id="create-page-title" class="text-lg font-bold text-zinc-950">Adicionar nova página</h2>
                        <p class="mt-1 text-sm leading-6 text-zinc-500">Define os dados da nova página. Podes editar o conteúdo depois de a criares.</p>
                    </div>
                </div>
                <button type="button" @click="showCreatePageModal = false" aria-label="Fechar janela" class="rounded-xl p-2 text-zinc-400 transition hover:bg-zinc-100 hover:text-zinc-800"><flux:icon name="x-mark" class="size-5" /></button>
            </div>
            <form wire:submit="createPage" class="mt-6 space-y-5">
                <div>
                    <label for="newPageName" class="text-sm font-semibold text-zinc-800">Nome da página</label>
                    <input id="newPageName" wire:model="newPageName" placeholder="Ex.: Serviços" required class="mt-1.5 w-full rounded-xl border border-zinc-200 bg-white px-3.5 py-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                    @error('newPageName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="newPageSlug" class="text-sm font-semibold text-zinc-800">Endereço (slug)</label>
                    <input id="newPageSlug" wire:model="newPageSlug" placeholder="Ex.: servicos (opcional)" class="mt-1.5 w-full rounded-xl border border-zinc-200 bg-white px-3.5 py-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                    <p class="mt-1.5 text-xs text-zinc-400">Se ficar vazio, é criado automaticamente a partir do nome.</p>
                    @error('newPageSlug') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="newPageStatus" class="text-sm font-semibold text-zinc-800">Estado inicial</label>
                    <select id="newPageStatus" wire:model="newPageStatus" class="mt-1.5 w-full rounded-xl border border-zinc-200 bg-white px-3.5 py-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                        <option value="draft">Rascunho</option>
                        <option value="published">Publicada</option>
                    </select>
                    @error('newPageStatus') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="flex flex-col-reverse gap-3 border-t border-zinc-100 pt-5 sm:flex-row sm:justify-end">
                    <button type="button" @click="showCreatePageModal = false" class="inline-flex items-center justify-center rounded-xl border border-zinc-200 px-4 py-2.5 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-50">Cancelar</button>
                    <button type="submit" wire:loading.attr="disabled" wire:target="createPage" class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-indigo-700 disabled:opacity-60">
                        <span wire:loading.remove wire:target="createPage"><flux:icon name="plus" class="size-4" /></span>
                        <span wire:loading wire:target="createPage"><flux:icon name="arrow-path" class="size-4 animate-spin" /></span>
                        Criar página
                    </button>
                </div>
            </form>
        </section>
    </div>

    <section class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-zinc-950">Todas as páginas do website</h2>
                <p class="mt-1 text-sm text-zinc-500">Seleciona qualquer página para editar textos, secções, imagens e widgets.</p>
                <div class="mt-3 flex items-start gap-2.5 rounded-xl border border-amber-200 bg-amber-50 px-3.5 py-3 text-sm text-amber-900">
                    <flux:icon name="information-circle" class="mt-0.5 size-5 shrink-0 text-amber-600" />
                    <p><strong>Páginas obrigatórias:</strong> Início, Sobre mim e Contactos são páginas essenciais do website. Mantém estas páginas no site e configura o respetivo conteúdo para os visitantes.</p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="rounded-full bg-zinc-100 px-3 py-1.5 text-xs font-semibold text-zinc-600">{{ $allPages->count() }} páginas</span>
                <button type="button" wire:click="$set('showCreatePageModal', true)" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <flux:icon name="plus" class="size-4" />
                    Adicionar página
                </button>
            </div>
        </div>
        <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($allPages as $page)
                <a href="{{ route('site.manage.pages.edit', $page->id) }}" class="group rounded-2xl border border-zinc-200 p-4 transition hover:-translate-y-0.5 hover:border-indigo-300 hover:shadow-md">
                    <div class="flex items-start justify-between gap-3">
                        <div><p class="font-semibold text-zinc-900">{{ $page->name }}</p><p class="mt-1 text-xs text-zinc-500">/ {{ $page->slug }}</p></div>
                        <span class="rounded-full px-2 py-1 text-[10px] font-semibold {{ $page->status === 'published' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">{{ $page->status === 'published' ? 'Publicada' : 'Rascunho' }}</span>
                    </div>
                    <div class="mt-3 flex flex-wrap gap-1.5">
                        @if($menuApplied && ($items->firstWhere('site_page_id', $page->id)?->is_visible ?? false))
                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-1 text-[10px] font-semibold text-emerald-700"><span class="size-1.5 rounded-full bg-emerald-500"></span>No menu do site</span>
                        @elseif($items->firstWhere('site_page_id', $page->id)?->is_visible ?? false)
                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-1 text-[10px] font-semibold text-amber-700"><span class="size-1.5 rounded-full bg-amber-500"></span>Menu por aplicar</span>
                        @endif
                        @if(in_array((string) $page->id, $sidebarPageIds, true))
                            <span class="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-2 py-1 text-[10px] font-semibold text-indigo-700"><span class="size-1.5 rounded-full bg-indigo-500"></span>Selecionada na sidebar</span>
                        @endif
                        @if(!($menuApplied && ($items->firstWhere('site_page_id', $page->id)?->is_visible ?? false)) && !in_array((string) $page->id, $sidebarPageIds, true) && !($items->firstWhere('site_page_id', $page->id)?->is_visible ?? false))
                            <span class="inline-flex items-center gap-1 rounded-full bg-zinc-100 px-2 py-1 text-[10px] font-semibold text-zinc-500"><span class="size-1.5 rounded-full bg-zinc-400"></span>Fora da navegação</span>
                        @endif
                    </div>
                    <p class="mt-4 text-sm font-semibold text-indigo-600 group-hover:text-indigo-800">Editar página →</p>
                </a>
            @endforeach
        </div>
    </section>

</div>
</div>