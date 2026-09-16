<div class="flex h-screen min-h-0 flex-col overflow-hidden bg-zinc-100 text-zinc-950 dark:bg-zinc-950 dark:text-white">
    {{-- Top bar: only the actions the user actually needs. --}}
    <header class="shrink-0 border-b border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex h-16 items-center gap-3 px-4 lg:px-6">
            <a href="{{ route('admin.site.dashboard', $site) }}"
               class="inline-flex h-10 items-center rounded-xl border border-zinc-200 px-3 text-sm font-semibold transition hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800">
                ← Sair
            </a>

            <div class="min-w-0">
                <p class="truncate text-sm font-black">{{ $site->name }}</p>
                <p class="text-xs text-zinc-400">Editar website</p>
            </div>

            <div class="ml-auto flex items-center gap-2">
                @if($site->pages->count() > 1)
                    <select wire:model.live="pageId" wire:change="selectPage($event.target.value)"
                            class="hidden max-w-40 rounded-xl border-zinc-200 bg-white text-sm font-semibold sm:block dark:border-zinc-700 dark:bg-zinc-800">
                        @foreach($site->pages->sortBy('sort_order') as $page)
                            <option value="{{ $page->id }}">{{ $page->name }}</option>
                        @endforeach
                    </select>
                @endif

                <a href="{{ route('site.public', ['site' => $site, 'preview' => 1]) }}"
                   target="_blank"
                   class="inline-flex h-10 items-center rounded-xl border border-zinc-200 px-3 text-sm font-semibold transition hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800">
                    Pré-visualizar
                </a>

                <button type="button" wire:click="save"
                        class="inline-flex h-10 items-center rounded-xl border border-zinc-200 bg-white px-4 text-sm font-bold transition hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:hover:bg-zinc-700">
                    Guardar
                </button>

                <button type="button" wire:click="publish"
                        class="inline-flex h-10 items-center rounded-xl bg-zinc-950 px-4 text-sm font-bold text-white transition hover:bg-zinc-800 dark:bg-white dark:text-zinc-950 dark:hover:bg-zinc-200">
                    Publicar
                </button>
            </div>
        </div>
    </header>

    <div class="min-h-0 flex-1 overflow-hidden lg:flex">
        {{-- Website canvas --}}
        <main class="min-h-0 flex-1 overflow-y-auto">
            <div class="mx-auto max-w-6xl px-3 py-4 sm:px-6 lg:px-10 lg:py-8">
                <div class="mb-4 flex items-center justify-between gap-4">
                    <div class="min-w-0">
                        <input wire:model.live.debounce.700ms="pageName"
                               class="w-full max-w-md border-0 bg-transparent p-0 text-xl font-black outline-none ring-0 focus:ring-0"
                               aria-label="Nome da página">
                        <p class="mt-1 text-xs text-zinc-400">/{{ trim($pageSlug, '/') }}</p>
                    </div>

                    <span class="shrink-0 rounded-full px-3 py-1.5 text-xs font-bold {{ $pageStatus === 'published' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                        {{ $pageStatus === 'published' ? 'Publicada' : 'Rascunho' }}
                    </span>
                </div>

                <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900"
                     style="max-width: {{ $theme['content_width'] ?? '1200px' }}; margin-inline: auto;">
                    @forelse($sections as $index => $section)
                        @php
                            $content = $section['content'] ?? [];
                            $items = $content['items'] ?? [];
                            $type = $section['type'];
                            $isSelected = $selectedSection === $index;
                        @endphp

                        <section wire:key="simple-builder-section-{{ $section['id'] ?? 'new-'.$index }}"
                                 wire:click="selectSection({{ $index }})"
                                 class="group relative border-b border-zinc-100 transition last:border-b-0 dark:border-zinc-800 {{ $section['is_visible'] ? '' : 'opacity-40' }} {{ $isSelected ? 'ring-2 ring-inset ring-zinc-900 dark:ring-white' : 'hover:ring-1 hover:ring-zinc-300 dark:hover:ring-zinc-600' }}">

                            <div class="absolute right-4 top-4 z-10 opacity-0 transition group-hover:opacity-100 {{ $isSelected ? '!opacity-100' : '' }}">
                                <span class="rounded-full bg-white px-3 py-1.5 text-[11px] font-bold text-zinc-600 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 dark:text-zinc-300 dark:ring-zinc-700">
                                    Clique para editar
                                </span>
                            </div>

                            <div class="px-6 py-16 sm:px-10 lg:px-16">
                                @if($type === 'hero')
                                    <div class="mx-auto max-w-3xl text-center">
                                        <p class="mb-4 text-xs font-black uppercase tracking-[0.2em]" style="color: {{ $theme['primary'] ?? '#635bff' }}">
                                            {{ $content['subtitle'] ?? 'A tua marca' }}
                                        </p>
                                        <h1 class="text-4xl font-black tracking-tight sm:text-6xl">
                                            {{ $content['title'] ?? 'Título principal' }}
                                        </h1>
                                        <p class="mx-auto mt-5 max-w-2xl text-base leading-7 text-zinc-500">
                                            {{ $content['description'] ?? $content['body'] ?? 'Apresenta aqui o teu negócio e o que tens para oferecer.' }}
                                        </p>
                                        @if(!empty($content['button_label']))
                                            <span class="mt-7 inline-flex rounded-xl px-5 py-3 text-sm font-black text-white" style="background: {{ $theme['primary'] ?? '#635bff' }}">
                                                {{ $content['button_label'] }}
                                            </span>
                                        @endif
                                    </div>

                                @elseif($type === 'text')
                                    <div class="mx-auto max-w-3xl">
                                        <h2 class="text-3xl font-black">{{ $content['title'] ?? 'Título' }}</h2>
                                        <p class="mt-4 whitespace-pre-line text-base leading-7 text-zinc-500">
                                            {{ $content['body'] ?? $content['description'] ?? 'Adiciona aqui a descrição.' }}
                                        </p>
                                    </div>

                                @elseif($type === 'image')
                                    <div class="mx-auto max-w-4xl text-center">
                                        @if(!empty($content['url']))
                                            <img src="{{ $content['url'] }}" alt="{{ $content['alt'] ?? '' }}" class="mx-auto max-h-[480px] rounded-2xl object-cover">
                                        @else
                                            <div class="rounded-2xl border-2 border-dashed border-zinc-200 px-6 py-20 text-sm text-zinc-400 dark:border-zinc-700">
                                                Adiciona uma imagem no painel de edição.
                                            </div>
                                        @endif
                                    </div>

                                @elseif($type === 'product_grid')
                                    <div class="mx-auto max-w-5xl">
                                        <div class="mb-8">
                                            <h2 class="text-3xl font-black">{{ $content['title'] ?? 'Os nossos produtos' }}</h2>
                                            <p class="mt-2 text-zinc-500">{{ $content['description'] ?? 'Escolhe os produtos que queres apresentar aos teus clientes.' }}</p>
                                        </div>

                                        @if(count($items))
                                            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                                                @foreach($items as $item)
                                                    <article class="overflow-hidden rounded-2xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                                                        @if(!empty($item['image_url']))
                                                            <img src="{{ $item['image_url'] }}" alt="{{ $item['name'] ?? '' }}" class="h-48 w-full object-cover">
                                                        @endif
                                                        <div class="p-5">
                                                            <h3 class="font-black">{{ $item['name'] ?? $item['title'] ?? 'Produto' }}</h3>
                                                            @if(!empty($item['description']))
                                                                <p class="mt-2 text-sm leading-6 text-zinc-500">{{ $item['description'] }}</p>
                                                            @endif
                                                            @if(isset($item['price']))
                                                                <p class="mt-4 text-lg font-black">€ {{ number_format((float) $item['price'], 2, ',', '.') }}</p>
                                                            @endif
                                                        </div>
                                                    </article>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="rounded-2xl border-2 border-dashed border-zinc-200 px-6 py-14 text-center dark:border-zinc-700">
                                                <p class="font-bold">Ainda não tens produtos para mostrar.</p>
                                                <p class="mt-1 text-sm text-zinc-500">Adiciona os produtos na área de Produtos do teu website.</p>
                                            </div>
                                        @endif
                                    </div>

                                @elseif($type === 'cta')
                                    <div class="rounded-3xl p-8 text-center sm:p-14" style="background: {{ $theme['background'] ?? '#f4f4f5' }}">
                                        <h2 class="text-3xl font-black">{{ $content['title'] ?? 'Fala connosco' }}</h2>
                                        <p class="mx-auto mt-3 max-w-2xl text-zinc-500">{{ $content['description'] ?? 'Estamos disponíveis para ajudar.' }}</p>
                                        <span class="mt-6 inline-flex rounded-xl px-5 py-3 text-sm font-black text-white" style="background: {{ $theme['primary'] ?? '#635bff' }}">
                                            {{ $content['button_label'] ?? 'Contactar' }}
                                        </span>
                                    </div>

                                @else
                                    <div class="mx-auto max-w-4xl">
                                        <h2 class="text-3xl font-black">{{ $content['title'] ?? \Illuminate\Support\Str::headline($type) }}</h2>
                                        @if(!empty($content['description']))
                                            <p class="mt-3 text-zinc-500">{{ $content['description'] }}</p>
                                        @endif

                                        @if(count($items))
                                            <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                                @foreach($items as $item)
                                                    <article class="rounded-2xl border border-zinc-200 p-5 dark:border-zinc-700">
                                                        <p class="font-black">{{ $item['title'] ?? $item['name'] ?? $item['question'] ?? $item['label'] ?? 'Item' }}</p>
                                                        <p class="mt-2 text-sm leading-6 text-zinc-500">{{ $item['description'] ?? $item['quote'] ?? $item['answer'] ?? $item['excerpt'] ?? $item['price'] ?? '' }}</p>
                                                    </article>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </section>
                    @empty
                        <div class="flex min-h-[650px] items-center justify-center px-8 text-center">
                            <div class="max-w-md">
                                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-zinc-100 text-2xl dark:bg-zinc-800">✦</div>
                                <h2 class="mt-5 text-xl font-black">O teu website está pronto para editar</h2>
                                <p class="mt-2 text-sm leading-6 text-zinc-500">O modelo escolhido ainda não tem conteúdo. Volta atrás e escolhe um modelo para começar.</p>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </main>

        {{-- Simple editor: text, description and products only. --}}
        <aside class="w-full shrink-0 overflow-y-auto border-t border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900 lg:w-[360px] lg:border-l lg:border-t-0">
            <div class="border-b border-zinc-200 px-5 py-5 dark:border-zinc-800">
                <p class="text-xs font-black uppercase tracking-wider text-zinc-400">Editar</p>
                @if($selectedSection !== null && isset($sections[$selectedSection]))
                    <h2 class="mt-1 text-lg font-black">{{ $sections[$selectedSection]['label'] ?? 'Secção' }}</h2>
                    <p class="mt-1 text-sm text-zinc-500">Altera apenas o conteúdo. O design fica protegido.</p>
                @else
                    <h2 class="mt-1 text-lg font-black">Escolhe uma secção</h2>
                    <p class="mt-1 text-sm text-zinc-500">Clica numa parte do website para editar o texto.</p>
                @endif
            </div>

            @if($selectedSection !== null && isset($sections[$selectedSection]))
                @php
                    $selectedContent = $sections[$selectedSection]['content'] ?? [];
                    $selectedItems = $selectedContent['items'] ?? [];
                    $selectedType = $sections[$selectedSection]['type'];
                @endphp

                <div class="space-y-5 p-5">
                    @foreach($selectedContent as $fieldKey => $fieldValue)
                        @continue($fieldKey === 'items')
                        @if(is_scalar($fieldValue) || $fieldValue === null)
                            @php
                                $fieldLabel = match($fieldKey) {
                                    'title' => 'Título',
                                    'subtitle' => 'Subtítulo',
                                    'description', 'body' => 'Descrição',
                                    'button_label' => 'Texto do botão',
                                    'button_url' => 'Link do botão',
                                    'alt' => 'Texto da imagem',
                                    'url' => 'Imagem / URL',
                                    default => \Illuminate\Support\Str::headline(str_replace('_', ' ', $fieldKey)),
                                };
                            @endphp

                            @if(in_array($fieldKey, ['title', 'subtitle', 'description', 'body', 'button_label'], true))
                                <div>
                                    <label class="text-sm font-bold">{{ $fieldLabel }}</label>
                                    @if(in_array($fieldKey, ['description', 'body'], true))
                                        <textarea wire:model.live.debounce.700ms="sections.{{ $selectedSection }}.content.{{ $fieldKey }}"
                                                  rows="4"
                                                  class="mt-2 w-full resize-none rounded-xl border-zinc-200 bg-zinc-50 px-3 py-3 text-sm leading-6 focus:border-zinc-400 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800"></textarea>
                                    @else
                                        <input wire:model.live.debounce.700ms="sections.{{ $selectedSection }}.content.{{ $fieldKey }}"
                                               class="mt-2 w-full rounded-xl border-zinc-200 bg-zinc-50 px-3 py-3 text-sm focus:border-zinc-400 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-800">
                                    @endif
                                </div>
                            @endif
                        @endif
                    @endforeach

                    @if($selectedType === 'product_grid')
                        <div class="border-t border-zinc-200 pt-5 dark:border-zinc-700">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="font-black">Produtos</h3>
                                    <p class="mt-1 text-xs leading-5 text-zinc-500">Os produtos activos da tua loja aparecem automaticamente nesta secção.</p>
                                </div>
                                <a href="{{ route('admin.site.products', $site) }}"
                                   class="shrink-0 rounded-xl border border-zinc-200 px-3 py-2 text-xs font-bold hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800">
                                    Gerir produtos
                                </a>
                            </div>

                            <div class="mt-4 space-y-2">
                                @forelse($selectedItems as $item)
                                    <div class="rounded-xl border border-zinc-200 p-3 dark:border-zinc-700">
                                        <p class="text-sm font-bold">{{ $item['name'] ?? $item['title'] ?? 'Produto' }}</p>
                                        @if(!empty($item['description']))
                                            <p class="mt-1 line-clamp-2 text-xs text-zinc-500">{{ $item['description'] }}</p>
                                        @endif
                                        @if(isset($item['price']))
                                            <p class="mt-2 text-xs font-black">€ {{ number_format((float) $item['price'], 2, ',', '.') }}</p>
                                        @endif
                                    </div>
                                @empty
                                    <p class="rounded-xl bg-zinc-50 px-3 py-4 text-center text-xs text-zinc-500 dark:bg-zinc-800">Ainda não tens produtos activos.</p>
                                @endforelse
                            </div>
                        </div>
                    @endif

                    <div class="border-t border-zinc-200 pt-5 dark:border-zinc-700">
                        <button type="button" wire:click="save"
                                class="w-full rounded-xl bg-zinc-950 px-4 py-3 text-sm font-black text-white transition hover:bg-zinc-800 dark:bg-white dark:text-zinc-950 dark:hover:bg-zinc-200">
                            Guardar alterações
                        </button>
                    </div>
                </div>
            @else
                <div class="p-5">
                    <button type="button" wire:click="addSection('product_grid')"
                            class="w-full rounded-xl border border-dashed border-zinc-300 px-4 py-4 text-left transition hover:border-zinc-500 hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800">
                        <span class="block text-sm font-black">+ Adicionar produtos</span>
                        <span class="mt-1 block text-xs text-zinc-500">Mostra os produtos da tua loja no website.</span>
                    </button>
                </div>
            @endif
        </aside>
    </div>
</div>
