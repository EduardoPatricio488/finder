<div class="mx-auto max-w-4xl space-y-8 px-4 py-6 sm:px-6 lg:px-8">
    <div>
        <p class="text-sm font-medium uppercase tracking-[0.18em] text-amber-700">Catálogo</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-stone-950 dark:text-white">Categorias</h1>
        <p class="mt-2 text-sm text-stone-600 dark:text-zinc-400">Organize os produtos para facilitar a gestão da loja.</p>
    </div>

    @if (session('status'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">
            {{ session('status') }}
        </div>
    @endif

    <form wire:submit="save" class="rounded-xl border border-stone-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="space-y-2">
            <label for="category-name" class="block text-sm font-medium text-stone-700 dark:text-zinc-200">
                Nome da categoria
            </label>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-start">
                <div class="min-w-0 flex-1">
                    <input
                        id="category-name"
                        name="categoryName"
                        wire:model="categoryName"
                        type="text"
                        maxlength="255"
                        autocomplete="off"
                        placeholder="Ex.: Roupa, Calçado, Acessórios..."
                        class="block min-h-11 w-full rounded-lg border border-stone-300 bg-white px-3 py-2.5 text-sm text-stone-900 outline-none transition focus:border-stone-500 focus:ring-2 focus:ring-stone-200 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:focus:border-zinc-500 dark:focus:ring-zinc-800"
                    >

                    @error('categoryName')
                        <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="save"
                    class="min-h-11 shrink-0 rounded-lg bg-stone-950 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-stone-800 disabled:cursor-not-allowed disabled:opacity-60 dark:bg-white dark:text-zinc-950 dark:hover:bg-zinc-200"
                >
                    <span wire:loading.remove wire:target="save">Adicionar categoria</span>
                    <span wire:loading wire:target="save">A adicionar...</span>
                </button>
            </div>
        </div>
    </form>

    <section class="divide-y divide-stone-100 rounded-xl border border-stone-200 bg-white shadow-sm dark:divide-zinc-800 dark:border-zinc-800 dark:bg-zinc-900">
        @forelse ($categories as $category)
            <div class="flex items-center justify-between gap-4 px-5 py-4">
                <div class="min-w-0">
                    <p class="font-medium text-stone-800 dark:text-zinc-100">{{ $category->name }}</p>
                    <p class="text-xs text-stone-500 dark:text-zinc-500">{{ $category->products_count }} produto(s)</p>
                </div>

                <button
                    type="button"
                    wire:click="delete({{ $category->id }})"
                    wire:confirm="Eliminar esta categoria?"
                    class="shrink-0 text-sm font-medium text-red-600 transition hover:text-red-700"
                >
                    Eliminar
                </button>
            </div>
        @empty
            <p class="px-5 py-12 text-center text-sm text-stone-500 dark:text-zinc-500">Nenhuma categoria criada.</p>
        @endforelse
    </section>
</div>
