@php
    $item = function (bool $active): string {
        return $active
            ? 'flex items-center gap-3 rounded-xl bg-amber-100 px-3 py-2.5 text-sm font-semibold text-amber-950 shadow-sm'
            : 'flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-stone-600 transition hover:translate-x-0.5 hover:bg-amber-50 hover:text-stone-950';
    };
@endphp

<aside
    class="fixed inset-y-0 left-0 z-50 flex h-screen w-72 shrink-0 flex-col border-r border-stone-200 bg-white px-5 py-6 shadow-xl transition-transform duration-300 lg:shadow-none"
    :class="menuOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
>
    <div class="flex items-start justify-between gap-3">
        <a href="{{ route('sales') }}" wire:navigate class="group block">
            <span class="block text-xl font-semibold tracking-tight transition group-hover:text-amber-800">Casa &amp; Co.</span>
            <span class="mt-1 block text-[11px] font-semibold uppercase tracking-[0.18em] text-stone-400">Área do cliente</span>
        </a>
        <button
            type="button"
            class="inline-flex size-9 items-center justify-center rounded-lg text-stone-500 transition hover:bg-stone-100 hover:text-stone-950 lg:hidden"
            aria-label="Fechar menu"
            @click="menuOpen = false"
        >
            <flux:icon.x-mark class="size-5" />
        </button>
    </div>

    <nav class="mt-8 flex-1 space-y-1 overflow-y-auto pr-1">
        <a href="{{ route('home') }}" wire:navigate class="{{ $item(request()->routeIs('home')) }}"><flux:icon.squares-2x2 class="size-4 shrink-0" /><span>Todos os sites</span></a>
        <a href="{{ route('sales') }}" wire:navigate class="{{ $item(request()->routeIs('sales')) }}"><flux:icon.home class="size-4 shrink-0" /><span>Loja</span></a>
        <a href="{{ route('products') }}" wire:navigate class="{{ $item(request()->routeIs('products', 'products.show')) }}"><flux:icon.shopping-bag class="size-4 shrink-0" /><span>Explorar produtos</span></a>
        <a href="{{ route('orders.tracking') }}" wire:navigate class="{{ $item(request()->routeIs('orders.tracking')) }}"><flux:icon.truck class="size-4 shrink-0" /><span>As minhas encomendas</span></a>
        <a href="{{ route('account.favorites') }}" wire:navigate class="{{ $item(request()->routeIs('account.favorites')) }}"><flux:icon.heart class="size-4 shrink-0" /><span>Favoritos</span></a>
        <a href="{{ route('products', ['cart' => 1]) }}" wire:navigate class="{{ $item(false) }}"><flux:icon.shopping-cart class="size-4 shrink-0" /><span>Carrinho</span></a>

        <div class="my-5 border-t border-stone-200"></div>

        <a href="{{ route('account') }}" wire:navigate class="block px-3 pb-2 text-[10px] font-semibold uppercase tracking-[0.18em] text-stone-400 hover:text-amber-700">A minha conta</a>
        <a href="{{ route('profile.edit') }}" wire:navigate class="{{ $item(request()->routeIs('profile.edit')) }}"><flux:icon.user class="size-4 shrink-0" /><span>Perfil</span></a>
        <a href="{{ route('account.addresses') }}" wire:navigate class="{{ $item(request()->routeIs('account.addresses')) }}"><flux:icon.map-pin class="size-4 shrink-0" /><span>Moradas</span></a>
        <a href="{{ route('security.edit') }}" wire:navigate class="{{ $item(request()->routeIs('security.edit')) }}"><flux:icon.lock-closed class="size-4 shrink-0" /><span>Segurança</span></a>
        <a href="{{ route('account.payments') }}" wire:navigate class="{{ $item(request()->routeIs('account.payments')) }}"><flux:icon.credit-card class="size-4 shrink-0" /><span>Pagamentos</span></a>

        <div class="my-5 border-t border-stone-200"></div>

        <a href="{{ route('account.notifications') }}" wire:navigate class="{{ $item(request()->routeIs('account.notifications')) }}"><flux:icon.bell class="size-4 shrink-0" /><span>Notificações</span></a>
        <a href="{{ route('appearance.edit') }}" wire:navigate class="{{ $item(request()->routeIs('appearance.edit')) }}"><flux:icon.cog-6-tooth class="size-4 shrink-0" /><span>Definições</span></a>

        @if (auth()->user()->isAdministrator())
            <div class="my-5 border-t border-stone-200"></div>

            <a href="{{ route('dashboard') }}" wire:navigate class="block px-3 pb-2 text-[10px] font-semibold uppercase tracking-[0.18em] text-stone-400 hover:text-amber-700">Administração</a>
            <a href="{{ route('dashboard') }}" wire:navigate class="{{ $item(request()->routeIs('dashboard')) }}"><flux:icon.home class="size-4 shrink-0" /><span>Painel</span></a>
            <a href="{{ route('admin.products') }}" wire:navigate class="{{ $item(request()->routeIs('admin.products')) }}"><flux:icon.cube class="size-4 shrink-0" /><span>Produtos</span></a>
            <a href="{{ route('admin.promotions') }}" wire:navigate class="{{ $item(request()->routeIs('admin.promotions')) }}"><flux:icon.receipt-percent class="size-4 shrink-0" /><span>Promoções</span></a>
            <a href="{{ route('admin.categories') }}" wire:navigate class="{{ $item(request()->routeIs('admin.categories')) }}"><flux:icon.tag class="size-4 shrink-0" /><span>Categorias</span></a>
            <a href="{{ route('admin.sales') }}" wire:navigate class="{{ $item(request()->routeIs('admin.sales')) }}"><flux:icon.banknotes class="size-4 shrink-0" /><span>Vendas</span></a>
            <a href="{{ route('admin.orders') }}" wire:navigate class="{{ $item(request()->routeIs('admin.orders')) }}"><flux:icon.clipboard-document-list class="size-4 shrink-0" /><span>Encomendas</span></a>
            <a href="{{ route('admin.payments') }}" wire:navigate class="{{ $item(request()->routeIs('admin.payments')) }}"><flux:icon.credit-card class="size-4 shrink-0" /><span>Pagamentos</span></a>
            <a href="{{ route('admin.customers') }}" wire:navigate class="{{ $item(request()->routeIs('admin.customers')) }}"><flux:icon.user-group class="size-4 shrink-0" /><span>Clientes</span></a>
            <a href="{{ route('admin.users') }}" wire:navigate class="{{ $item(request()->routeIs('admin.users')) }}"><flux:icon.users class="size-4 shrink-0" /><span>Utilizadores</span></a>
            <a href="{{ route('admin.reports') }}" wire:navigate class="{{ $item(request()->routeIs('admin.reports')) }}"><flux:icon.chart-bar class="size-4 shrink-0" /><span>Relatórios</span></a>
            <a href="{{ route('admin.stock') }}" wire:navigate class="{{ $item(request()->routeIs('admin.stock')) }}"><flux:icon.archive-box class="size-4 shrink-0" /><span>Movimentos de stock</span></a>
            <a href="{{ route('admin.config') }}" wire:navigate class="{{ $item(request()->routeIs('admin.config')) }}"><flux:icon.cog-6-tooth class="size-4 shrink-0" /><span>Configuração</span></a>
        @endif
    </nav>

    <div class="shrink-0 border-t border-stone-200 pt-4">
        <a href="{{ route('profile.edit') }}" wire:navigate class="flex items-center gap-3 rounded-xl p-2 transition hover:bg-amber-50">
            <span class="flex size-10 items-center justify-center rounded-xl bg-stone-950 text-xs font-bold text-amber-200">{{ auth()->user()->initials() }}</span>
            <span class="min-w-0">
                <span class="block truncate text-sm font-semibold">{{ auth()->user()->name }}</span>
                <span class="block truncate text-xs text-stone-500">{{ auth()->user()->email }}</span>
            </span>
        </a>
        <form method="POST" action="{{ route('logout') }}" class="mt-3">
            @csrf
            <button type="submit" class="flex w-full items-center gap-2 rounded-xl px-2 py-2 text-sm text-stone-500 transition hover:bg-stone-100 hover:text-stone-950">
                <flux:icon.arrow-right-start-on-rectangle class="size-4" />
                <span>Terminar sessão</span>
            </button>
        </form>
    </div>
</aside>
