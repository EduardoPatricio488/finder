@props([
    'title',
    'description',
])

<div class="flex w-full flex-col gap-2">
    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-amber-700">Finder</p>
    <h1 class="text-2xl font-semibold tracking-tight text-stone-950">{{ $title }}</h1>
    <p class="text-sm leading-6 text-stone-600">{{ $description }}</p>
</div>
