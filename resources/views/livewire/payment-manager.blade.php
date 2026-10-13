<div class="min-h-screen bg-zinc-50 p-6 dark:bg-zinc-950 lg:p-8">
    <div class="mx-auto max-w-7xl space-y-6">
        <div class="mb-2">
            <x-site-workspace-selector :current-site="$this->currentSite()" route-name="payments" />
        </div>

        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-8">
                <h1 class="text-3xl font-bold tracking-tight text-zinc-950 dark:text-white">Pagamentos</h1>
                <p class="mt-3 max-w-2xl text-sm text-zinc-500 dark:text-zinc-400">Acompanha os pagamentos das encomendas deste website e confirma os pagamentos recebidos.</p>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white px-5 py-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Total apresentado</p>
                <p class="mt-1 text-xl font-bold text-zinc-950 dark:text-white">€ {{ number_format((float) $payments->sum('amount'), 2, ',', '.') }}</p>
                <p class="mt-1 text-xs text-zinc-500">{{ $payments->count() }} pagamento(s)</p>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/30 dark:text-emerald-300">{{ session('status') }}</div>
        @endif

        <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="font-bold text-zinc-950 dark:text-white">Registo de pagamentos</h2>
                    <p class="mt-1 text-sm text-zinc-500">Filtra os pagamentos pelo estado.</p>
                </div>
                <select wire:model.live="status" class="w-full rounded-xl border-zinc-200 bg-zinc-50 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950 sm:w-60">
                    <option value="">Todos os estados</option>
                    <option value="pendente">Pendente</option>
                    <option value="recebido">Recebido</option>
                    <option value="falhado">Falhado</option>
                    <option value="reembolsado">Reembolsado</option>
                </select>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[850px] text-left text-sm">
                    <thead class="bg-zinc-50 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:bg-zinc-800/50">
                        <tr>
                            <th class="px-5 py-4">Encomenda</th>
                            <th class="px-5 py-4">Cliente</th>
                            <th class="px-5 py-4">Método</th>
                            <th class="px-5 py-4">Referência</th>
                            <th class="px-5 py-4">Estado</th>
                            <th class="px-5 py-4">Data</th>
                            <th class="px-5 py-4 text-right">Valor</th>
                            <th class="px-5 py-4 text-right">Ação</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse ($payments as $payment)
                            <tr class="transition hover:bg-zinc-50 dark:hover:bg-zinc-800/40">
                                <td class="px-5 py-4 font-semibold text-zinc-950 dark:text-white">#{{ $payment->order?->order_number ?? $payment->order_id }}</td>
                                <td class="px-5 py-4 text-zinc-700 dark:text-zinc-300">{{ $payment->order?->customer?->name ?? '—' }}</td>
                                <td class="px-5 py-4 text-zinc-700 dark:text-zinc-300">{{ ucfirst(str_replace('_', ' ', $payment->method ?? '—')) }}</td>
                                <td class="px-5 py-4 text-zinc-500 dark:text-zinc-400">{{ $payment->transaction_reference ?: '—' }}</td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $payment->status === 'recebido' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300' : ($payment->status === 'falhado' ? 'bg-red-100 text-red-700 dark:bg-red-950/40 dark:text-red-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300') }}">{{ ucfirst(str_replace('_', ' ', $payment->status)) }}</span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-zinc-500 dark:text-zinc-400">{{ $payment->paid_at?->format('d/m/Y H:i') ?? $payment->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
                                <td class="px-5 py-4 text-right font-bold text-zinc-950 dark:text-white">€ {{ number_format((float) $payment->amount, 2, ',', '.') }}</td>
                                <td class="px-5 py-4 text-right">
                                    @if ($payment->status !== 'recebido')
                                        <button type="button" wire:click="markAsReceived({{ $payment->id }})" wire:loading.attr="disabled" class="whitespace-nowrap rounded-lg bg-indigo-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-50">Marcar recebido</button>
                                    @else
                                        <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-300">Confirmado</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-16 text-center">
                                    <div class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-zinc-100 text-zinc-500 dark:bg-zinc-800"><flux:icon name="currency-euro" class="size-6" /></div>
                                    <p class="mt-3 font-semibold text-zinc-900 dark:text-white">Ainda não existem pagamentos</p>
                                    <p class="mt-1 text-sm text-zinc-500">Os pagamentos das encomendas deste website aparecerão aqui.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
