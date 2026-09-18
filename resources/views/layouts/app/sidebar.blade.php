<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>@include('partials.head')</head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        @php
            $currentSite = \App\Support\SiteContext::current();
            $hasSite = $currentSite !== null;
            $isDashboard = request()->routeIs('dashboard');
            $modelProfile = $hasSite ? config('website.model_profiles.'.($currentSite?->type ?? ''), []) : [];
            $isPlatformAdmin = auth()->user()?->isAdministrator() && session()->has('platform_admin_site_id');
            $availableSites = \App\Models\Site::query()->where(function ($query) {
                $query->where('owner_id', auth()->id())->orWhereHas('members', fn ($members) => $members->whereKey(auth()->id()));
                if (auth()->user()?->isAdministrator()) $query->orWhereNotNull('id');
            })->orderBy('sort_order')->orderBy('name')->get();
            $siteRoute = function (string $name, ?\App\Models\Site $site = null) use ($currentSite): string {
                $target = $site ?? $currentSite;
                if (! $target) return '#';
                $routeName = 'admin.site.'.$name;
                return Route::has($routeName) ? route($routeName, $target) : '#';
            };
        @endphp

        @if($isPlatformAdmin)
            <div class="fixed inset-x-0 top-0 z-50 flex items-center justify-center gap-4 border-b border-amber-300 bg-amber-100 px-4 py-2 text-xs font-semibold text-amber-900">
                <span>Modo de administração da plataforma ativo — estás a gerir um website como administrador.</span>
                <form method="POST" action="{{ route('admin.websites.exit-access') }}">@csrf<button class="rounded-lg bg-amber-900 px-3 py-1.5 text-white">Sair deste website</button></form>
            </div>
        @endif

        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 {{ $isPlatformAdmin ? 'pt-10' : '' }}">
            <flux:sidebar.header><x-app-logo :sidebar="true" href="{{ route('home') }}" wire:navigate /><flux:sidebar.collapse class="lg:hidden" /></flux:sidebar.header>
            <div class="px-3 pb-3">
                <flux:dropdown position="bottom" align="start">
                    <button type="button" class="flex w-full items-center justify-between gap-3 rounded-xl border border-zinc-700 bg-zinc-800 px-3 py-2.5 text-left text-sm text-white">
                        <span class="min-w-0">
                            @if($hasSite)
                                <span class="block truncate font-semibold">{{ $currentSite->name }}</span>
                                <span class="mt-0.5 block text-xs text-zinc-400">{{ $currentSite->statusLabel() }} · {{ $modelProfile['label'] ?? ($currentSite->category_label ?? 'Website') }}</span>
                            @else
                                <span class="block truncate font-semibold text-zinc-400 italic">Selecionar Projeto</span>
                            @endif
                        </span>
                        <flux:icon.chevrons-up-down class="size-4 shrink-0 text-zinc-400" />
                    </button>
                    <flux:menu>@foreach ($availableSites as $site)<flux:menu.item :href="route('admin.site.dashboard', $site)" wire:navigate>{{ $site->name }} @if ($hasSite && $site->is($currentSite)) ✓ @endif</flux:menu.item>@endforeach<flux:menu.separator /><flux:menu.item :href="route('site.create')" icon="plus" wire:navigate>Criar novo website</flux:menu.item></flux:menu>
                </flux:dropdown>
            </div>

            <flux:sidebar.nav>
                <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>Meus Websites</flux:sidebar.item>

                <flux:sidebar.group heading="Loja" class="grid">
                    <flux:sidebar.item icon="shopping-bag" :href="route('products')" :current="request()->routeIs('products')" wire:navigate>Produtos</flux:sidebar.item>
                    <flux:sidebar.item icon="currency-euro" :href="route('sales')" :current="request()->routeIs('sales')" wire:navigate>Vendas</flux:sidebar.item>
                    <flux:sidebar.item icon="clipboard-document-list" :href="route('orders.tracking')" :current="request()->routeIs('orders.tracking')" wire:navigate>Encomendas</flux:sidebar.item>
                </flux:sidebar.group>

                @if($hasSite && ! $isDashboard)
                    <flux:sidebar.group heading="Website" class="grid">
                        <flux:sidebar.item icon="cog-6-tooth" :href="$siteRoute('settings')" :current="request()->routeIs('admin.site.settings')" wire:navigate>Configuração do site</flux:sidebar.item>
                        <flux:sidebar.item icon="photo" :href="$siteRoute('media')" :current="request()->routeIs('admin.site.media')" wire:navigate>Media</flux:sidebar.item>
                        <flux:sidebar.item icon="document-text" :href="$siteRoute('menus')" :current="request()->routeIs('admin.site.menus')" wire:navigate>Menus</flux:sidebar.item>
                        <flux:sidebar.item icon="adjustments-horizontal" :href="$siteRoute('definitions')" :current="request()->routeIs('admin.site.definitions')" wire:navigate>Definições</flux:sidebar.item>
                    </flux:sidebar.group>

                    @if($currentSite->type === 'online_store')
                        <flux:sidebar.group heading="Vendas" class="grid">
                            <flux:sidebar.item icon="presentation-chart-line" :href="$siteRoute('sales')" :current="request()->routeIs('admin.site.sales')" wire:navigate>Vendas</flux:sidebar.item>
                            <flux:sidebar.item icon="currency-euro" :href="$siteRoute('payments')" :current="request()->routeIs('admin.site.payments')" wire:navigate>Pagamentos</flux:sidebar.item>
                            <flux:sidebar.item icon="archive-box" :href="$siteRoute('stock')" :current="request()->routeIs('admin.site.stock')" wire:navigate>Stock</flux:sidebar.item>
                            <flux:sidebar.item icon="tag" :href="$siteRoute('promotions')" :current="request()->routeIs('admin.site.promotions')" wire:navigate>Promoções</flux:sidebar.item>
                        </flux:sidebar.group>
                    @endif
                @endif
            </flux:sidebar.nav>

            <flux:spacer />

            <div class="px-3 pb-3">
                <flux:sidebar.nav class="!mt-0">
                    <flux:sidebar.group heading="Conta" class="grid">
                        <flux:sidebar.item icon="user-circle" :href="route('account')" :current="request()->routeIs('account')" wire:navigate>Minha conta</flux:sidebar.item>
                        <flux:sidebar.item icon="bell" :href="route('notifications')" :current="request()->routeIs('notifications')" wire:navigate>Notificações</flux:sidebar.item>
                    </flux:sidebar.group>
                    <flux:sidebar.item icon="book-open-text" :href="route('help')" :current="request()->routeIs('help')" wire:navigate>Ajuda</flux:sidebar.item>
                    <form method="POST" action="{{ route('logout') }}" class="mt-1">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-2 rounded-lg px-2 py-2 text-sm font-medium text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-950 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-white">
                            <flux:icon.arrow-right-start-on-rectangle class="size-5" />
                            <span>Logout</span>
                        </button>
                    </form>
                </flux:sidebar.nav>
            </div>
        </flux:sidebar>
        <flux:header class="lg:hidden"><flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" /><flux:spacer /></flux:header>
        {{ $slot }}
        @persist('toast')<flux:toast.group><flux:toast /></flux:toast.group>@endpersist
        @fluxScripts
    </body>
</html>