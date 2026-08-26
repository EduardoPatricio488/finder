@php
    $showSidebar = auth()->check();
@endphp

<div
    class="min-h-screen bg-[#f7f4ee] text-stone-950"
    @if ($showSidebar)
        x-data="{ menuOpen: false }"
        @keydown.escape.window="menuOpen = false"
    @endif
>
    @if ($showSidebar)
        <div
            x-show="menuOpen"
            x-cloak
            x-transition.opacity
            class="fixed inset-0 z-40 bg-stone-950/40 lg:hidden"
            @click="menuOpen = false"
        ></div>
        <x-customer-sidebar />
    @endif

    <x-store-header :with-sidebar="$showSidebar">
        @if (isset($actions))
            <x-slot:actions>{{ $actions }}</x-slot:actions>
        @endif
    </x-store-header>

    <div class="{{ $showSidebar ? 'pt-[4.75rem] lg:pl-72' : 'pt-[4.75rem]' }}">
        {{ $slot }}
    </div>
</div>
