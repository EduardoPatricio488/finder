<div class="min-h-screen bg-[#fafaf8] text-zinc-950 dark:bg-zinc-950 dark:text-white">
    <div class="mx-auto max-w-6xl px-5 py-10 sm:px-8 lg:py-16">
        <a href="{{ request()->route('site') ? route('site.public', ['site' => request()->route('site'), 'preview' => request()->boolean('preview') ? 1 : null]) : route('site.public', ['site' => $product->site, 'preview' => request()->boolean('preview') ? 1 : null]) }}"
           class="mb-8 inline-flex items-center gap-2 text-sm font-semibold text-zinc-600 transition hover:text-zinc-950 dark:text-zinc-400 dark:hover:text-white">
            ← Voltar à loja
        </a>

        <div class="grid gap-10 lg:grid-cols-2 lg:items-start">
            <div class="overflow-hidden rounded-3xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                @php
                    $photos = is_array($product->images) ? $product->images : [];
                    $photo = $photos[0] ?? $product->image_url;
                    $photoUrl = $photo
                        ? (str_starts_with((string) $photo, 'http://') || str_starts_with((string) $photo, 'https://')
                            ? $photo
                            : asset('storage/'.ltrim((string) $photo, '/')))
                        : null;
                @endphp

                <div class="aspect-square bg-zinc-100 dark:bg-zinc-800">
                    @if($photoUrl)
                        <img src="{{ $photoUrl }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                    @else
                        <div class="flex h-full items-center justify-center text-zinc-400">Sem imagem</div>
                    @endif
                </div>
            </div>

            <div class="lg:pt-4">
                <p class="text-sm font-semibold uppercase tracking-wider" style="color: {{ $product->site->primary_color ?? '#635bff' }}">
                    {{ $product->category?->name ?? 'Produto' }}
                </p>

                <h1 class="mt-3 text-4xl font-black tracking-tight sm:text-5xl">{{ $product->name }}</h1>

                <p class="mt-5 text-3xl font-black" style="color: {{ $product->site->primary_color ?? '#635bff' }}">
                    {{ number_format((float) $product->price, 2, ',', '.') }} €
                </p>

                @if($product->description)
                    <div class="mt-8 border-t border-zinc-200 pt-8 text-base leading-7 text-zinc-600 dark:border-zinc-800 dark:text-zinc-300">
                        {!! nl2br(e($product->description)) !!}
                    </div>
                @endif

                <div class="mt-8 rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-zinc-500">Disponibilidade</span>
                        <span class="font-semibold {{ $product->stock > 0 ? 'text-emerald-600' : 'text-red-600' }}">
                            {{ $product->stock > 0 ? 'Em stock' : 'Esgotado' }}
                        </span>
                    </div>
                </div>

                @if(!$product->is_active)
                    <div class="mt-5 rounded-2xl bg-amber-50 p-4 text-sm font-medium text-amber-800 dark:bg-amber-950/30 dark:text-amber-300">
                        Este produto não está atualmente disponível.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
