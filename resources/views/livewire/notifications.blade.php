<div class="min-h-screen bg-zinc-50 dark:bg-zinc-950">
    <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-indigo-600 dark:text-indigo-400">Centro de atividade</p>
                <h1 class="mt-1 text-3xl font-bold text-zinc-950 dark:text-white">Notificações</h1>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">Recebe avisos sobre alterações no website, produtos e vendas.</p>
            </div>
            @if($unreadCount > 0)
                <button type="button" wire:click="markAllAsRead" class="rounded-xl border border-zinc-200 bg-white px-4 py-2.5 text-sm font-semibold hover:border-indigo-300 dark:border-zinc-700 dark:bg-zinc-900">Marcar todas como lidas</button>
            @endif
        </div>

        <div class="mt-6 overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            @forelse($notifications as $notification)
                <button type="button" wire:click="markAsRead('{{ $notification->id }}')" class="flex w-full gap-4 border-b border-zinc-100 px-5 py-5 text-left transition last:border-b-0 hover:bg-zinc-50 dark:border-zinc-800 dark:hover:bg-zinc-800/50 {{ $notification->read_at ? '' : 'bg-indigo-50/50 dark:bg-indigo-500/5' }}">
                    <span class="mt-0.5 flex size-10 shrink-0 items-center justify-center rounded-xl {{ $notification->read_at ? 'bg-zinc-100 text-zinc-500 dark:bg-zinc-800' : 'bg-indigo-100 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-400' }}">
                        <flux:icon name="{{ $notification->data['icon'] ?? 'bell' }}" class="size-5" />
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="flex items-center justify-between gap-3">
                            <span class="font-semibold text-zinc-950 dark:text-white">{{ $notification->data['title'] ?? 'Notificação' }}</span>
                            <span class="shrink-0 text-xs text-zinc-400">{{ $notification->created_at->diffForHumans() }}</span>
                        </span>
                        <span class="mt-1 block text-sm text-zinc-500 dark:text-zinc-400">{{ $notification->data['message'] ?? '' }}</span>
                    </span>
                </button>
            @empty
                <div class="px-6 py-16 text-center">
                    <flux:icon name="bell" class="mx-auto size-10 text-zinc-300 dark:text-zinc-600" />
                    <p class="mt-3 font-semibold text-zinc-950 dark:text-white">Sem notificações</p>
                    <p class="mt-1 text-sm text-zinc-500">Os avisos sobre o teu website vão aparecer aqui.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>