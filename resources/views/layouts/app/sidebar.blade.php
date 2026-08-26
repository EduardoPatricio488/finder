<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        @php
            $currentSite = \App\Support\SiteContext::current();
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
            $siteRoute = fn (string $name, ?\App\Models\Site $site = null): string => route('admin.site.'.$name, $site ?? $currentSite);
        @endphp

        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('home') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <div class="px-3 pb-3">
                <flux:dropdown position="bottom" align="start">
                    <button type="button" class="flex w-full items-center justify-between gap-3 rounded-xl border border-zinc-700 bg-zinc-800 px-3 py-2.5 text-left text-sm text-white">
                        <span class="min-w-0">
                            <span class="block truncate font-semibold">{{ $currentSite->name }}</span>
                            <span class="mt-0.5 block text-xs text-zinc-400">{{ $currentSite->statusLabel() }}</span>
                        </span>
                        <flux:icon.chevrons-up-down class="size-4 shrink-0 text-zinc-400" />
                    </button>

                    <flux:menu>
                        @foreach ($availableSites as $site)
                            <flux:menu.item :href="route('admin.site.dashboard', $site)" wire:navigate>
                                {{ $site->name }} @if ($site->is($currentSite)) ✓ @endif
                            </flux:menu.item>
                        @endforeach
                        <flux:menu.separator />
                        <flux:menu.item :href="route('home')" icon="plus" wire:navigate>
                            Criar novo site
                        </flux:menu.item>
                    </flux:menu>
                </flux:dropdown>
            </div>

            <flux:sidebar.nav>
                <flux:sidebar.group heading="Visão geral" class="grid">
                    <flux:sidebar.item icon="squares-2x2" :href="route('home')" :current="request()->routeIs('home')" wire:navigate>
                        Início Finder
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="home" :href="$siteRoute('dashboard')" :current="request()->routeIs('admin.site.dashboard', 'dashboard')" wire:navigate>
                        Dashboard
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group heading="Catálogo" class="grid">
                    <flux:sidebar.item icon="shopping-bag" :href="$siteRoute('products')" :current="request()->routeIs('admin.site.products', 'admin.products')" wire:navigate>
                        Produtos
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="tag" :href="$siteRoute('categories')" :current="request()->routeIs('admin.site.categories', 'admin.categories')" wire:navigate>
                        Categorias
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="archive-box" :href="$siteRoute('stock')" :current="request()->routeIs('admin.site.stock', 'admin.stock')" wire:navigate>
                        Stock
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group heading="Vendas" class="grid">
                    <flux:sidebar.item icon="receipt-percent" :href="$siteRoute('sales')" :current="request()->routeIs('admin.site.sales', 'admin.sales')" wire:navigate>
                        Vendas
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="clipboard-document-list" :href="$siteRoute('orders')" :current="request()->routeIs('admin.site.orders', 'admin.orders')" wire:navigate>
                        Encomendas
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="credit-card" :href="$siteRoute('payments')" :current="request()->routeIs('admin.site.payments', 'admin.payments')" wire:navigate>
                        Pagamentos
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group heading="Clientes" class="grid">
                    <flux:sidebar.item icon="user-group" :href="$siteRoute('customers')" :current="request()->routeIs('admin.site.customers', 'admin.customers')" wire:navigate>
                        Clientes
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group heading="Equipa" class="grid">
                    <flux:sidebar.item icon="users" :href="$siteRoute('users')" :current="request()->routeIs('admin.site.users', 'admin.users')" wire:navigate>
                        Utilizadores
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group heading="Análise" class="grid">
                    <flux:sidebar.item icon="chart-bar" :href="$siteRoute('reports')" :current="request()->routeIs('admin.site.reports', 'admin.reports')" wire:navigate>
                        Relatórios
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group heading="Marketing" class="grid">
                    <flux:sidebar.item icon="receipt-percent" :href="$siteRoute('promotions')" :current="request()->routeIs('admin.site.promotions', 'admin.promotions')" wire:navigate>
                        Promoções
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group heading="Sistema" class="grid">
                    <flux:sidebar.item icon="cog-6-tooth" :href="$siteRoute('config')" :current="request()->routeIs('admin.site.config', 'admin.config')" wire:navigate>
                        Configuração
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="sparkles" :href="$siteRoute('assistant')" :current="request()->routeIs('admin.site.assistant', 'admin.assistant')" wire:navigate>
                        Assistente administrativo
                    </flux:sidebar.item>
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <flux:spacer />

            <flux:sidebar.nav>
                <flux:sidebar.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit" target="_blank">
                    {{ __('Repository') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire" target="_blank">
                    {{ __('Documentation') }}
                </flux:sidebar.item>
            </flux:sidebar.nav>

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        <livewire:admin-assistant />

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
