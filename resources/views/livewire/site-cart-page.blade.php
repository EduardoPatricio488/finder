@php
    $accent = $site->theme['primary'] ?? $site->primary_color ?? '#4f46e5';
    $homeUrl = route('site.public', ['site' => $site, 'pageSlug' => null, 'preview' => $preview ? 1 : null]);
    $visibleSections = $page->sections->where('is_visible', true)->sortBy('sort_order');
    $pageTitle = $visibleSections->firstWhere('type', 'hero')?->content['title'] ?? $page->name;
    $pageIntro = $visibleSections->firstWhere('type', 'hero')?->content['body'] ?? 'Revê os teus produtos antes de concluir a encomenda.';
@endphp

<div class="min-h-screen bg-zinc-50 text-zinc-900" style="--finder-primary: {{ $accent }}">
    <header class="border-b border-zinc-200 bg-white">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-5 sm:px-6">
            <a href="{{ $homeUrl }}" class="flex min-w-0 items-center gap-3">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-xl text-sm font-black text-white" style="background:{{ $accent }}">{{ mb_strtoupper(mb_substr($site->name, 0, 1)) }}</span>
                <span class="truncate font-extrabold tracking-tight">{{ $site->name }}</span>
            </a>
            <a href="{{ $homeUrl }}" class="rounded-xl px-3 py-2 text-sm font-semibold text-zinc-600 hover:bg-zinc-100">Continuar a comprar</a>
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:py-14">
        <div class="mb-8">
            <p class="text-xs font-bold uppercase tracking-[.2em]" style="color:{{ $accent }}">A tua encomenda</p>
            <h1 class="mt-2 text-3xl font-black tracking-tight sm:text-4xl">{{ $pageTitle }}</h1>
            @if($pageIntro)
                <p class="mt-3 max-w-2xl leading-7 text-zinc-500">{{ $pageIntro }}</p>
            @endif
            @foreach($visibleSections->where('type', 'text') as $section)
                @if(data_get($section->content, 'title'))
                    <h2 class="mt-6 text-xl font-bold">{{ data_get($section->content, 'title') }}</h2>
                @endif
                @if(data_get($section->content, 'body'))
                    <p class="mt-2 max-w-3xl whitespace-pre-line leading-7 text-zinc-600">{{ data_get($section->content, 'body') }}</p>
                @endif
            @endforeach
        </div>

        @if($completedOrderNumber)
            <section class="rounded-3xl border border-emerald-200 bg-emerald-50 p-8 text-center">
                <div class="mx-auto flex size-14 items-center justify-center rounded-full bg-emerald-100 text-2xl text-emerald-700">✓</div>
                <h2 class="mt-4 text-2xl font-extrabold text-emerald-950">Encomenda registada</h2>
                <p class="mt-2 text-sm text-emerald-800">O número da tua encomenda é <strong>{{ $completedOrderNumber }}</strong>.</p>
                <a href="{{ $homeUrl }}" class="mt-6 inline-flex rounded-xl px-5 py-3 text-sm font-bold text-white" style="background:{{ $accent }}">Voltar à loja</a>
            </section>
        @elseif($checkoutOpen)
            <section class="mx-auto max-w-2xl rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm sm:p-9">
                <button type="button" wire:click="$set('checkoutOpen', false)" class="mb-5 text-sm font-semibold text-zinc-500 hover:text-zinc-900">← Voltar ao carrinho</button>
                <h2 class="text-2xl font-extrabold">Dados da encomenda</h2>
                <p class="mt-2 text-sm text-zinc-500">Preenche os dados para registar a tua encomenda.</p>
                @error('cart')<div role="alert" class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $message }}</div>@enderror
                <form wire:submit="checkout" class="mt-7 space-y-5">
                    <div><label for="customerName" class="mb-1.5 block text-sm font-semibold">Nome completo</label><input id="customerName" wire:model="customerName" required autocomplete="name" class="w-full rounded-xl border border-zinc-200 px-4 py-3 text-sm">@error('customerName')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
                    <div><label for="customerEmail" class="mb-1.5 block text-sm font-semibold">Email</label><input id="customerEmail" type="email" wire:model="customerEmail" required autocomplete="email" class="w-full rounded-xl border border-zinc-200 px-4 py-3 text-sm">@error('customerEmail')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
                    <div><label for="customerPhone" class="mb-1.5 block text-sm font-semibold">Telefone</label><input id="customerPhone" wire:model="customerPhone" required autocomplete="tel" class="w-full rounded-xl border border-zinc-200 px-4 py-3 text-sm">@error('customerPhone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
                    <div><label for="deliveryAddress" class="mb-1.5 block text-sm font-semibold">Morada de entrega</label><textarea id="deliveryAddress" wire:model="deliveryAddress" required rows="3" autocomplete="street-address" class="w-full rounded-xl border border-zinc-200 px-4 py-3 text-sm"></textarea>@error('deliveryAddress')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
                    <div><label for="paymentMethod" class="mb-1.5 block text-sm font-semibold">Método de pagamento</label><select id="paymentMethod" wire:model="paymentMethod" required class="w-full rounded-xl border border-zinc-200 bg-white px-4 py-3 text-sm"><option value="mbway">MB WAY</option><option value="transferencia">Transferência bancária</option><option value="cartao">Cartão</option><option value="entrega">Pagamento na entrega</option></select></div>
                    <div class="flex items-center justify-between border-t border-zinc-100 pt-5"><span class="font-semibold">Total estimado</span><strong class="text-xl">€{{ number_format($this->cartTotal, 2, ',', '.') }}</strong></div>
                    <button type="submit" wire:loading.attr="disabled" wire:target="checkout" class="w-full rounded-xl px-5 py-3.5 text-sm font-bold text-white disabled:cursor-wait disabled:opacity-60" style="background:{{ $accent }}"><span wire:loading.remove wire:target="checkout">Confirmar encomenda</span><span wire:loading wire:target="checkout">A registar encomenda…</span></button>
                </form>
            </section>
        @elseif($this->cartProducts->isEmpty())
            <section class="rounded-3xl border border-dashed border-zinc-300 bg-white px-6 py-16 text-center">
                <div class="mx-auto flex size-16 items-center justify-center rounded-2xl bg-zinc-100 text-3xl">🛒</div>
                <h2 class="mt-5 text-xl font-extrabold">O teu carrinho está vazio</h2>
                <p class="mt-2 text-sm text-zinc-500">Explora a loja e adiciona os produtos de que gostas.</p>
                <a href="{{ $homeUrl }}" class="mt-6 inline-flex rounded-xl px-5 py-3 text-sm font-bold text-white" style="background:{{ $accent }}">Explorar loja</a>
            </section>
        @else
            <div class="grid gap-6 lg:grid-cols-[1fr_340px]">
                <section class="space-y-3">
                    @foreach($this->cartProducts as $product)
                        @php
                            $promotion = $product->promotions->first(fn ($p) => $p->starts_at <= now() && $p->ends_at >= now());
                            $unitPrice = $promotion ? ($promotion->discount_type === 'percentagem' ? max(0, (float) $product->price * (1 - ((float) $promotion->discount_value / 100))) : max(0, (float) $product->price - (float) $promotion->discount_value)) : (float) $product->price;
                        @endphp
                        <article wire:key="cart-product-{{ $product->id }}" class="flex flex-col gap-4 rounded-2xl border border-zinc-200 bg-white p-4 sm:flex-row sm:items-center sm:p-5">
                            <div class="flex-1">
                                <h2 class="font-bold">{{ $product->name }}</h2>
                                <p class="mt-1 text-sm text-zinc-500">€{{ number_format($unitPrice, 2, ',', '.') }} por unidade</p>
                                <p class="mt-2 text-sm font-bold" style="color:{{ $accent }}">Subtotal: €{{ number_format($unitPrice * ($cart[$product->id] ?? 0), 2, ',', '.') }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" wire:click="decreaseQuantity({{ $product->id }})" aria-label="Diminuir quantidade" class="flex size-9 items-center justify-center rounded-lg border border-zinc-200 font-bold hover:bg-zinc-50">−</button>
                                <span class="min-w-8 text-center text-sm font-bold">{{ $cart[$product->id] ?? 0 }}</span>
                                <button type="button" wire:click="increaseQuantity({{ $product->id }})" aria-label="Aumentar quantidade" @disabled(($cart[$product->id] ?? 0) >= $product->stock) class="flex size-9 items-center justify-center rounded-lg border border-zinc-200 font-bold hover:bg-zinc-50 disabled:cursor-not-allowed disabled:opacity-40">+</button>
                                <button type="button" wire:click="removeFromCart({{ $product->id }})" class="ml-2 text-sm font-semibold text-red-600 hover:text-red-800">Remover</button>
                            </div>
                        </article>
                    @endforeach
                </section>
                <aside class="h-fit rounded-2xl border border-zinc-200 bg-white p-6">
                    <h2 class="text-lg font-extrabold">Resumo da encomenda</h2>
                    <div class="mt-5 space-y-3 text-sm">
                        <div class="flex justify-between gap-4"><span class="text-zinc-500">Subtotal</span><span>€{{ number_format($this->cartSubtotal, 2, ',', '.') }}</span></div>
                        <div class="flex justify-between gap-4"><span class="text-zinc-500">IVA (23%)</span><span>€{{ number_format($this->cartTax, 2, ',', '.') }}</span></div>
                        <div class="flex justify-between gap-4"><span class="text-zinc-500">Envio</span><span>{{ $this->cartShipping > 0 ? '€'.number_format($this->cartShipping, 2, ',', '.') : 'Grátis' }}</span></div>
                        <div class="flex justify-between gap-4 border-t border-zinc-100 pt-4 text-base font-extrabold"><span>Total</span><span>€{{ number_format($this->cartTotal, 2, ',', '.') }}</span></div>
                    </div>
                    <button type="button" wire:click="openCheckout" class="mt-6 w-full rounded-xl px-4 py-3.5 text-sm font-bold text-white" style="background:{{ $accent }}">Avançar para pagamento</button>
                    <p class="mt-3 text-center text-xs leading-5 text-zinc-400">Os custos finais são apresentados antes de concluir a encomenda.</p>
                </aside>
            </div>
        @endif
    </main>
    <footer class="border-t border-zinc-200 bg-white px-4 py-6 text-center text-xs text-zinc-400">© {{ date('Y') }} {{ $site->name }}</footer>
</div>
