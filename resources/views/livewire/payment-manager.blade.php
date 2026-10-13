<div class="rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
    @if(session('status'))
        <div class="m-4 rounded-xl bg-emerald-50 p-3 text-sm text-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-300">{{ session('status') }}</div>
    @endif

    <div class="flex flex-col gap-3 border-b border-zinc-100 p-4 dark:border-zinc-800 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h3 class="font-bold text-zinc-950 dark:text-white">Registo de pagamentos</h3>
            <p class="mt-1 text-sm text-zinc-500">Total: € {{ number_format((float) $payments->sum('amount'), 2, ',', '.') }} <span class="mx-1">·</span> {{ $payments->count() }} pagamento(s)</p>
        </div>
        <select wire:model.live="status" class="w-full rounded-xl border-zinc-200 bg-zinc-50 px-3 py-2.5 text-sm dark:border-zinc-700 dark:bg-zinc-950 sm:w-44">
            <option value="">Todos os estados</option>
            <option value="pendente">Pendente</option>
            <option value="recebido">Recebido</option>
            <option value="falhado">Falhado</option>
            <option value="reembolsado">Reembolsado</option>
        </select>
    </div>

    <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
        @forelse($payments as $payment)
            <div wire:key="payment-{{ $payment->id }}" class="grid gap-3 p-4 transition hover:bg-zinc-50 dark:hover:bg-zinc-800/30 sm:grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)_minmax(0,0.8fr)_auto] sm:items-center">
                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-zinc-950 dark:text-white">#{{ $payment->order?->order_number ?? $payment->order_id }}</p>
                    <p class="mt-1 truncate text-sm text-zinc-600 dark:text-zinc-300">{{ $payment->order?->customer?->name ?? '—' }}</p>
                    <p class="mt-1 truncate text-xs text-zinc-500">{{ $payment->order?->site?->name ?? '—' }}</p>
                </div>

                <div class="flex min-w-0 flex-wrap items-center gap-2 text-xs text-zinc-500">
                    <span class="rounded-lg bg-zinc-100 px-2 py-1 font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">{{ ucfirst(str_replace('_', ' ', $payment->method ?? '—')) }}</span>
                    @if($payment->transaction_reference)
                        <span class="truncate" title="{{ $payment->transaction_reference }}">Ref. {{ $payment->transaction_reference }}</span>
                    @endif
                </div>

                <div class="flex items-center justify-between gap-3 sm:block">
                    <div>
                        <p class="text-base font-bold text-zinc-950 dark:text-white">€ {{ number_format((float) $payment->amount, 2, ',', '.') }}</p>
                        <span class="mt-1 inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ $payment->status === 'recebido' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300' : ($payment->status === 'pendente' ? 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300') }}">{{ ucfirst(str_replace('_', ' ', $payment->status)) }}</span>
                    </div>
                    <div class="sm:hidden">
                        @if($payment->status !== 'recebido')
                            <button type="button" wire:click="markAsReceived({{ $payment->id }})" class="rounded-lg bg-indigo-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-indigo-500">Marcar recebido</button>
                        @else
                            <span class="text-xs font-semibold text-emerald-600">Confirmado</span>
                        @endif
                    </div>
                </div>

                <div class="hidden text-right sm:block">
                    @if($payment->status !== 'recebido')
                        <button type="button" wire:click="markAsReceived({{ $payment->id }})" class="whitespace-nowrap rounded-lg bg-indigo-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-indigo-500">Marcar recebido</button>
                    @else
                        <span class="text-xs font-semibold text-emerald-600">Confirmado</span>
                    @endif
                </div>
            </div>
        @empty
            <div class="px-4 py-10 text-center">
                <flux:icon name="banknotes" class="mx-auto size-8 text-zinc-300 dark:text-zinc-600" />
                <p class="mt-2 text-sm font-semibold text-zinc-900 dark:text-white">Ainda não existem pagamentos</p>
                <p class="mt-1 text-sm text-zinc-500">Os pagamentos aparecerão aqui quando forem registados.</p>
            </div>
        @endforelse
    </div>
</div>
