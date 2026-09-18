<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>@include('partials.head')</head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        @php
            $currentSite = \App\Support\SiteContext::current();
            $hasSite = $currentSite !== null;
            $isDashboard = request()->routeIs('dashboard');
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
            $modelProfile = $hasSite
                ? config('website.model_profiles.'.$currentSite->type, [])
                : [];
            $modelFields = $modelProfile['fields'] ?? [];
            $modelFieldValues = $hasSite ? (data_get($currentSite->settings, 'model_content', []) ?? []) : [];
            $modelFilled = collect($modelFields)->filter(fn ($field) => filled($modelFieldValues[$field['key']] ?? null))->count();
            $modelTotal = count($modelFields);
        @endphp

        @if($isPlatformAdmin)
            <div class="fixed inset-x-0 top-0 z-50 flex items-center justify-center gap-4 border-b border-amber-300 bg-amber-100 px-4 py-2 text-xs font-semibold text-amber-900">
                <span>Modo de administração da plataforma ativo — estás a gerir um website como administrador.</span>
                <form method="POST" action="{{ route('admin.websites.exit-access') }}">@csrf<button class="rounded-lg bg-amber-900 px-3 py-1.5 text-white">Sair deste website</button></form>
            </div>
        @endif

        @if($isDashboard)
            <header class="sticky top-0 z-40 border-b border-zinc-200 bg-zinc-50/95 backdrop-blur-xl dark:border-zinc-700 dark:bg-zinc-900/95 {{ $isPlatformAdmin ? 'pt-10' : '' }}">
                <div class="mx-auto flex min-h-16 max-w-7xl items-center gap-4 px-4 sm:px-6 lg:px-8">
                    <a href="{{ route('home') }}" wire:navigate class="shrink-0"><x-app-logo :sidebar="false" /></a>
                    <nav class="flex min-w-0 flex-1 items-center gap-1 overflow-x-auto" aria-label="Navegação principal">
                        <a href="{{ route('dashboard') }}" wire:navigate class="shrink-0 rounded-xl bg-zinc-900 px-4 py-2 text-sm font-semibold text-white dark:bg-white dark:text-zinc-900">Meus websites</a>
                        <a href="{{ route('site.create') }}" wire:navigate class="shrink-0 rounded-xl px-4 py-2 text-sm font-semibold text-zinc-600 transition hover:bg-white hover:text-zinc-950 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-white">Criar website</a>
                        <a href="{{ route('products') }}" wire:navigate class="shrink-0 rounded-xl px-4 py-2 text-sm font-semibold text-zinc-600 transition hover:bg-white hover:text-zinc-950 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-white">Produtos</a>
                        <a href="{{ route('sales') }}" wire:navigate class="shrink-0 rounded-xl px-4 py-2 text-sm font-semibold text-zinc-600 transition hover:bg-white hover:text-zinc-950 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-white">Vendas</a>
                        <a href="{{ route('orders.tracking') }}" wire:navigate class="shrink-0 rounded-xl px-4 py-2 text-sm font-semibold text-zinc-600 transition hover:bg-white hover:text-zinc-950 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-white">Encomendas</a>
                        <a href="{{ route('account') }}" wire:navigate class="shrink-0 rounded-xl px-4 py-2 text-sm font-semibold text-zinc-600 transition hover:bg-white hover:text-zinc-950 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-white">Conta</a>
                        <a href="{{ route('customer.assistant') }}" wire:navigate class="shrink-0 rounded-xl px-4 py-2 text-sm font-semibold text-zinc-600 transition hover:bg-white hover:text-zinc-950 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-white">Assistente IA</a>
                    </nav>
                    <div class="hidden shrink-0 lg:block"><x-desktop-user-menu :name="auth()->user()->name" /></div>
                </div>
            </header>
        @else
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
                <flux:sidebar.group heading="Finder Hub" class="grid">
                    <flux:sidebar.item icon="squares-2x2" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>Meus websites</flux:sidebar.item>
                    @if(auth()->user()?->isAdministrator())<flux:sidebar.item icon="shield-check" :href="route('admin.websites')" :current="request()->routeIs('admin.websites')" wire:navigate>Administração</flux:sidebar.item>@endif
                    @if($hasSite)<flux:sidebar.item icon="home" :href="$siteRoute('dashboard').'#model-content'" :current="request()->routeIs('admin.site.dashboard')" wire:navigate>Dashboard</flux:sidebar.item>@endif
                </flux:sidebar.group>

                @if($hasSite)
                    <flux:sidebar.group heading="Dados do site" class="grid">
                        <flux:sidebar.item icon="cog-6-tooth" :href="$siteRoute('dashboard').'#model-content'" :current="request()->routeIs('admin.site.dashboard')" wire:navigate>
                            <span class="flex min-w-0 flex-1 items-center gap-2">
                                <span class="truncate">Configuração do modelo</span>
                                <span class="ms-auto shrink-0 text-[10px] font-semibold text-zinc-400">{{ $modelFilled }}/{{ $modelTotal }} preenchidos</span>
                            </span>
                        </flux:sidebar.item>
                    </flux:sidebar.group>

                    <flux:sidebar.group heading="Website" class="grid">
                        <flux:sidebar.item icon="photo" :href="$siteRoute('media')" :current="request()->routeIs('admin.site.media')" wire:navigate>Media</flux:sidebar.item>
                        <flux:sidebar.item icon="document-text" :href="$siteRoute('menus')" :current="request()->routeIs('admin.site.menus')" wire:navigate>Menus</flux:sidebar.item>
                        <flux:sidebar.item icon="cog-6-tooth" :href="$siteRoute('settings')" :current="request()->routeIs('admin.site.settings')" wire:navigate>Definições</flux:sidebar.item>
                    </flux:sidebar.group>

                    @if($currentSite->type === 'online_store')
                        <flux:sidebar.group heading="Vendas" class="grid">
                            <flux:sidebar.item icon="presentation-chart-line" :href="$siteRoute('sales')" :current="request()->routeIs('admin.site.sales')" wire:navigate>Vendas</flux:sidebar.item>
                            <flux:sidebar.item icon="currency-euro" :href="$siteRoute('payments')" :current="request()->routeIs('admin.site.payments')" wire:navigate>Pagamentos</flux:sidebar.item>
                            <flux:sidebar.item icon="archive-box" :href="$siteRoute('stock')" :current="request()->routeIs('admin.site.stock')" wire:navigate>Stock</flux:sidebar.item>
                            <flux:sidebar.item icon="tag" :href="$siteRoute('promotions')" :current="request()->routeIs('admin.site.promotions')" wire:navigate>Promoções</flux:sidebar.item>
                        </flux:sidebar.group>
                    @endif

                    <flux:sidebar.group heading="Inteligência" class="grid">
                        @can('access-reports', $currentSite)
                            <flux:sidebar.item icon="chart-bar" :href="$siteRoute('reports')" :current="request()->routeIs('admin.site.reports')" wire:navigate>Relatórios</flux:sidebar.item>
                            <flux:sidebar.item icon="presentation-chart-line" :href="$siteRoute('analytics')" :current="request()->routeIs('admin.site.analytics')" wire:navigate>Analytics</flux:sidebar.item>
                        @else
                            <flux:sidebar.item icon="lock-closed" :href="$siteRoute('upgrade')" :current="request()->routeIs('admin.site.upgrade')" wire:navigate>Relatórios <span class="ms-auto text-[10px] font-semibold uppercase tracking-wide text-amber-500">Pro</span></flux:sidebar.item>
                            <flux:sidebar.item icon="lock-closed" :href="$siteRoute('upgrade')" wire:navigate>Analytics <span class="ms-auto text-[10px] font-semibold uppercase tracking-wide text-amber-500">Pro</span></flux:sidebar.item>
                        @endcan
                        @can('access-ai', $currentSite)
                            <flux:sidebar.item icon="sparkles" :href="$siteRoute('assistant')" :current="request()->routeIs('admin.site.assistant')" wire:navigate>Assistente IA</flux:sidebar.item>
                        @else
                            <flux:sidebar.item icon="lock-closed" :href="$siteRoute('upgrade')" :current="request()->routeIs('admin.site.upgrade')" wire:navigate>Assistente IA <span class="ms-auto text-[10px] font-semibold uppercase tracking-wide text-amber-500">Pro</span></flux:sidebar.item>
                        @endcan
                    </flux:sidebar.group>
                @else
                    <flux:sidebar.group heading="Finder" class="grid">
                        <flux:sidebar.item icon="squares-2x2" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>Meus websites</flux:sidebar.item>
                        <flux:sidebar.item icon="plus" :href="route('site.create')" :current="request()->routeIs('site.create')" wire:navigate>Criar website</flux:sidebar.item>
                        <flux:sidebar.item icon="shopping-bag" :href="route('products')" :current="request()->routeIs('products')" wire:navigate>Produtos</flux:sidebar.item>
                        <flux:sidebar.item icon="currency-euro" :href="route('sales')" :current="request()->routeIs('sales')" wire:navigate>Vendas</flux:sidebar.item>
                        <flux:sidebar.item icon="clipboard-document-list" :href="route('orders.tracking')" :current="request()->routeIs('orders.tracking')" wire:navigate>Encomendas</flux:sidebar.item>
                    </flux:sidebar.group>

                    <flux:sidebar.group heading="A minha conta" class="grid">
                        <flux:sidebar.item icon="user-circle" :href="route('account')" :current="request()->routeIs('account')" wire:navigate>Conta</flux:sidebar.item>
                        <flux:sidebar.item icon="sparkles" :href="route('customer.assistant')" :current="request()->routeIs('customer.assistant')" wire:navigate>Assistente IA</flux:sidebar.item>
                    </flux:sidebar.group>

                    @if(auth()->user()?->isAdministrator())
                        <flux:sidebar.group heading="Administração" class="grid">
                            <flux:sidebar.item icon="shield-check" :href="route('admin.websites')" :current="request()->routeIs('admin.websites')" wire:navigate>Websites</flux:sidebar.item>
                            <flux:sidebar.item icon="users" :href="route('admin.users')" :current="request()->routeIs('admin.users')" wire:navigate>Utilizadores</flux:sidebar.item>
                            <flux:sidebar.item icon="cog-6-tooth" :href="route('admin.config')" :current="request()->routeIs('admin.config')" wire:navigate>Configurações</flux:sidebar.item>
                        </flux:sidebar.group>
                    @endif
                @endif
            </flux:sidebar.nav>

            <flux:spacer /><flux:sidebar.nav><flux:sidebar.item icon="book-open-text" :href="route('help')" :current="request()->routeIs('help')" wire:navigate>Ajuda</flux:sidebar.item></flux:sidebar.nav><x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>
        @endif
        @if(! $isDashboard)
        <flux:header class="lg:hidden"><flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" /><flux:spacer /></flux:header>
        @endif
        {{ $slot }}
        @if($hasSite && auth()->user()->can('access-ai', $currentSite))<livewire:admin-assistant />@endif
        @persist('toast')<flux:toast.group><flux:toast /></flux:toast.group>@endpersist
        @fluxScripts
    </body>
</html>
