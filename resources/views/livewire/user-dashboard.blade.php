<div class="min-h-screen bg-zinc-50 dark:bg-zinc-950">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400">Finder</p>
                <h1 class="mt-1 text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white">
                    Bom dia, {{ auth()->user()->name }}.
                </h1>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    Cria, personaliza e gere todos os teus websites num só lugar.
                </p>
            </div>
            <flux:button variant="primary" icon="plus" href="{{ route('site.create') }}" wire:navigate>
                Criar website
            </flux:button>
        </div>

        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach([
                ['label' => 'Websites', 'value' => $sites->count(), 'icon' => 'globe-alt'],
                ['label' => 'Publicados', 'value' => $publishedCount, 'icon' => 'rocket-launch'],
                ['label' => 'Rascunhos', 'value' => $draftCount, 'icon' => 'pencil-square'],
                ['label' => 'Produtos', 'value' => $totalProducts, 'icon' => 'shopping-bag'],
            ] as $stat)
                <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ $stat['label'] }}</span>
                        <flux:icon :name="$stat['icon']" class="size-5 text-zinc-400" />
                    </div>
                    <p class="mt-3 text-2xl font-semibold text-zinc-950 dark:text-white">{{ number_format($stat['value']) }}</p>
                </div>
            @endforeach
        </div>

        <div class="mt-10 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Os teus websites</h2>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Continua de onde ficaste ou cria um novo projeto.</p>
            </div>
        </div>

        @if($sites->isEmpty())
            <div class="mt-5 rounded-2xl border border-dashed border-zinc-300 bg-white p-12 text-center dark:border-zinc-700 dark:bg-zinc-900">
                <div class="mx-auto flex size-12 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                    <flux:icon name="globe-alt" class="size-6" />
                </div>
                <h3 class="mt-4 text-base font-semibold text-zinc-950 dark:text-white">Ainda não tens nenhum website</h3>
                <p class="mx-auto mt-2 max-w-md text-sm text-zinc-500 dark:text-zinc-400">Escolhe um tipo e um template. O Finder cria a estrutura inicial para começares a editar.</p>
                <flux:button class="mt-6" variant="primary" href="{{ route('site.create') }}" wire:navigate>
                    Criar o meu primeiro website
                </flux:button>
            </div>
        @else
            <div class="mt-5 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                @foreach($sites as $site)
                    <article class="group overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="relative h-40 bg-gradient-to-br from-indigo-500 via-violet-500 to-fuchsia-500 p-5">
                            <div class="absolute inset-0 bg-black/10"></div>
                            <div class="relative flex items-start justify-between">
                                <div class="rounded-xl bg-white/15 px-3 py-2 text-white backdrop-blur">
                                    <p class="text-xs font-medium">{{ ucfirst(str_replace('_', ' ', $site->type)) }}</p>
                                    <p class="mt-0.5 text-lg font-semibold">{{ $site->name }}</p>
                                </div>
                                <span class="rounded-full bg-white/90 px-2.5 py-1 text-xs font-medium text-zinc-700">
                                    {{ $site->is_published ? 'Publicado' : 'Rascunho' }}
                                </span>
                            </div>
                        </div>
                        <div class="p-5">
                            <p class="line-clamp-2 min-h-10 text-sm text-zinc-500 dark:text-zinc-400">{{ $site->description ?: 'Personaliza o teu website, adiciona páginas e publica quando estiveres pronto.' }}</p>
                            <div class="mt-4 flex gap-4 text-xs text-zinc-500 dark:text-zinc-400">
                                <span>{{ $site->pages_count }} páginas</span>
                                <span>{{ $site->products_count }} produtos</span>
                            </div>
                            <div class="mt-5 flex gap-2">
                                <flux:button class="flex-1" href="{{ route('admin.site.dashboard', $site) }}" wire:navigate>
                                    Gerir
                                </flux:button>
                                <flux:button variant="primary" href="{{ route('builder.edit', $site) }}" wire:navigate>
                                    Editar
                                </flux:button>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</div>
