<x-layouts::app.sidebar :title="$title ?? 'Administração'">
    <flux:main class="bg-stone-50/70 dark:bg-zinc-900">
        {{ $slot }}
    </flux:main>
</x-layouts::app.sidebar>
