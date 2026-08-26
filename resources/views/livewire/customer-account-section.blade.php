<main class="store-reveal mx-auto max-w-5xl px-5 py-10 sm:px-8 lg:px-12 lg:py-14">
    <div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-amber-700">A minha conta</p>
            <h1 class="mt-3 text-4xl font-semibold tracking-tight">{{ $currentSection['title'] }}</h1>
            <p class="mt-3 max-w-xl text-stone-600">{{ $currentSection['description'] }}</p>
        </div>
        <a href="{{ route('account') }}" wire:navigate class="text-sm font-semibold text-stone-600 hover:text-stone-950">&larr; Resumo da conta</a>
    </div>

    @if (session('account-status'))
        <div class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('account-status') }}</div>
    @endif

    @if ($section === 'favorites')
        <section class="mt-8 grid gap-4 sm:grid-cols-2">
            @forelse ($favorites as $product)
                <article class="store-card rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                    <h2 class="font-semibold">{{ $product->name }}</h2>
                    <p class="mt-2 text-sm text-stone-500">€ {{ number_format($product->price, 2, ',', '.') }}</p>
                    <a href="{{ route('products.show', $product) }}" wire:navigate class="mt-4 inline-block text-sm font-semibold text-amber-700">Ver produto &rarr;</a>
                </article>
            @empty
                <div class="rounded-xl border border-dashed border-stone-300 bg-white px-6 py-16 text-center sm:col-span-2">
                    <p class="text-4xl">♡</p>
                    <h2 class="mt-4 font-semibold">Ainda não tem favoritos.</h2>
                    <a href="{{ route('products') }}" wire:navigate class="mt-5 inline-flex rounded-lg bg-stone-950 px-4 py-2.5 text-sm font-semibold text-white">Explorar produtos</a>
                </div>
            @endforelse
        </section>
    @elseif ($section === 'addresses')
        <section class="mt-8 grid gap-8 lg:grid-cols-[0.8fr_1.2fr]">
            <form wire:submit="addAddress" class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                <h2 class="font-semibold">Adicionar morada</h2>
                <input wire:model="addressLabel" type="text" placeholder="Ex.: Casa" class="mt-4 w-full rounded-lg border-stone-300 text-sm" required>
                <textarea wire:model="addressText" rows="4" placeholder="Rua, código postal e localidade" class="mt-3 w-full rounded-lg border-stone-300 text-sm" required></textarea>
                <button class="mt-3 rounded-lg bg-stone-950 px-4 py-2.5 text-sm font-semibold text-white">Guardar morada</button>
            </form>
            <div class="space-y-3">
                @forelse ($addresses as $address)
                    <article class="flex items-start justify-between gap-4 rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                        <div>
                            <h2 class="font-semibold">{{ $address->label }}</h2>
                            <p class="mt-2 whitespace-pre-line text-sm leading-6 text-stone-600">{{ $address->address }}</p>
                        </div>
                        <button type="button" wire:click="removeAddress({{ $address->id }})" class="text-sm font-semibold text-red-700">Remover</button>
                    </article>
                @empty
                    <p class="rounded-xl border border-dashed border-stone-300 bg-white p-10 text-center text-sm text-stone-500">Ainda não tem moradas guardadas.</p>
                @endforelse
            </div>
        </section>
    @elseif ($section === 'payments')
        <section class="mt-8 grid gap-8 lg:grid-cols-[0.8fr_1.2fr]">
            <form wire:submit="addPaymentMethod" class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                <h2 class="font-semibold">Adicionar método</h2>
                <select wire:model="paymentType" class="mt-4 w-full rounded-lg border-stone-300 text-sm">
                    <option value="cartao">Cartão</option>
                    <option value="mbway">MB WAY</option>
                </select>
                <input wire:model="paymentLabel" type="text" placeholder="Ex.: Cartão pessoal" class="mt-3 w-full rounded-lg border-stone-300 text-sm" required>
                <input wire:model="paymentLastFour" type="text" inputmode="numeric" maxlength="4" placeholder="Últimos 4 dígitos (opcional)" class="mt-3 w-full rounded-lg border-stone-300 text-sm">
                <button class="mt-3 rounded-lg bg-stone-950 px-4 py-2.5 text-sm font-semibold text-white">Guardar método</button>
                <p class="mt-3 text-xs text-stone-500">Nunca guardamos o número completo do cartão.</p>
            </form>
            <div class="space-y-3">
                @forelse ($paymentMethods as $method)
                    <article class="flex items-center justify-between rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                        <div>
                            <h2 class="font-semibold">{{ $method->label }}</h2>
                            <p class="mt-1 text-sm uppercase text-stone-500">{{ $method->type }} @if ($method->last_four) · **** {{ $method->last_four }}@endif</p>
                        </div>
                        <button type="button" wire:click="removePaymentMethod({{ $method->id }})" class="text-sm font-semibold text-red-700">Remover</button>
                    </article>
                @empty
                    <p class="rounded-xl border border-dashed border-stone-300 bg-white p-10 text-center text-sm text-stone-500">Ainda não tem métodos guardados.</p>
                @endforelse
            </div>
        </section>
    @else
        <section class="mt-8 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold">Preferências de email</h2>
            <div class="mt-5 space-y-4">
                <label class="flex items-center justify-between gap-4 text-sm">
                    <span><strong class="block">Atualizações de encomendas</strong><span class="text-stone-500">Receba notícias sobre o estado das suas compras.</span></span>
                    <input wire:model="orderUpdates" type="checkbox" class="size-5 rounded border-stone-300 text-amber-600">
                </label>
                <label class="flex items-center justify-between gap-4 text-sm">
                    <span><strong class="block">Promoções</strong><span class="text-stone-500">Saiba quando existem novas ofertas.</span></span>
                    <input wire:model="promotions" type="checkbox" class="size-5 rounded border-stone-300 text-amber-600">
                </label>
                <label class="flex items-center justify-between gap-4 text-sm">
                    <span><strong class="block">Alertas de stock</strong><span class="text-stone-500">Seja avisado quando produtos voltarem.</span></span>
                    <input wire:model="stockAlerts" type="checkbox" class="size-5 rounded border-stone-300 text-amber-600">
                </label>
            </div>
            <button type="button" wire:click="saveNotifications" class="mt-6 rounded-lg bg-stone-950 px-4 py-2.5 text-sm font-semibold text-white">Guardar preferências</button>
        </section>
    @endif
</main>
