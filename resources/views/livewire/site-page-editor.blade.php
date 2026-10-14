<div class="min-h-screen bg-zinc-50 p-5 sm:p-8">
    <div class="mx-auto max-w-7xl space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('site.manage.menus') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-zinc-600 hover:text-zinc-950">← Voltar às páginas</a>
            <a href="{{ route('site.public', [$site, 'pageSlug' => $page->slug, 'preview' => 1]) }}" target="_blank" class="rounded-xl border border-zinc-200 bg-white px-4 py-2.5 text-sm font-semibold">Pré-visualizar página ↗</a>
        </div>

        <header class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm sm:p-8">
            <p class="text-xs font-bold uppercase tracking-[.18em] text-indigo-600">Editor de páginas</p>
            <h1 class="mt-2 text-3xl font-black tracking-tight text-zinc-950">{{ $page->name }}</h1>
            <p class="mt-2 text-sm text-zinc-500">Edita o conteúdo e adiciona secções e widgets. As alterações ficam limitadas a esta página.</p>
        </header>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('status') }}</div>
        @endif

        <section class="overflow-hidden rounded-3xl border border-zinc-200 bg-white shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-zinc-200 px-5 py-4">
                <div>
                    <h2 class="font-bold text-zinc-900">Pré-visualização real do website</h2>
                    <p class="mt-1 text-xs text-zinc-500">Usa o mesmo tema, tipografia, cores, navegação e separadores da página pública.</p>
                </div>
                <a href="{{ route('site.public', [$site, 'pageSlug' => $page->slug, 'preview' => 1]) }}" target="_blank" class="rounded-xl border border-zinc-200 px-3 py-2 text-sm font-semibold hover:bg-zinc-50">Abrir em novo separador ↗</a>
            </div>
            <div class="bg-zinc-100 p-2 sm:p-4">
                <div class="mx-auto overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-lg">
                    <div class="flex h-8 items-center gap-1.5 border-b border-zinc-200 bg-zinc-50 px-3">
                        <span class="size-2.5 rounded-full bg-red-400"></span><span class="size-2.5 rounded-full bg-amber-400"></span><span class="size-2.5 rounded-full bg-emerald-400"></span>
                        <span class="ml-2 truncate rounded-md bg-white px-3 py-1 text-[10px] text-zinc-500">{{ $site->domain ?: $site->slug.'.finder.test' }}</span>
                    </div>
                    <iframe title="Pré-visualização da página do website" src="{{ route('site.public', [$site, 'pageSlug' => $page->slug, 'preview' => 1, 'editor_refresh' => $previewVersion]) }}" class="block h-[680px] w-full bg-white sm:h-[780px]" loading="eager"></iframe>
                </div>
            </div>
        </section>

        <form wire:submit="save" class="space-y-6">
            <section class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold">Definições da página</h2>
                <div class="mt-5 grid gap-5 md:grid-cols-3">
                    <label class="grid gap-2 text-sm font-semibold">Nome da página
                        <input wire:model="name" class="rounded-xl border border-zinc-200 px-3.5 py-3 font-normal">
                        @error('name')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                    </label>
                    <label class="grid gap-2 text-sm font-semibold">Endereço (slug)
                        <input wire:model="slug" class="rounded-xl border border-zinc-200 px-3.5 py-3 font-normal">
                        @error('slug')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                    </label>
                    <label class="grid gap-2 text-sm font-semibold">Estado
                        <select wire:model="status" class="rounded-xl border border-zinc-200 bg-white px-3.5 py-3 font-normal">
                            <option value="draft">Rascunho</option>
                            <option value="published">Publicada</option>
                        </select>
                    </label>
                </div>
            </section>

            <section class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div><h2 class="text-lg font-bold">Conteúdo e widgets</h2><p class="mt-1 text-sm text-zinc-500">Adiciona secções, altera textos e define botões e imagens.</p></div>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" wire:click="addSection('text')" class="rounded-xl border border-zinc-200 px-3 py-2 text-sm font-semibold hover:bg-zinc-50">+ Texto</button>
                        <button type="button" wire:click="addSection('features')" class="rounded-xl border border-zinc-200 px-3 py-2 text-sm font-semibold hover:bg-zinc-50">+ Funcionalidades</button>
                        <button type="button" wire:click="addSection('image')" class="rounded-xl border border-zinc-200 px-3 py-2 text-sm font-semibold hover:bg-zinc-50">+ Imagem</button>
                        <button type="button" wire:click="addSection('gallery')" class="rounded-xl border border-zinc-200 px-3 py-2 text-sm font-semibold hover:bg-zinc-50">+ Galeria</button>
                        <button type="button" wire:click="addSection('testimonials')" class="rounded-xl border border-zinc-200 px-3 py-2 text-sm font-semibold hover:bg-zinc-50">+ Testemunhos</button>
                        <button type="button" wire:click="addSection('faq')" class="rounded-xl border border-zinc-200 px-3 py-2 text-sm font-semibold hover:bg-zinc-50">+ FAQ</button>
                        <button type="button" wire:click="addSection('cta')" class="rounded-xl border border-zinc-200 px-3 py-2 text-sm font-semibold hover:bg-zinc-50">+ Botão</button>
                        <button type="button" wire:click="addSection('widget')" class="rounded-xl border border-zinc-200 px-3 py-2 text-sm font-semibold hover:bg-zinc-50">+ Widget</button>
                    </div>
                </div>

                <div class="mt-6 space-y-5">
                    @forelse($sections as $index => $section)
                        <article wire:key="page-section-{{ $section['id'] ?? 'new-'.$index }}" class="rounded-2xl border border-zinc-200 bg-zinc-50/70 p-5">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div class="flex items-center gap-3"><span class="flex size-9 items-center justify-center rounded-xl bg-indigo-100 font-bold text-indigo-700">{{ $index + 1 }}</span><div><p class="font-bold">{{ $pageTypes[$section['type']] ?? 'Secção' }}</p><p class="text-xs text-zinc-500">Os campos vazios não aparecem no site.</p></div></div>
                                <div class="flex items-center gap-3"><label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model="sections.{{ $index }}.is_visible" class="rounded border-zinc-300"> Visível</label><button type="button" wire:click="removeSection({{ $index }})" wire:confirm="Queres remover esta secção?" class="rounded-lg px-3 py-2 text-sm font-semibold text-red-600 hover:bg-red-50">Remover</button></div>
                            </div>
                            <div class="mt-5 grid gap-4 md:grid-cols-2">
                                <label class="grid gap-2 text-sm font-semibold">Nome interno
                                    <input wire:model="sections.{{ $index }}.label" class="rounded-xl border border-zinc-200 bg-white px-3 py-2.5 font-normal">
                                </label>
                                <label class="grid gap-2 text-sm font-semibold">Tipo de secção
                                    <select wire:model="sections.{{ $index }}.type" class="rounded-xl border border-zinc-200 bg-white px-3 py-2.5 font-normal">
                                        @foreach($pageTypes as $type => $label)<option value="{{ $type }}">{{ $label }}</option>@endforeach
                                    </select>
                                </label>
                                <label class="grid gap-2 text-sm font-semibold md:col-span-2">Título
                                    <input wire:model="sections.{{ $index }}.title" class="rounded-xl border border-zinc-200 bg-white px-3 py-2.5 font-normal" placeholder="Título apresentado na página">
                                </label>
                                <label class="grid gap-2 text-sm font-semibold md:col-span-2">Texto / descrição
                                    <textarea wire:model="sections.{{ $index }}.body" rows="4" class="rounded-xl border border-zinc-200 bg-white px-3 py-2.5 font-normal" placeholder="Escreve o texto desta secção..."></textarea>
                                </label>
                                <label class="grid gap-2 text-sm font-semibold">URL da imagem
                                    <input wire:model="sections.{{ $index }}.image_url" type="url" class="rounded-xl border border-zinc-200 bg-white px-3 py-2.5 font-normal" placeholder="https://...">
                                    @error('sections.'.$index.'.image_url')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                                </label>
                                <label class="grid gap-2 text-sm font-semibold">Itens (um por linha)
                                    <textarea wire:model="sections.{{ $index }}.items_text" rows="3" class="rounded-xl border border-zinc-200 bg-white px-3 py-2.5 font-normal" placeholder="Item 1&#10;Item 2&#10;Item 3"></textarea>
                                </label>
                                <label class="grid gap-2 text-sm font-semibold">Texto do botão
                                    <input wire:model="sections.{{ $index }}.button_label" class="rounded-xl border border-zinc-200 bg-white px-3 py-2.5 font-normal" placeholder="Saber mais">
                                </label>
                                <label class="grid gap-2 text-sm font-semibold">Link do botão
                                    <input wire:model="sections.{{ $index }}.button_url" class="rounded-xl border border-zinc-200 bg-white px-3 py-2.5 font-normal" placeholder="/contact ou https://...">
                                </label>
                                <div class="md:col-span-2 rounded-2xl border border-zinc-200 bg-white p-4">
                                    <p class="mb-3 text-sm font-bold">Cores desta secção</p>
                                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                        <label class="grid gap-2 text-xs font-semibold text-zinc-600">Fundo
                                            <span class="flex items-center gap-2"><input type="color" wire:model.live="sections.{{ $index }}.background_color" class="h-10 w-12 cursor-pointer rounded-lg border border-zinc-200 p-1"><input wire:model.live="sections.{{ $index }}.background_color" class="min-w-0 w-full rounded-lg border border-zinc-200 px-2 py-2 font-mono text-xs"></span>
                                        </label>
                                        <label class="grid gap-2 text-xs font-semibold text-zinc-600">Texto
                                            <span class="flex items-center gap-2"><input type="color" wire:model.live="sections.{{ $index }}.text_color" class="h-10 w-12 cursor-pointer rounded-lg border border-zinc-200 p-1"><input wire:model.live="sections.{{ $index }}.text_color" class="min-w-0 w-full rounded-lg border border-zinc-200 px-2 py-2 font-mono text-xs"></span>
                                        </label>
                                        <label class="grid gap-2 text-xs font-semibold text-zinc-600">Destaques / botões
                                            <span class="flex items-center gap-2"><input type="color" wire:model.live="sections.{{ $index }}.accent_color" class="h-10 w-12 cursor-pointer rounded-lg border border-zinc-200 p-1"><input wire:model.live="sections.{{ $index }}.accent_color" class="min-w-0 w-full rounded-lg border border-zinc-200 px-2 py-2 font-mono text-xs"></span>
                                        </label>
                                        <label class="grid gap-2 text-xs font-semibold text-zinc-600">Separadores / contorno
                                            <span class="flex items-center gap-2"><input type="color" wire:model.live="sections.{{ $index }}.border_color" class="h-10 w-12 cursor-pointer rounded-lg border border-zinc-200 p-1"><input wire:model.live="sections.{{ $index }}.border_color" class="min-w-0 w-full rounded-lg border border-zinc-200 px-2 py-2 font-mono text-xs"></span>
                                        </label>
                                    </div>
                                    <p class="mt-2 text-xs text-zinc-500">As cores são aplicadas à secção no website depois de guardares.</p>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-2xl border border-dashed border-zinc-300 px-5 py-12 text-center"><p class="font-semibold text-zinc-700">Ainda não existem secções personalizadas.</p><p class="mt-1 text-sm text-zinc-500">Usa os botões acima para adicionar texto, imagens, funcionalidades ou widgets.</p></div>
                    @endforelse
                </div>
            </section>

            <div class="flex flex-wrap justify-end gap-3"><a href="{{ route('site.manage.menus') }}" class="rounded-xl border border-zinc-200 bg-white px-5 py-3 text-sm font-semibold">Cancelar</a><button type="submit" wire:loading.attr="disabled" class="rounded-xl bg-indigo-600 px-6 py-3 text-sm font-bold text-white hover:bg-indigo-700 disabled:opacity-50">Guardar alterações</button></div>
        </form>
    </div>
</div>