<div class="mx-auto max-w-7xl space-y-6 p-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div><p class="text-sm font-semibold text-indigo-600">{{ $site->name }}</p><h1 class="text-3xl font-bold tracking-tight">Biblioteca de media</h1><p class="mt-1 text-sm text-zinc-500">Imagens e ficheiros deste website, isolados por tenant.</p></div>
        <a href="{{ route('builder.edit', $site) }}" wire:navigate class="rounded-xl border border-zinc-200 px-4 py-2 text-sm font-semibold dark:border-zinc-700">Voltar ao editor</a>
    </div>
    @if(session('status'))<div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('status') }}</div>@endif
    <div class="grid gap-6 lg:grid-cols-[360px_1fr]">
        <form wire:submit="uploadMedia" class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <h2 class="font-semibold">Adicionar ficheiro</h2>
            <input type="file" wire:model="upload" accept="image/jpeg,image/png,image/webp,image/gif,image/svg+xml" class="mt-4 block w-full text-sm">
            @error('upload')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
            <input wire:model="altText" placeholder="Texto alternativo" class="mt-4 w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-950">
            <button class="mt-4 w-full rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white" wire:loading.attr="disabled">Carregar</button>
        </form>
        <div class="space-y-4">
            <input wire:model.live.debounce.300ms="search" placeholder="Pesquisar ficheiros..." class="w-full rounded-xl border border-zinc-200 bg-white px-4 py-3 text-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @forelse($media as $item)
                    <article class="overflow-hidden rounded-2xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="aspect-[4/3] bg-zinc-100 dark:bg-zinc-950"><img src="{{ Storage::disk($item->disk ?: 'public')->url($item->path) }}" alt="{{ $item->alt_text }}" class="h-full w-full object-cover" loading="lazy"></div>
                        <div class="p-4"><p class="truncate text-sm font-semibold">{{ $item->original_name }}</p><p class="mt-1 text-xs text-zinc-500">{{ number_format($item->size / 1024, 1) }} KB @if($item->width) · {{ $item->width }}×{{ $item->height }}@endif</p><button wire:click="deleteMedia({{ $item->id }})" wire:confirm="Eliminar este ficheiro?" class="mt-3 text-xs font-semibold text-red-600">Eliminar</button></div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-zinc-300 p-10 text-center text-sm text-zinc-500 sm:col-span-2 xl:col-span-3">Ainda não existem ficheiros.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
