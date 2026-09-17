<div class="min-h-screen bg-zinc-50 dark:bg-zinc-950">
    <style>
        @keyframes dash-fade-up {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .dash-fade-up { animation: dash-fade-up .45s ease-out both; }
        @media (prefers-reduced-motion: reduce) { .dash-fade-up { animation: none; } }
    </style>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="relative overflow-hidden rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm sm:p-8 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="pointer-events-none absolute -right-24 -top-24 size-72 rounded-full bg-indigo-500/10 blur-3xl dark:bg-indigo-500/10" aria-hidden="true"></div>
            <div class="pointer-events-none absolute -bottom-28 left-1/3 size-64 rounded-full bg-fuchsia-500/5 blur-3xl dark:bg-fuchsia-500/10"></div>
            <div class="relative flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="inline-flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-indigo-600 dark:text-indigo-400"><span class="size-1.5 rounded-full bg-indigo-500"></span>Finder</p>
                    <h1 class="mt-2 text-3xl font-bold tracking-tight text-zinc-950 sm:text-4xl dark:text-white">Bom dia, {{ auth()->user()->name }}.</h1>
                    <p class="mt-2 max-w-md text-sm leading-6 text-zinc-500 dark:text-zinc-400">Cria, personaliza e gere todos os teus websites num só lugar.</p>
                </div>
                <flux:button variant="primary" icon="plus" href="{{ route('site.create') }}" wire:navigate class="motion-safe:transition motion-safe:hover:-translate-y-0.5 shadow-lg shadow-indigo-500/20">Criar website</flux:button>
            </div>
        </div>

        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @php
                $stats = [
                    ['label' => 'Websites', 'value' => $allSitesCount, 'icon' => 'globe-alt', 'accent' => 'text-indigo-600 bg-indigo-50 dark:text-indigo-400 dark:bg-indigo-500/10', 'filter' => 'all'],
                    ['label' => 'Publicados', 'value' => $publishedCount, 'icon' => 'rocket-launch', 'accent' => 'text-emerald-600 bg-emerald-50 dark:text-emerald-400 dark:bg-emerald-500/10', 'filter' => 'published'],
                    ['label' => 'Rascunhos', 'value' => $draftCount, 'icon' => 'pencil-square', 'accent' => 'text-amber-600 bg-amber-50 dark:text-amber-400 dark:bg-amber-500/10', 'filter' => 'drafts'],
                    ['label' => 'Produtos', 'value' => $totalProducts, 'icon' => 'shopping-bag', 'accent' => 'text-fuchsia-600 bg-fuchsia-50 dark:text-fuchsia-400 dark:bg-fuchsia-500/10', 'filter' => 'products'],
                ];
            @endphp
            @foreach ($stats as $index => $stat)
                <button type="button" wire:click="setFilter('{{ $stat['filter'] }}')" class="dash-fade-up group relative overflow-hidden rounded-2xl border bg-white p-5 text-left shadow-sm outline-none transition duration-200 hover:-translate-y-1 hover:border-zinc-300 hover:shadow-lg focus-visible:ring-2 focus-visible:ring-indigo-500 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700 {{ $filter === $stat['filter'] ? 'ring-2 ring-indigo-500/30 border-indigo-300 dark:border-indigo-700' : 'border-zinc-200' }}" style="animation-delay: {{ $index * 60 }}ms">
                    <div class="pointer-events-none absolute -right-6 -top-6 size-20 rounded-full bg-zinc-50 transition duration-300 group-hover:scale-125 dark:bg-zinc-800/60" aria-hidden="true"></div>
                    <div class="relative flex items-center justify-between">
                        <span class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ $stat['label'] }}</span>
                        <span class="flex size-9 items-center justify-center rounded-xl {{ $stat['accent'] }}"><flux:icon :name="$stat['icon']" class="size-4.5" /></span>
                    </div>
                    <p class="relative mt-4 text-3xl font-bold tracking-tight text-zinc-950 dark:text-white">{{ number_format($stat['value']) }}</p>
                    <span class="relative mt-2 inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 dark:text-indigo-400">Ver {{ strtolower($stat['label']) }} <span aria-hidden="true">→</span></span>
                </button>
            @endforeach
        </div>

        <div id="websites" class="mt-10 flex scroll-mt-6 flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Os teus websites</h2>
                    <span class="text-sm font-medium text-zinc-400 dark:text-zinc-500">({{ $allSitesCount }})</span>
                </div>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                    @switch($filter)
                        @case('published') Apenas websites publicados. @break
                        @case('drafts') Apenas websites em rascunho. @break
                        @case('products') Websites que têm produtos. @break
                        @default Continua de onde ficaste ou cria um novo projeto.
                    @endswitch
                </p>
            </div>
            <div class="flex items-center gap-2">
                @if($filter !== 'all')
                    <flux:button variant="ghost" wire:click="clearFilter">Ver todos</flux:button>
                @endif
                <flux:button variant="ghost" icon="plus" href="{{ route('site.create') }}" wire:navigate class="hidden sm:inline-flex">Novo website</flux:button>
            </div>
        </div>

        @if ($sites->isEmpty())
            <div class="relative mt-5 overflow-hidden rounded-3xl border border-dashed border-zinc-300 bg-white p-10 text-center sm:p-16 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="relative mx-auto flex max-w-md flex-col items-center">
                    <span class="flex size-16 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 shadow-sm dark:bg-indigo-500/10 dark:text-indigo-400"><flux:icon name="globe-alt" class="size-7" /></span>
                    <h3 class="mt-6 text-lg font-semibold text-zinc-950 dark:text-white">Não existem resultados para este filtro</h3>
                    <p class="mt-2 text-sm leading-6 text-zinc-500 dark:text-zinc-400">Experimenta outro widget ou volta a ver todos os websites.</p>
                    <flux:button class="mt-7" variant="primary" wire:click="clearFilter">Ver todos os websites</flux:button>
                </div>
            </div>
        @else
            <div class="mt-5 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($sites as $site)
                    <article class="dash-fade-up group flex flex-col overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm transition duration-200 hover:-translate-y-1 hover:shadow-lg hover:shadow-zinc-900/5 dark:border-zinc-800 dark:bg-zinc-900" style="animation-delay: {{ $loop->index * 60 }}ms">
                        <div class="relative h-40 overflow-hidden bg-gradient-to-br from-indigo-500 via-violet-500 to-fuchsia-500">
                            <div class="absolute inset-0 bg-black/10"></div>
                            <div class="absolute inset-0 opacity-25 transition duration-300 group-hover:scale-105 group-hover:opacity-30" style="background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,.6) 1px, transparent 0); background-size: 18px 18px  ;" aria-hidden="true"></div>
                            <div class="relative flex h-full flex-col justify-between p-5">
                                <div class="flex items-start justify-between gap-3">
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-white/90 px-2.5 py-1 text-xs font-semibold text-zinc-700 shadow-sm dark:bg-zinc-950/80 dark:text-zinc-200"><span class="size-1.5 rounded-full {{ $site->is_published ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>{{ $site->is_published ? 'Publicado' : 'Rascunho' }}</span>
                                </div>
                                <div class="rounded-xl bg-white/15 px-3 py-2.5 text-white backdrop-blur"><p class="text-xs font-medium uppercase tracking-wide text-white/80">{{ ucfirst(str_replace('_', ' ', $site->type)) }}</p><p class="mt-0.5 truncate text-lg font-semibold">{{ $site->name }}</p></div>
                            </div>
                        </div>
                        <div class="flex flex-1 flex-col p-5">
                            <p class="line-clamp-2 min-h-10 text-sm leading-6 text-zinc-500 dark:text-zinc-400">{{ $site->description ?: 'Personaliza o teu website, adiciona páginas e publica quando estiveres pronto.' }}</p>
                            <div class="mt-4 flex gap-4 text-xs font-medium text-zinc-500 dark:text-zinc-400"><span class="inline-flex items-center gap-1.5"><flux:icon name="document" class="size-3.5 text-zinc-400 dark:text-zinc-500" />{{ $site->pages_count }} páginas</span><span class="inline-flex items-center gap-1.5"><flux:icon name="shopping-bag" class="size-3.5 text-zinc-400 dark:text-zinc-500" />{{ $site->products_count }} produtos</span></div>
                            <div class="mt-5 flex gap-2 border-t border-zinc-100 pt-4 dark:border-zinc-800">
                                <flux:button class="flex-1" variant="ghost" href="{{ route('admin.site.dashboard', $site) }}">Gerir</flux:button>
                                <flux:button class="flex-1 transition hover:-translate-y-0.5" variant="primary" icon="eye" href="{{ route('site.public', ['site' => $site, 'preview' => 1]) }}" target="_blank">Ver site</flux:button>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</div>