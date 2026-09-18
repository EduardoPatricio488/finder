@php $products = $products ?? collect(); @endphp
<div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
@forelse($products as $product)
    @php
        $productPhotos = is_array($product->images) ? $product->images : [];
        $productPhoto = $productPhotos[0] ?? $product->image_url;
        $productPhotoUrl = $productPhoto
            ? (str_starts_with((string) $productPhoto, 'http://') || str_starts_with((string) $productPhoto, 'https://')
                ? $productPhoto
                : asset('storage/'.ltrim((string) $productPhoto, '/')))
            : null;
    @endphp
    <a href="{{ route('site.products.show', ['site' => $site, 'product' => $product->slug, 'preview' => request()->boolean('preview') ? 1 : null]) }}" class="group overflow-hidden rounded-3xl border border-black/5 bg-[#fafaf8] transition hover:-translate-y-1 hover:shadow-xl">
        <div class="aspect-square overflow-hidden bg-zinc-100">
            @if($productPhotoUrl)<img src="{{ $productPhotoUrl }}" alt="{{ $product->name }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">@else<div class="flex h-full items-center justify-center text-sm text-zinc-400">{{ $product->name }}</div>@endif
        </div>
        <div class="p-5"><p class="font-bold">{{ $product->name }}</p><p class="mt-2 text-sm text-zinc-500">{{ Str::limit((string) $product->description, 90) }}</p><p class="mt-4 text-lg font-black" style="color:var(--finder-primary)">{{ number_format((float)$product->price, 2, ',', '.') }} €</p>
            @if($product->stock > 0)
                <button type="button" wire:click.prevent="addToCart({{ $product->id }})" class="mt-5 w-full rounded-2xl px-4 py-3 text-sm font-bold text-white transition hover:opacity-90" style="background:var(--finder-primary)">Adicionar ao carrinho</button>
            @else
                <span class="mt-5 block rounded-2xl bg-zinc-100 px-4 py-3 text-center text-sm font-bold text-zinc-500">Esgotado</span>
            @endif
        </div>
    </a>
@empty
    <div class="rounded-3xl border border-dashed border-zinc-300 p-8 text-sm text-zinc-500 sm:col-span-2 lg:col-span-3">Ainda não existem produtos activos nesta loja.</div>
@endforelse
</div>