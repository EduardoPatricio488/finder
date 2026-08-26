<main class="store-reveal bg-[#f7f4ee]">
    <section class="mx-auto max-w-6xl px-6 py-10 lg:px-10 lg:py-14">
        <a href="{{ route('products') }}" wire:navigate class="inline-flex items-center gap-2 rounded-full border border-stone-200 bg-white px-3 py-2 text-sm font-semibold text-stone-700 shadow-sm transition hover:-translate-y-0.5 hover:border-amber-300 hover:bg-amber-50">&larr; Voltar aos produtos</a>
        <div class="mt-8 grid gap-12 lg:grid-cols-2 lg:items-start">
            <div class="overflow-hidden rounded-3xl bg-amber-50 shadow-sm">
                @if ($product->image_url)
                    <img src="{{ asset('storage/'.$product->image_url) }}" alt="{{ $product->name }}" class="aspect-square w-full object-cover">
                @else
                    <div class="flex aspect-square items-center justify-center text-sm text-amber-700">Imagem em breve</div>
                @endif
            </div>
            <div class="rounded-3xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-amber-700">{{ $product->category?->name }}</p>
                <h1 class="mt-3 text-4xl font-semibold tracking-tight text-stone-950">{{ $product->name }}</h1>
                <div class="mt-4 flex items-center gap-3">
                    <span class="text-amber-600">{{ str_repeat('★', (int) round($averageRating)) }}{{ str_repeat('☆', 5 - (int) round($averageRating)) }}</span>
                    <span class="text-sm text-stone-500">{{ number_format($averageRating, 1, ',', '.') }}/5 · {{ $reviews->count() }} {{ $reviews->count() === 1 ? 'avaliação' : 'avaliações' }}</span>
                </div>
                <p class="mt-6 text-3xl font-semibold">€ {{ number_format($product->price, 2, ',', '.') }}</p>
                <p class="mt-6 leading-7 text-stone-600">{{ $product->description }}</p>
                <a href="{{ route('products') }}" wire:navigate class="mt-8 inline-flex rounded-xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-amber-400 hover:text-stone-950">Escolher este produto</a>
            </div>
        </div>
    </section>
    <section class="border-t border-stone-200 bg-white">
        <div class="mx-auto grid max-w-6xl gap-12 px-6 py-14 lg:grid-cols-[0.8fr_1.2fr] lg:px-10">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-amber-700">Opiniões reais</p>
                <h2 class="mt-3 text-3xl font-semibold">O que dizem os clientes</h2>
                <p class="mt-3 text-sm leading-6 text-stone-500">As avaliações são publicadas apenas por clientes que compraram este produto.</p>
            </div>
            <div class="space-y-8">
                @auth
                    @if ($canReview)
                        <form wire:submit="submitReview" class="rounded-2xl border border-amber-200 bg-amber-50 p-5">
                            <h3 class="font-semibold">Partilhe a sua experiência</h3>
                            <div class="mt-4 flex gap-1">
                                @for ($star = 1; $star <= 5; $star++)
                                    <button type="button" wire:click="$set('rating', {{ $star }})" aria-label="{{ $star }} estrelas" class="text-2xl {{ $star <= $rating ? 'text-amber-500' : 'text-stone-300' }}">★</button>
                                @endfor
                            </div>
                            <textarea wire:model="comment" rows="3" placeholder="Escreva o seu comentário" class="mt-4 w-full rounded-lg border-stone-300 text-sm" required></textarea>
                            @error('comment')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            <button type="submit" class="mt-3 rounded-lg bg-stone-950 px-4 py-2.5 text-sm font-semibold text-white">Publicar avaliação</button>
                        </form>
                    @else
                        <p class="rounded-lg bg-stone-100 p-4 text-sm text-stone-600">Compre este produto para poder deixar uma avaliação.</p>
                    @endif
                    @if (session('review-status'))
                        <p class="text-sm text-emerald-700">{{ session('review-status') }}</p>
                    @endif
                @else
                    <p class="rounded-lg bg-stone-100 p-4 text-sm text-stone-600">Entre na sua conta depois de comprar este produto para deixar uma avaliação.</p>
                @endauth
                @forelse ($reviews as $review)
                    <article class="rounded-2xl border border-stone-100 bg-[#f7f4ee] p-5">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="font-semibold">{{ $review->user->name }}</p>
                                <p class="text-xs text-stone-500">Cliente verificado</p>
                            </div>
                            <span class="text-amber-500">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</span>
                        </div>
                        <p class="mt-3 text-sm leading-6 text-stone-600">{{ $review->comment }}</p>
                    </article>
                @empty
                    <p class="text-sm text-stone-500">Ainda não existem avaliações para este produto.</p>
                @endforelse
            </div>
        </div>
    </section>
</main>
