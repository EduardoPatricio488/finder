<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <flux:heading size="xl">Websites</flux:heading>
            <flux:text class="mt-1">Gere todos os websites da plataforma e acede ao respetivo backoffice.</flux:text>
        </div>
        <div class="flex gap-2">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Pesquisar website ou proprietário..." icon="magnifying-glass" />
            <flux:select wire:model.live="status">
                <flux:select.option value="all">Todos</flux:select.option>
                <flux:select.option value="published">Publicados</flux:select.option>
                <flux:select.option value="draft">Rascunhos</flux:select.option>
            </flux:select>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                <thead class="bg-zinc-50 dark:bg-zinc-950">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Website</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Proprietário</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Estado</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-zinc-500">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                    @forelse($sites as $site)
                        <tr wire:key="site-{{ $site->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-950/50">
                            <td class="px-5 py-4">
                                <div class="font-medium text-zinc-950 dark:text-white">{{ $site->name }}</div>
                                <div class="text-sm text-zinc-500">/{{ $site->slug }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="text-sm text-zinc-900 dark:text-zinc-100">{{ $site->owner?->name ?? '—' }}</div>
                                <div class="text-xs text-zinc-500">{{ $site->owner?->email ?? '—' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <flux:badge color="{{ $site->is_published && $site->status === 'published' ? 'green' : 'zinc' }}">
                                    {{ $site->statusLabel() }}
                                </flux:badge>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <flux:button wire:click="access({{ $site->id }})" size="sm" variant="primary">Gerir website</flux:button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-12 text-center text-sm text-zinc-500">Não foram encontrados websites.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-zinc-200 p-4 dark:border-zinc-800">{{ $sites->links() }}</div>
    </div>
</div>
