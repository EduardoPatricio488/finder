<div>
<div class="mx-auto max-w-7xl space-y-6 p-6 lg:p-8">
    <div class="mb-2">
        <x-site-workspace-selector :current-site="$site" route-name="menus" />
    </div>

    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-8">
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

    @if (session('status'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('status') }}</div>
    @endif
    @if (session('menu-applied'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('menu-applied') }}</div>
    @endif

    <div class="grid gap-6 lg:grid-cols-[.8fr_1.2fr]">
        <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm">
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
                    <input wire:model="menuName" readonly disabled class="mt-1.5 w-full cursor-not-allowed rounded-xl border border-zinc-200 bg-zinc-100 px-3.5 py-2.5 text-sm text-zinc-500 outline-none" />
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
        </div>

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
                    <div role="link" tabindex="0" onclick="if (!event.target.closest('input,button,a,select')) window.location.href='{{ route('site.manage.pages.edit', $page->id) }}'" class="group flex cursor-pointer flex-col gap-3 rounded-2xl border border-zinc-200 bg-zinc-50/70 p-4 transition hover:border-indigo-300 hover:bg-indigo-50/40 sm:flex-row sm:items-center">
                        <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-white text-zinc-500 shadow-sm">
                            <flux:icon name="{{ match($page->slug) { 'home' => 'home', 'about' => 'user', 'contact' => 'envelope', default => 'document-text' } }}" class="size-5" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-400">{{ $this->pageLabel($page->slug) }}</p>
                            <input wire:model="requiredLabels.{{ $page->slug }}" class="mt-1 w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm font-semibold text-zinc-900" />
                        </div>
                        <a href="{{ route('site.manage.pages.edit', $page->id) }}" class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-indigo-50 px-3 py-2 text-xs font-semibold text-indigo-700 hover:bg-indigo-100">Editar página →</a>
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

    <section class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div><h2 class="text-lg font-semibold text-zinc-950">Todas as páginas do website</h2><p class="mt-1 text-sm text-zinc-500">Seleciona qualquer página para editar textos, secções, imagens e widgets.</p></div>
            <span class="rounded-full bg-zinc-100 px-3 py-1.5 text-xs font-semibold text-zinc-600">{{ $allPages->count() }} páginas</span>
        </div>
        <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($allPages as $sitePageOption)
                <a href="{{ route('site.manage.pages.edit', $sitePageOption->id) }}" class="group rounded-2xl border border-zinc-200 p-4 transition hover:-translate-y-0.5 hover:border-indigo-300 hover:shadow-md">
                    <div class="flex items-start justify-between gap-3">
                        <div><p class="font-semibold text-zinc-900">{{ $sitePageOption->name }}</p><p class="mt-1 text-xs text-zinc-500">/ {{ $sitePageOption->slug }}</p></div>
                        <span class="rounded-full px-2 py-1 text-[10px] font-semibold {{ $sitePageOption->status === 'published' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">{{ $sitePageOption->status === 'published' ? 'Publicada' : 'Rascunho' }}</span>
                    </div>
                    <p class="mt-4 text-sm font-semibold text-indigo-600 group-hover:text-indigo-800">Editar página →</p>
                </a>
            @endforeach
        </div>
    </section>

</div>
</div>