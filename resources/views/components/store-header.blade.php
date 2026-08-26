@props([
    'withSidebar' => false,
])

@php
    $now = now()->locale('pt_PT');
    $dateLabel = $now->isoFormat('dddd, D [de] MMMM');
    $timeLabel = $now->format('H:i');
@endphp

<header
    {{ $attributes->merge([
        'class' => 'fixed top-0 z-40 border-b border-stone-200/80 bg-white/90 shadow-[0_8px_30px_rgba(28,25,23,0.06)] backdrop-blur-xl '.($withSidebar ? 'inset-x-0 lg:left-72' : 'inset-x-0'),
    ]) }}
>
    <div class="flex h-[4.75rem] items-center justify-between gap-3 px-4 sm:px-6 lg:px-8">
        <div class="flex min-w-0 items-center gap-2 sm:gap-3">
            @if ($withSidebar)
                <button
                    type="button"
                    class="inline-flex size-10 items-center justify-center rounded-xl border border-stone-200 bg-white text-stone-700 shadow-sm transition hover:border-amber-300 hover:bg-amber-50 lg:hidden"
                    aria-label="Abrir menu"
                    @click="menuOpen = true"
                >
                    <flux:icon.bars-3 class="size-5" />
                </button>
            @endif

            <a
                href="{{ route('home') }}"
                wire:navigate
                class="inline-flex items-center gap-2 rounded-full border border-stone-200 bg-white px-3 py-2 text-sm font-semibold text-stone-700 shadow-sm transition hover:-translate-y-0.5 hover:border-stone-300 hover:bg-stone-50"
            >
                <span class="flex size-5 items-center justify-center rounded-md bg-stone-950 text-[10px] font-bold text-amber-200">F</span>
                <span class="hidden sm:inline">Sites</span>
            </a>

            <a
                href="{{ route('sales') }}"
                wire:navigate
                class="inline-flex items-center gap-2 rounded-full border border-stone-200 bg-stone-50 px-3 py-2 text-sm font-semibold text-stone-800 shadow-sm transition hover:-translate-y-0.5 hover:border-amber-300 hover:bg-amber-50 hover:text-amber-950"
            >
                <flux:icon.home class="size-4 text-amber-700" />
                <span>Início</span>
            </a>
        </div>

        <div class="flex min-w-0 items-center gap-2 sm:gap-3">
            {{ $actions ?? '' }}

            <div
                wire:ignore
                class="flex items-center gap-2 rounded-2xl border border-stone-200 bg-stone-50 px-2.5 py-1.5 sm:gap-3 sm:px-3"
                x-data="{
                    date: @js($dateLabel),
                    time: @js($timeLabel),
                    tick() {
                        const now = new Date();
                        this.date = now.toLocaleDateString('pt-PT', { weekday: 'long', day: 'numeric', month: 'long' });
                        this.time = now.toLocaleTimeString('pt-PT', { hour: '2-digit', minute: '2-digit' });
                    },
                }"
                x-init="tick(); setInterval(() => tick(), 1000)"
            >
                <span class="flex size-9 items-center justify-center rounded-xl bg-amber-100 text-amber-800">
                    <flux:icon.clock class="size-4" />
                </span>
                <span class="text-right">
                    <span class="hidden capitalize sm:block">
                        <span class="block text-[11px] font-medium text-stone-500" x-text="date">{{ $dateLabel }}</span>
                    </span>
                    <span class="block text-sm font-semibold tabular-nums text-stone-950" x-text="time">{{ $timeLabel }}</span>
                </span>
            </div>

            @auth
                <div class="flex min-w-0 items-center overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
                    <a
                        href="{{ route('profile.edit') }}"
                        wire:navigate
                        title="Abrir o meu perfil"
                        class="flex min-w-0 items-center gap-2 py-1.5 pl-1.5 pr-3 transition hover:bg-amber-50"
                    >
                        <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-stone-950 text-xs font-bold text-amber-200">{{ auth()->user()->initials() }}</span>
                        <span class="min-w-0 text-left">
                            <span class="block text-[10px] font-semibold uppercase tracking-[0.16em] text-stone-400">Perfil</span>
                            <span class="block max-w-28 truncate text-sm font-semibold text-stone-950 sm:max-w-40">{{ auth()->user()->name }}</span>
                        </span>
                    </a>
                    <flux:dropdown position="bottom" align="end">
                        <button
                            type="button"
                            class="flex h-full items-center border-l border-stone-200 px-2.5 text-stone-500 transition hover:bg-amber-50 hover:text-stone-950"
                            aria-label="Abrir menu da conta"
                        >
                            <flux:icon.chevron-down class="size-4" />
                        </button>
                        <flux:menu>
                            <flux:menu.item :href="route('profile.edit')" icon="user" wire:navigate>O meu perfil</flux:menu.item>
                            <flux:menu.item :href="route('account')" icon="identification" wire:navigate>A minha conta</flux:menu.item>
                            <flux:menu.separator />
                            <form method="POST" action="{{ route('logout') }}" class="w-full">
                                @csrf
                                <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                                    Terminar sessão
                                </flux:menu.item>
                            </form>
                        </flux:menu>
                    </flux:dropdown>
                </div>
            @else
                <a href="{{ route('login') }}" wire:navigate class="rounded-xl px-3 py-2 text-sm font-semibold text-stone-700 transition hover:bg-stone-50">Entrar</a>
                <a href="{{ route('register') }}" wire:navigate class="rounded-xl bg-stone-950 px-3 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-400 hover:text-stone-950">Criar conta</a>
            @endauth
        </div>
    </div>
</header>
