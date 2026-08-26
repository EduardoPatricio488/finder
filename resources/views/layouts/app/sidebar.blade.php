<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        @php
            $currentSite = \App\Support\SiteContext::current();
            $hasSite = $currentSite !== null;

            $availableSites = \App\Models\Site::query()
                ->where(function ($query) {
                    $query->where('owner_id', auth()->id())
                        ->orWhereHas('members', fn ($members) => $members->whereKey(auth()->id()));

                    if (auth()->user()?->isAdministrator()) {
                        $query->orWhereNotNull('id');
                    }
                })
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();

            // Função de rota segura: só gera se houver site ou se for passada uma instância
            $siteRoute = function (string $name, ?\App\Models\Site $site = null) use ($currentSite, $hasSite): string {
                $target = $site ?? $currentSite;
                if (! $target) return '#';

                $routeName = 'admin.site.' . $name;
                return Route::has($routeName) ? route($routeName, $target) : '#';
            };
        @endphp

        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('home') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            {{-- SELETOR DE SITES --}}
            <div class="px-3 pb-3">
                <flux:dropdown position="bottom" align="start">
                    <button type="button" class="flex w-full items-center justify-between gap-3 rounded-xl border border-zinc-700 bg-zinc-800 px-3 py-2.5 text-left text-sm text-white">
                        <span class="min-w-0">
                            @if($hasSite)
                                <span class="block truncate font-semibold">{{ $currentSite->name }}</span>
                                <span class="mt-0.5 block text-xs text-zinc-400">{{ $currentSite->statusLabel() }}</span>
                            @else
                                <span class="block truncate font-semibold text-zinc-400 italic">Selecionar Projeto</span>
                            @endif
                        </span>
                        <flux:icon.chevrons-up-down class="size-4 shrink-0 text-zinc-400" />
                    </button>

                    <flux:menu>
                        @foreach ($availableSites as $site)
                            <flux:menu.item :href="route('admin.site.dashboard', $site)" wire:navigate>
                                {{ $site->name }} @if ($hasSite && $site->is($currentSite)) ✓ @endif
                            </flux:menu.item>
                        @endforeach
                        <flux:menu.separator />
                        <flux:menu.item :href="route('site.create')" icon="plus" wire:navigate>
                            Criar nova loja
                        </flux:menu.item>
                    </flux:menu>
                </flux:dropdown>
            </div>

            <flux:sidebar.nav>
                {{-- NAVEGAÇÃO GLOBAL --}}
                <flux:sidebar.group heading="Finder Hub" class="grid">
                    <flux:sidebar.item icon="squares-2x2" :href="route('home')" :current="request()->routeIs('home')" wire:navigate>
                        Início / Projetos
                    </flux:sidebar.item>

                    @if($hasSite)
                        <flux:sidebar.item icon="home" :href="$siteRoute('dashboard')" :current="request()->routeIs('admin.site.dashboard')" wire:navigate>
                            Dashboard
                        </flux:sidebar.item>
                    @endif
                </flux:sidebar.group>

                {{-- SÓ APARECE SE TIVER UM SITE SELECIONADO --}}
                @if($hasSite)
                    <flux:sidebar.group heading="Catálogo" class="grid">
                        <flux:sidebar.item icon="shopping-bag" :href="$siteRoute('products')" :current="request()->routeIs('admin.site.products')" wire:navigate>
                            Produtos
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="tag" :href="$siteRoute('categories')" :current="request()->routeIs('admin.site.categories')" wire:navigate>
                            Categorias
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="archive-box" :href="$siteRoute('stock')" :current="request()->routeIs('admin.site.stock')" wire:navigate>
                            Stock
                        </flux:sidebar.item>
                    </flux:sidebar.group>

                    <flux:sidebar.group heading="Vendas" class="grid">
                        <flux:sidebar.item icon="clipboard-document-list" :href="$siteRoute('orders')" :current="request()->routeIs('admin.site.orders')" wire:navigate>
                            Encomendas
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="receipt-percent" :href="$siteRoute('sales')" :current="request()->routeIs('admin.site.sales')" wire:navigate>
                            Histórico de Vendas
                        </flux:sidebar.item>
                    </flux:sidebar.group>

                    <flux:sidebar.group heading="Clientes & Equipa" class="grid">
                        <flux:sidebar.item icon="user-group" :href="$siteRoute('customers')" :current="request()->routeIs('admin.site.customers')" wire:navigate>
                            Clientes
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="users" :href="$siteRoute('users')" :current="request()->routeIs('admin.site.users')" wire:navigate>
                            Utilizadores
                        </flux:sidebar.item>
                    </flux:sidebar.group>

                    <flux:sidebar.group heading="Marketing & IA" class="grid">
                        @can('access-reports', $currentSite)
                            <flux:sidebar.item icon="chart-bar" :href="$siteRoute('reports')" :current="request()->routeIs('admin.site.reports')" wire:navigate>
                                Relatórios Pro
                            </flux:sidebar.item>
                        @endcan

                        <flux:sidebar.item icon="receipt-percent" :href="$siteRoute('promotions')" :current="request()->routeIs('admin.site.promotions')" wire:navigate>
                            Promoções
                        </flux:sidebar.item>

                        @can('access-ai', $currentSite)
                            <flux:sidebar.item icon="sparkles" :href="$siteRoute('assistant')" :current="request()->routeIs('admin.site.assistant')" wire:navigate>
                                Assistente IA
                            </flux:sidebar.item>
                        @endcan
                    </flux:sidebar.group>

                    <flux:sidebar.group heading="Sistema" class="grid">
                        <flux:sidebar.item icon="cog-6-tooth" :href="$siteRoute('config')" :current="request()->routeIs('admin.site.config')" wire:navigate>
                            Configuração
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="credit-card" :href="$siteRoute('upgrade')" :current="request()->routeIs('admin.site.upgrade')" wire:navigate>
                            Plano & Faturação
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                @else
                    {{-- ESTADO VAZIO PARA QUANDO NÃO HÁ SITE --}}
                    <div class="px-4 py-8 text-center">
                        <p class="text-xs font-bold uppercase tracking-widest text-zinc-500">Cria ou seleciona um site para gerir catálogo e vendas.</p>
                        <flux:button :href="route('site.create')" size="sm" class="mt-4 !rounded-xl" variant="primary">Criar Minha Loja</flux:button>
                    </div>
                @endif
            </flux:sidebar.nav>

            <flux:spacer />

            {{-- LINKS ÚTEIS --}}
            <flux:sidebar.nav>
                <flux:sidebar.item icon="book-open-text" href="#" target="_blank">Ajuda</flux:sidebar.item>
            </flux:sidebar.nav>

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <!-- Header Mobile -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
            <flux:spacer />
            {{-- Menu de Perfil Mobile (Omitido para brevidade, manter o que tinhas) --}}
        </flux:header>

        {{ $slot }}

        {{-- Componentes Globais --}}
        @if($hasSite && auth()->user()->can('access-ai', $currentSite))
            <livewire:admin-assistant />
        @endif

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
