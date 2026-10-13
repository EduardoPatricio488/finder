<div class="mx-auto max-w-7xl space-y-6 p-6">
<h1 class="text-3xl font-bold">Promoções</h1>
<form wire:submit="save" class="space-y-3">
<input wire:model="name" placeholder="Nome da promoção" class="w-full rounded border p-2">
<select wire:model="discountType" class="w-full rounded border p-2"><option value="percentagem">Percentagem</option><option value="fixo">Valor fixo</option></select>
<input wire:model="discountValue" type="number" step="0.01" placeholder="Valor do desconto" class="w-full rounded border p-2">
<input wire:model="startsAt" type="datetime-local" class="w-full rounded border p-2">
<input wire:model="endsAt" type="datetime-local" class="w-full rounded border p-2">
<div class="space-y-2">@foreach($products as $product)<label class="block"><input type="checkbox" wire:model="productIds" value="{{ $product->id }}"> {{ $product->name }}</label>@endforeach</div>
<button type="submit" class="rounded bg-indigo-600 px-4 py-2 text-white">Guardar promoção</button>
</form>
<div class="space-y-3">@foreach($promotions as $promotion)<article class="rounded border p-4"><strong>{{ $promotion->name }}</strong><p>{{ $promotion->discount_value }} {{ $promotion->discount_type }}</p><p>{{ $promotion->starts_at?->format('d/m/Y H:i') }} — {{ $promotion->ends_at?->format('d/m/Y H:i') }}</p><button wire:click="edit({{ $promotion->id }})">Editar</button><button wire:click="delete({{ $promotion->id }})">Eliminar</button></article>@endforeach</div>
</div>