@props([
    'inverted' => false,
])

<a
    href="{{ route('home') }}"
    wire:navigate
    {{ $attributes->class('flex items-center gap-3') }}
>
    <span @class([
        'flex size-10 items-center justify-center rounded-2xl text-sm font-bold',
        'bg-white text-stone-950' => $inverted,
        'bg-stone-950 text-amber-200' => ! $inverted,
    ])>F</span>
    <span>
        <span @class([
            'block text-sm font-semibold tracking-tight',
            'text-white' => $inverted,
            'text-stone-950' => ! $inverted,
        ])>Finder</span>
        <span @class([
            'block text-[10px] font-semibold uppercase tracking-[0.18em]',
            'text-white/55' => $inverted,
            'text-stone-400' => ! $inverted,
        ])>Gestão de sites</span>
    </span>
</a>
