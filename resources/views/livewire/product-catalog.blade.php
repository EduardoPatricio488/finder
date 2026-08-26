<main class="store-reveal bg-[#f7f4ee]">
    <button type="button" wire:click="$set('cartOpen', true)" aria-label="Abrir carrinho" title="Abrir carrinho" class="fixed bottom-6 right-6 z-30 flex size-16 items-center justify-center rounded-full bg-stone-950 text-2xl text-white shadow-xl ring-4 ring-white transition hover:-translate-y-1 hover:bg-amber-400 hover:text-stone-950">
        <span aria-hidden="true">🛒</span>
        @if (count($cart) > 0)<span class="absolute -right-1 -top-1 flex size-6 items-center justify-center rounded-full bg-amber-300 text-xs font-bold text-stone-950">{{ array_sum($cart) }}</span>@endif
    </button>

    @php
        $productUrl = fn ($product): string => request()->route('site') ? route('sites.products.show', [$site, $product]) : route('products.show', $product);
    @endphp

    <section class="mx-auto max-w-7xl px-6 pb-8 pt-8 lg:px-10 lg:pt-10">
        <div class="overflow-hidden rounded-3xl bg-stone-950 px-6 py-8 text-white shadow-xl sm:px-10 sm:py-10">
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-amber-300">A sua loja de todos os dias</p>
            <h1 class="mt-3 max-w-2xl text-4xl font-semibold tracking-tight sm:text-5xl">Peças com lugar na sua casa.</h1>
            <p class="mt-4 max-w-xl text-base leading-7 text-stone-300">Descubra produtos escolhidos para tornar os momentos simples ainda melhores.</p>
        </div>

        <div class="mt-8 flex flex-col gap-3 md:flex-row">
            <label class="flex min-w-0 flex-1 items-center gap-3 rounded-2xl border border-stone-300 bg-white px-4 py-3 shadow-sm transition focus-within:border-amber-400 focus-within:ring-4 focus-within:ring-amber-100">
                <span class="text-stone-400">⌕</span>
                <input wire:model.live.debounce.300ms="search" type="search" placeholder="Pesquisar por nome, categoria, SKU ou tags" class="min-w-0 flex-1 border-0 bg-transparent text-sm outline-none placeholder:text-stone-400 focus:ring-0">
            </label>
            <select wire:model.live="category" class="rounded-2xl border border-stone-300 bg-white px-4 py-3 text-sm text-stone-700 outline-none transition hover:border-amber-400 focus:border-amber-600">
                <option value="">Todas as categorias</option>
                @foreach ($categories as $item)<option value="{{ $item->slug }}">{{ $item->name }}</option>@endforeach
            </select>
        </div>

        @if ($categories->isNotEmpty())
            <div class="mt-4 flex gap-2 overflow-x-auto pb-1">
                <button type="button" wire:click="$set('category', '')" class="{{ $category === '' ? 'bg-stone-950 text-white' : 'bg-white text-stone-700 hover:border-amber-300 hover:bg-amber-50' }} shrink-0 rounded-full border border-stone-200 px-4 py-2 text-sm font-semibold shadow-sm transition">Todas</button>
                @foreach ($categories as $item)
                    <button type="button" wire:key="category-chip-{{ $item->id }}" wire:click="$set('category', '{{ $item->slug }}')" class="{{ $category === $item->slug ? 'bg-stone-950 text-white' : 'bg-white text-stone-700 hover:border-amber-300 hover:bg-amber-50' }} shrink-0 rounded-full border border-stone-200 px-4 py-2 text-sm font-semibold shadow-sm transition">{{ $item->name }}</button>
                @endforeach
            </div>
        @endif

        <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <label class="rounded-2xl border border-stone-300 bg-white px-3 py-2 text-xs font-medium text-stone-500 transition hover:border-amber-300">Preço mínimo<input wire:model.live="minPrice" type="number" min="0" step="0.01" placeholder="0,00 €" class="mt-1 w-full border-0 p-0 text-sm text-stone-900 outline-none focus:ring-0"></label>
            <label class="rounded-2xl border border-stone-300 bg-white px-3 py-2 text-xs font-medium text-stone-500 transition hover:border-amber-300">Preço máximo<input wire:model.live="maxPrice" type="number" min="0" step="0.01" placeholder="Sem limite" class="mt-1 w-full border-0 p-0 text-sm text-stone-900 outline-none focus:ring-0"></label>
            <select wire:model.live="availability" class="rounded-2xl border border-stone-300 bg-white px-3 py-2 text-sm text-stone-700 outline-none hover:border-amber-400 focus:border-amber-600"><option value="">Disponibilidade</option><option value="disponivel">Em stock</option><option value="esgotado">Esgotado</option></select>
            <select wire:model.live="minRating" class="rounded-2xl border border-stone-300 bg-white px-3 py-2 text-sm text-stone-700 outline-none hover:border-amber-400 focus:border-amber-600"><option value="">Avaliação</option><option value="4">4 estrelas ou mais</option><option value="3">3 estrelas ou mais</option><option value="2">2 estrelas ou mais</option></select>
        </div>
    </section>

    <section class="relative mx-auto max-w-7xl px-6 pb-16 lg:px-10">
        <div class="mb-5 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-stone-950">Em destaque</h2>
            <span class="text-sm text-stone-500">{{ $products->count() }} produtos</span>
        </div>
        <div wire:loading.flex class="absolute inset-x-6 top-12 z-10 items-center justify-center lg:inset-x-10">
            <span class="rounded-full bg-white px-4 py-2 text-sm font-semibold text-stone-700 shadow-lg">A atualizar a coleção…</span>
        </div>
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3" wire:loading.class="opacity-50">
        @forelse ($products as $product)
            <article wire:key="product-{{ $product->id }}" class="store-card group overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-sm">
                <a href="{{ $productUrl($product) }}" wire:navigate class="relative block overflow-hidden">
                    @if ($product->image_url)
                        <img src="{{ asset('storage/'.$product->image_url) }}" alt="{{ $product->name }}" class="aspect-[4/3] w-full object-cover transition duration-500 group-hover:scale-105">
                    @else
                        <div class="flex aspect-[4/3] items-center justify-center bg-amber-50 text-sm text-amber-700">Imagem em breve</div>
                    @endif
                    <span class="absolute inset-x-4 bottom-4 translate-y-2 rounded-full bg-white/95 px-3 py-2 text-center text-xs font-semibold text-stone-950 opacity-0 shadow-sm transition group-hover:translate-y-0 group-hover:opacity-100">Ver produto</span>
                </a>
                <div class="p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-amber-700">{{ $product->category?->name }}</p>
                            <a href="{{ $productUrl($product) }}" wire:navigate class="mt-2 block text-xl font-semibold hover:text-amber-700">{{ $product->name }}</a>
                        </div>
                        <span class="shrink-0 text-right">
                            @if ($this->salePrice($product) < (float) $product->price)
                                <span class="block text-xs text-stone-400 line-through">€ {{ number_format($product->price, 2, ',', '.') }}</span>
                                <strong class="text-base font-semibold text-red-700">€ {{ number_format($this->salePrice($product), 2, ',', '.') }}</strong>
                            @else
                                <strong class="text-base font-semibold">€ {{ number_format($product->price, 2, ',', '.') }}</strong>
                            @endif
                        </span>
                    </div>
                    <div class="mt-2 flex items-center gap-2 text-xs"><span class="text-amber-500">{{ str_repeat('★', (int) round($product->reviews_avg_rating ?? 0)) }}{{ str_repeat('☆', 5 - (int) round($product->reviews_avg_rating ?? 0)) }}</span><span class="text-stone-500">{{ $product->reviews_count ?? 0 }} avaliações</span></div>
                    <p class="mt-2 line-clamp-2 text-sm leading-6 text-stone-600">{{ $product->description }}</p>
                    <div class="mt-5 flex items-center gap-2">
                        @auth<button type="button" wire:click="toggleFavorite({{ $product->id }})" class="rounded-xl border border-stone-200 px-3 py-2 text-sm text-stone-700 transition hover:border-amber-400 hover:bg-amber-50" aria-label="Adicionar aos favoritos">♡</button>@endauth
                        <button type="button" wire:click="addToCart({{ $product->id }})" class="flex-1 rounded-xl bg-stone-950 px-4 py-3 text-sm font-semibold text-white transition hover:bg-amber-400 hover:text-stone-950">Adicionar ao carrinho</button>
                    </div>
                </div>
            </article>
        @empty
            <div class="col-span-full rounded-xl border border-dashed border-stone-300 bg-white/60 px-6 py-16 text-center"><p class="font-semibold text-stone-800">Não encontrámos produtos.</p><p class="mt-2 text-sm text-stone-500">Experimente outra pesquisa ou categoria.</p></div>
        @endforelse
        </div>
    </section>

    @if ($cartOpen)
        <div class="fixed inset-0 z-40 bg-stone-950/30" wire:click="$set('cartOpen', false)"></div>
        <aside class="fixed inset-y-0 right-0 z-50 flex w-full max-w-md flex-col bg-white shadow-2xl">
            <header class="flex items-center justify-between border-b border-stone-200 px-6 py-5"><div><p class="text-xs font-semibold uppercase tracking-wider text-amber-700">O seu pedido</p><h2 class="mt-1 text-xl font-semibold">Carrinho</h2></div><button type="button" wire:click="$set('cartOpen', false)" aria-label="Fechar carrinho" class="text-2xl text-stone-500 hover:text-stone-950">&times;</button></header>
            <div class="flex-1 overflow-y-auto px-6 py-5">
                @forelse ($this->cartProducts as $product)
                    <div class="flex items-center gap-4 border-b border-stone-100 py-4"><div class="min-w-0 flex-1"><p class="truncate font-medium">{{ $product->name }}</p><p class="mt-1 text-sm text-stone-500">€ {{ number_format($this->salePrice($product), 2, ',', '.') }}</p></div><div class="flex items-center gap-2 rounded-lg bg-stone-100 px-2 py-1"><button type="button" wire:click="decreaseQuantity({{ $product->id }})" class="size-6 text-stone-600">−</button><span class="w-5 text-center text-sm">{{ $cart[$product->id] }}</span><button type="button" wire:click="increaseQuantity({{ $product->id }})" class="size-6 text-stone-600">+</button></div></div>
                @empty
                    <p class="py-16 text-center text-sm text-stone-500">O seu carrinho está vazio.</p>
                @endforelse
            </div>
            <footer class="border-t border-stone-200 px-6 py-5"><form wire:submit="applyCoupon" class="mb-5 flex gap-2"><input wire:model="couponCode" type="text" placeholder="Código do cupão" class="min-w-0 flex-1 rounded-lg border-stone-300 px-3 py-2 text-sm uppercase"><button type="submit" class="rounded-lg border border-stone-300 px-3 py-2 text-sm font-semibold hover:bg-stone-50">Aplicar</button></form>@error('couponCode')<p class="-mt-4 mb-4 text-xs text-red-600">{{ $message }}</p>@enderror@if ($couponMessage)<p class="-mt-4 mb-4 text-xs text-emerald-700">{{ $couponMessage }}</p>@endif<div class="space-y-2 text-sm"><div class="flex justify-between"><span class="text-stone-500">Subtotal</span><span>€ {{ number_format($this->cartSubtotal, 2, ',', '.') }}</span></div>@if ($this->couponDiscount > 0)<div class="flex justify-between text-emerald-700"><span>Cupão ({{ $appliedCouponCode }})</span><span>- € {{ number_format($this->couponDiscount, 2, ',', '.') }}</span></div>@endif<div class="flex justify-between"><span class="text-stone-500">IVA (23%)</span><span>€ {{ number_format($this->vat, 2, ',', '.') }}</span></div><div class="flex justify-between"><span class="text-stone-500">Portes</span><span>{{ $this->shipping === 0.0 ? 'Grátis' : '€ '.number_format($this->shipping, 2, ',', '.') }}</span></div></div><div class="mt-4 flex justify-between border-t border-stone-200 pt-4 text-base font-semibold"><span>Total</span><span>€ {{ number_format($this->cartTotal, 2, ',', '.') }}</span></div><button type="button" wire:click="openCheckout" class="mt-4 w-full rounded-lg bg-stone-950 px-4 py-3 text-sm font-semibold text-white hover:bg-stone-700" @disabled(count($cart) === 0)>Finalizar pedido</button></footer>
        </aside>
    @endif

    @if ($completedOrderNumber)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/40 px-6" wire:click="$set('completedOrderNumber', null)">
            <section class="w-full max-w-md rounded-2xl bg-white p-8 text-center shadow-2xl" wire:click.stop>
                <div class="mx-auto flex size-14 items-center justify-center rounded-full bg-emerald-100 text-2xl text-emerald-700">✓</div>
                <p class="mt-5 text-xs font-semibold uppercase tracking-[0.18em] text-amber-700">Pedido recebido</p>
                <h2 class="mt-2 text-2xl font-semibold">Obrigado pela sua compra.</h2>
                <p class="mt-3 text-sm leading-6 text-stone-600">A sua encomenda foi registada com o número <strong class="text-stone-950">{{ $completedOrderNumber }}</strong>. Entraremos em contacto para confirmar os próximos passos.</p>
                <button type="button" wire:click="$set('completedOrderNumber', null)" class="mt-6 w-full rounded-lg bg-stone-950 px-4 py-3 text-sm font-semibold text-white hover:bg-stone-700">Continuar a comprar</button>
            </section>
        </div>
    @endif

    @if ($checkoutOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/40 px-6 py-8" wire:click="$set('checkoutOpen', false)">
            <section class="max-h-full w-full max-w-lg overflow-y-auto rounded-2xl bg-white p-6 shadow-2xl sm:p-8" wire:click.stop>
                <div class="flex items-start justify-between gap-5"><div><p class="text-xs font-semibold uppercase tracking-[0.18em] text-amber-700">Último passo</p><h2 class="mt-2 text-2xl font-semibold">Finalizar pedido</h2></div><button type="button" wire:click="$set('checkoutOpen', false)" aria-label="Fechar checkout" class="text-2xl text-stone-400 hover:text-stone-950">&times;</button></div>
                <form wire:submit="checkout" class="mt-6 space-y-4">
                    <label class="block text-sm font-medium text-stone-700">Nome completo<input wire:model="customerName" type="text" class="mt-2 w-full rounded-lg border-stone-300 px-3 py-2.5 text-sm" required>@error('customerName')<span class="text-xs text-red-600">{{ $message }}</span>@enderror</label>
                    <label class="block text-sm font-medium text-stone-700">Email<input wire:model="customerEmail" type="email" class="mt-2 w-full rounded-lg border-stone-300 px-3 py-2.5 text-sm" required>@error('customerEmail')<span class="text-xs text-red-600">{{ $message }}</span>@enderror</label>
                    <label class="block text-sm font-medium text-stone-700">Telefone<input wire:model="customerPhone" type="tel" class="mt-2 w-full rounded-lg border-stone-300 px-3 py-2.5 text-sm" required>@error('customerPhone')<span class="text-xs text-red-600">{{ $message }}</span>@enderror</label>
                    <label class="block text-sm font-medium text-stone-700">Morada de entrega<textarea wire:model="deliveryAddress" rows="3" class="mt-2 w-full rounded-lg border-stone-300 px-3 py-2.5 text-sm" placeholder="Rua, número, código postal e localidade" required></textarea>@error('deliveryAddress')<span class="text-xs text-red-600">{{ $message }}</span>@enderror</label>
                    <label class="block text-sm font-medium text-stone-700">Método de pagamento<select wire:model="paymentMethod" class="mt-2 w-full rounded-lg border-stone-300 px-3 py-2.5 text-sm"><option value="mbway">MB WAY</option><option value="transferencia">Transferência bancária</option><option value="cartao">Cartão</option><option value="entrega">Pagamento na entrega</option></select></label>
                    <div class="rounded-lg bg-stone-50 p-4"><div class="flex justify-between text-sm"><span class="text-stone-500">Subtotal</span><span>€ {{ number_format($this->cartSubtotal, 2, ',', '.') }}</span></div>@if ($this->couponDiscount > 0)<div class="mt-2 flex justify-between text-sm text-emerald-700"><span>Cupão ({{ $appliedCouponCode }})</span><span>- € {{ number_format($this->couponDiscount, 2, ',', '.') }}</span></div>@endif<div class="mt-2 flex justify-between text-sm"><span class="text-stone-500">IVA (23%)</span><span>€ {{ number_format($this->vat, 2, ',', '.') }}</span></div><div class="mt-2 flex justify-between text-sm"><span class="text-stone-500">Portes</span><span>{{ $this->shipping === 0.0 ? 'Grátis' : '€ '.number_format($this->shipping, 2, ',', '.') }}</span></div><div class="mt-3 flex justify-between border-t border-stone-200 pt-3 font-semibold"><span>Total</span><span>€ {{ number_format($this->cartTotal, 2, ',', '.') }}</span></div></div>
                    <button type="submit" class="w-full rounded-lg bg-stone-950 px-4 py-3 text-sm font-semibold text-white hover:bg-stone-700" wire:loading.attr="disabled">Confirmar pedido</button>
                </form>
            </section>
        </div>
    @endif
</main>
