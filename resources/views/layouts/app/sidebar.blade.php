<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>@include('partials.head')</head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        @php
            $currentSite = \App\Support\SiteContext::current();
            $hasSite = $currentSite !== null;
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
                        <span class="min-w-0">@if($hasSite)<span class="block truncate font-semibold">{{ $currentSite->name }}</span><span class="mt-0.5 block text-xs text-zinc-400">{{ $currentSite->statusLabel() }}</span>@else<span class="block truncate font-semibold text-zinc-400 italic">Selecionar Projeto</span>@endif</span><flux:icon.chevrons-up-down class="size-4 shrink-0 text-zinc-400" />
                    </button>
                    <flux:menu>@foreach ($availableSites as $site)<flux:menu.item :href="route('admin.site.dashboard', $site)" wire:navigate>{{ $site->name }} @if ($hasSite && $site->is($currentSite)) ✓ @endif</flux:menu.item>@endforeach<flux:menu.separator /><flux:menu.item :href="route('site.create')" icon="plus" wire:navigate>Criar novo website</flux:menu.item></flux:menu>
                </flux:dropdown>
            </div>
            <flux:sidebar.nav>
                <flux:sidebar.group heading="Finder Hub" class="grid">
                    <flux:sidebar.item icon="squares-2x2" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>Meus websites</flux:sidebar.item>
                    @if(auth()->user()?->isAdministrator())<flux:sidebar.item icon="shield-check" :href="route('admin.websites')" :current="request()->routeIs('admin.websites')" wire:navigate>Administração</flux:sidebar.item>@endif
                    @if($hasSite)<flux:sidebar.item icon="home" :href="$siteRoute('dashboard')" :current="request()->routeIs('admin.site.dashboard')" wire:navigate>Dashboard</flux:sidebar.item>@endif
                </flux:sidebar.group>
                @if($hasSite)
                    <flux:sidebar.group heading="Website" class="grid">
                        <flux:sidebar.item icon="pencil-square" disabled class="pointer-events-none opacity-50" aria-disabled="true"><span class="flex items-center gap-2">Editor visual <span class="text-[10px] text-zinc-400" title="Indisponível">Bloqueado</span></span></flux:sidebar.item>
                        <flux:sidebar.item icon="photo" :href="$siteRoute('media')" :current="request()->routeIs('admin.site.media')" wire:navigate>Media</flux:sidebar.item>
                        <flux:sidebar.item icon="document-text" :href="$siteRoute('menus')" :current="request()->routeIs('admin.site.menus')" wire:navigate>Menus</flux:sidebar.item>
                        <flux:sidebar.item icon="cog-6-tooth" :href="$siteRoute('settings')" :current="request()->routeIs('admin.site.settings')" wire:navigate>Definições</flux:sidebar.item>
                    </flux:sidebar.group>
                    <flux:sidebar.group heading="Catálogo" class="grid"><flux:sidebar.item icon="shopping-bag" :href="$siteRoute('products')" :current="request()->routeIs('admin.site.products')" wire:navigate>Produtos</flux:sidebar.item><flux:sidebar.item icon="tag" :href="$siteRoute('categories')" :current="request()->routeIs('admin.site.categories')" wire:navigate>Categorias</flux:sidebar.item></flux:sidebar.group>
                    <flux:sidebar.group heading="Vendas & Clientes" class="grid"><flux:sidebar.item icon="clipboard-document-list" :href="$siteRoute('orders')" :current="request()->routeIs('admin.site.orders')" wire:navigate>Encomendas</flux:sidebar.item><flux:sidebar.item icon="users" :href="$siteRoute('users')" :current="request()->routeIs('admin.site.users')" wire:navigate>Utilizadores</flux:sidebar.item></flux:sidebar.group>
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
                    <div class="px-4 py-8 text-center"><p class="text-xs font-bold uppercase tracking-widest text-zinc-500">Cria ou seleciona um website para começar.</p><flux:button :href="route('site.create')" size="sm" class="mt-4 !rounded-xl" variant="primary">Criar website</flux:button></div>
                @endif
            </flux:sidebar.nav>
            <flux:spacer /><flux:sidebar.nav><flux:sidebar.item icon="book-open-text" :href="route('help')" :current="request()->routeIs('help')" wire:navigate>Ajuda</flux:sidebar.item></flux:sidebar.nav><x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>
        <flux:header class="lg:hidden"><flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" /><flux:spacer /></flux:header>
        {{ $slot }}
        @if($hasSite && auth()->user()->can('access-ai', $currentSite))<livewire:admin-assistant />@endif
        @persist('toast')<flux:toast.group><flux:toast /></flux:toast.group>@endpersist
        @fluxScripts
    </body>
</html>
