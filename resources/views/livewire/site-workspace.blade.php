<div class="min-h-screen">
    <header class="border-b border-stone-900/10 bg-[#f4f1ea]/90 backdrop-blur-xl">
        <div class="mx-auto flex h-[4.75rem] max-w-7xl items-center justify-between gap-3 px-6 lg:px-12">
            <a href="{{ route('home') }}" wire:navigate class="inline-flex items-center gap-2 rounded-full border border-stone-200 bg-white px-3 py-2 text-sm font-semibold text-stone-800 shadow-sm transition hover:border-amber-300 hover:bg-amber-50">
                <flux:icon.arrow-left class="size-4" />
                Todos os sites
            </a>
        </div>
    </header>

    <main class="mx-auto grid min-h-[calc(100vh-4.75rem)] max-w-7xl items-center gap-12 px-6 py-16 lg:grid-cols-2 lg:px-12">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-amber-700">{{ $site->category_label }}</p>
            <h1 class="mt-4 text-5xl font-semibold tracking-tight">{{ $site->name }}</h1>
            <p class="mt-5 max-w-lg text-lg leading-8 text-stone-600">{{ $site->tagline ?? $site->description }}</p>
            <p class="mt-8 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950">Este site ainda está a ser preparado. Volte mais tarde ou edite-o a partir da página principal.</p>
        </div>
        <div class="min-h-80 rounded-3xl bg-stone-950 p-10 text-white">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-amber-300">Em construção</p>
            <p class="mt-16 text-4xl font-semibold leading-tight">O essencial deste projeto vai aparecer aqui.</p>
        </div>
    </main>
</div>
