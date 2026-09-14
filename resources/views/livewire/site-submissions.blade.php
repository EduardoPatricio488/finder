<div class="space-y-6 p-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <flux:heading size="xl">Mensagens e subscrições</flux:heading>
            <flux:text class="mt-1">Contactos recebidos através do website público.</flux:text>
        </div>

        <flux:select wire:model.live="status" class="w-44">
            <flux:select.option value="all">Todas</flux:select.option>
            <flux:select.option value="new">Novas</flux:select.option>
            <flux:select.option value="read">Lidas</flux:select.option>
        </flux:select>
    </div>

    <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="border-b border-zinc-200 bg-zinc-50 text-left dark:border-zinc-800 dark:bg-zinc-950">
                    <tr>
                        <th class="px-5 py-3 font-medium">Tipo</th>
                        <th class="px-5 py-3 font-medium">Contacto</th>
                        <th class="px-5 py-3 font-medium">Mensagem</th>
                        <th class="px-5 py-3 font-medium">Estado</th>
                        <th class="px-5 py-3 font-medium">Data</th>
                        <th class="px-5 py-3 text-right font-medium">Ação</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse($submissions as $submission)
                        <tr wire:key="submission-{{ $submission->id }}" class="align-top">
                            <td class="px-5 py-4 font-medium">{{ $submission->form_type === 'newsletter' ? 'Newsletter' : 'Contacto' }}</td>
                            <td class="px-5 py-4">
                                <div>{{ $submission->name ?: '—' }}</div>
                                <div class="text-zinc-500">{{ $submission->email ?: '—' }}</div>
                            </td>
                            <td class="max-w-md px-5 py-4 text-zinc-600 dark:text-zinc-300">{{ $submission->message ?: 'Subscrição de newsletter.' }}</td>
                            <td class="px-5 py-4">{{ $submission->status === 'new' ? 'Nova' : 'Lida' }}</td>
                            <td class="px-5 py-4 whitespace-nowrap text-zinc-500">{{ $submission->created_at?->format('d/m/Y H:i') }}</td>
                            <td class="px-5 py-4 text-right">
                                @if($submission->status === 'new')
                                    <flux:button wire:click="markRead({{ $submission->id }})" variant="ghost" size="sm">Marcar como lida</flux:button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-zinc-500">Ainda não existem submissões.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($submissions->hasPages())
            <div class="border-t border-zinc-200 p-4 dark:border-zinc-800">{{ $submissions->links() }}</div>
        @endif
    </div>
</div>
