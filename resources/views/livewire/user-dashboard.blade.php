<div class="min-h-screen bg-zinc-50 dark:bg-zinc-950">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @php
                $stats = [
                    ['label' => 'Websites', 'value' => $allSitesCount, 'icon' => 'globe-alt', 'filter' => 'all'],
                    ['label' => 'Publicados', 'value' => $publishedCount, 'icon' => 'rocket-launch', 'filter' => 'published'],
                    ['label' => 'Rascunhos', 'value' => $draftCount, 'icon' => 'pencil-square', 'filter' => 'drafts'],
                    ['label' => 'Produtos', 'value' => $totalProducts, 'icon' => 'shopping-bag', 'filter' => 'products'],
                ];
            @endphp
            @foreach ($stats as $stat)
                <button type="button" wire:click="setFilter('{{ $stat['filter'] }}')" class="rounded-2xl border border-zinc-200 bg-white p-5 text-left shadow-sm transition hover:-translate-y-1 hover:shadow-lg dark:border-zinc-800 dark:bg-zinc-900 {{ $filter === $stat['filter'] ? 'ring-2 ring-indigo-500/30' : '' }}">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ $stat['label'] }}</span>
                        <span class="flex size-9 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400"><flux:icon :name="$stat['icon']" class="size-4.5" /></span>
                    </div>
                    <p class="mt-4 text-3xl font-bold text-zinc-950 dark:text-white">{{ number_format($stat['value']) }}</p>
                    <span class="mt-2 inline-flex text-xs font-semibold text-indigo-600 dark:text-indigo-400">Ver {{ strtolower($stat['label']) }} →</span>
                </button>
            @endforeach
        </div>

        <div class="mt-10 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <div class="flex items-center gap-2"><h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Os teus websites</h2><span class="text-sm text-zinc-400">({{ $allSitesCount }})</span></div>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                    @switch($filter)
                        @case('published') Apenas websites publicados. @break
                        @case('drafts') Apenas websites em rascunho. @break
                        @case('products') Websites que têm produtos. @break
                        @default Continua de onde ficaste ou cria um novo projeto.
                    @endswitch
                </p>
            </div>
            <div class="flex gap-2">
                @if($filter !== 'all') <flux:button variant="ghost" wire:click="clearFilter">Ver todos</flux:button> @endif

            </div>
        </div>

        @if ($sites->isEmpty())
            <div class="mt-5 rounded-3xl border border-dashed border-zinc-300 bg-white p-12 text-center dark:border-zinc-700 dark:bg-zinc-900">
                <flux:icon name="globe-alt" class="mx-auto size-12 text-indigo-500" />
                <h3 class="mt-5 text-lg font-semibold dark:text-white">Não existem resultados para este filtro</h3>
                <p class="mt-2 text-sm text-zinc-500">Experimenta outro filtro ou volta a ver todos os websites.</p>
                <flux:button class="mt-6" variant="primary" wire:click="clearFilter">Ver todos os websites</flux:button>
            </div>
        @else
            <div class="mt-5 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($sites as $site)
                    <article class="flex flex-col overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="relative h-40 overflow-hidden bg-gradient-to-br from-indigo-500 via-violet-500 to-fuchsia-500">
                            <div class="absolute inset-0 bg-black/10"></div>
                            <div class="relative flex h-full flex-col justify-between p-5">
                                <span class="inline-flex w-fit items-center gap-1.5 rounded-full bg-white/90 px-2.5 py-1 text-xs font-semibold text-zinc-700">
                                    <span class="size-1.5 rounded-full {{ $site->is_published ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                                    {{ $site->is_published ? 'Publicado' : 'Rascunho' }}
                                </span>
                                <div class="rounded-xl bg-white/15 px-3 py-2.5 text-white backdrop-blur">
                                    <p class="text-xs uppercase tracking-wide text-white/80">{{ ucfirst(str_replace('_', ' ', $site->type)) }}</p>
                                    <p class="mt-0.5 truncate text-lg font-semibold">{{ $site->name }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-1 flex-col p-5">
                            <p class="line-clamp-2 min-h-10 text-sm leading-6 text-zinc-500 dark:text-zinc-400">{{ $site->description ?: 'Personaliza o teu website, adiciona páginas e publica quando estiveres pronto.' }}</p>
                            <div class="mt-4 flex gap-4 text-xs font-medium text-zinc-500"><span>{{ $site->pages_count }} páginas</span><span>{{ $site->products_count }} produtos</span></div>
                            <div class="mt-5 flex gap-2 border-t border-zinc-100 pt-4 dark:border-zinc-800">
                                <flux:button class="flex-1" variant="ghost" href="{{ route('site.manage.dashboard') }}">Gerir</flux:button>
                                <flux:button class="flex-1" variant="primary" icon="eye" href="{{ route('site.public', ['site' => $site, 'preview' => 1]) }}" target="_blank">Ver site</flux:button>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif

        <div class="mt-8 flex justify-center">
            <a href="{{ route('site.create') }}" wire:navigate class="group inline-flex items-center gap-3 rounded-2xl bg-indigo-600 px-6 py-4 text-base font-bold text-white shadow-lg shadow-indigo-500/25 transition hover:-translate-y-0.5 hover:bg-indigo-700 hover:shadow-xl hover:shadow-indigo-500/30 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-zinc-950">
                <span class="flex size-10 items-center justify-center rounded-xl bg-white/15 ring-1 ring-white/20 transition group-hover:scale-105">
                    <flux:icon name="plus" class="size-5" />
                </span>
                <span>
                    <span class="block text-xs font-semibold uppercase tracking-wider text-indigo-100">Novo projeto</span>
                    <span class="block text-base font-bold">Criar website</span>
                </span>
                <flux:icon name="arrow-right" class="size-5 transition group-hover:translate-x-1" />
            </a>
        </div>

        @php
            $quickLinks = [
                ['title' => 'Produtos', 'description' => 'Catálogo, stock e promoções.', 'icon' => 'shopping-bag', 'url' => route('products')],
                ['title' => 'Vendas e pagamentos', 'description' => 'Vendas e pagamentos recebidos.', 'icon' => 'currency-euro', 'url' => route('sales')],
                ['title' => 'Encomendas', 'description' => 'Consulta e gere as encomendas.', 'icon' => 'clipboard-document-list', 'url' => route('orders')],
                ['title' => 'Utilizadores', 'description' => 'Gere utilizadores dos websites.', 'icon' => 'users', 'url' => route('users')],
                ['title' => 'Dashboard do website', 'description' => 'Resumo do website selecionado.', 'icon' => 'squares-2x2', 'url' => route('site.manage.dashboard')],
                ['title' => 'Configuração do site', 'description' => 'Dados e opções do website.', 'icon' => 'cog-6-tooth', 'url' => route('site.manage.settings')],
                ['title' => 'Biblioteca de media', 'description' => 'Imagens e ficheiros do website.', 'icon' => 'photo', 'url' => route('site.manage.media')],
                ['title' => 'Menus', 'description' => 'Organiza a navegação do website.', 'icon' => 'bars-3', 'url' => route('site.manage.menus')],
                ['title' => 'Definições', 'description' => 'Opções e configuração do modelo.', 'icon' => 'adjustments-horizontal', 'url' => route('site.manage.definitions')],
                ['title' => 'Categorias', 'description' => 'Organiza as categorias de produtos.', 'icon' => 'tag', 'url' => route('site.manage.categories')],
                ['title' => 'Submissões', 'description' => 'Formulários recebidos pelo website.', 'icon' => 'inbox', 'url' => route('site.manage.submissions')],
                ['title' => 'Plano e funcionalidades', 'description' => 'Consulta planos e funcionalidades.', 'icon' => 'sparkles', 'url' => route('site.manage.upgrade')],
                ['title' => 'A minha conta', 'description' => 'Perfil e dados da tua conta.', 'icon' => 'user-circle', 'url' => route('account')],
                ['title' => 'Notificações', 'description' => 'Avisos e atualizações da conta.', 'icon' => 'bell', 'url' => route('notifications')],
                ['title' => 'Ajuda', 'description' => 'Orientações para utilizar o Finder.', 'icon' => 'book-open-text', 'url' => route('help')],
            ];
        @endphp

        <div class="mt-5 flex justify-center">
            <flux:modal.trigger name="dashboard-quick-access">
                <flux:button variant="outline" icon="squares-2x2">Todas as opções do Finder</flux:button>
            </flux:modal.trigger>
        </div>

        <flux:modal name="dashboard-quick-access" class="w-full max-w-4xl">
            <div class="space-y-5">
                <div>
                    <flux:heading size="lg">Todas as opções do Finder</flux:heading>
                    <flux:text class="mt-1">Escolhe a área que pretendes abrir.</flux:text>
                </div>

                <div class="grid max-h-[65vh] grid-cols-1 gap-3 overflow-y-auto pr-1 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($quickLinks as $link)
                        <a href="{{ $link['url'] }}" wire:navigate class="group flex items-start gap-3 rounded-xl border border-zinc-200 p-4 transition hover:border-indigo-300 hover:bg-indigo-50/50 dark:border-zinc-700 dark:hover:border-indigo-700 dark:hover:bg-indigo-500/5">
                            <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                                <flux:icon :name="$link['icon']" class="size-5" />
                            </span>
                            <span class="min-w-0">
                                <span class="block text-sm font-semibold text-zinc-900 dark:text-white">{{ $link['title'] }}</span>
                                <span class="mt-1 block text-xs leading-5 text-zinc-500 dark:text-zinc-400">{{ $link['description'] }}</span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </flux:modal>
    </div>
</div>
