<div class="mx-auto max-w-7xl space-y-8 px-6 py-10 lg:px-12">
    <div class="flex flex-col justify-between gap-5 md:flex-row md:items-end">
        <div>
            <p class="text-[10px] font-black uppercase tracking-[0.25em] text-indigo-600">Gestão do website</p>
            <h1 class="mt-2 text-4xl font-black tracking-tight text-zinc-950 dark:text-white">Utilizadores</h1>
            <p class="mt-2 max-w-2xl text-sm text-zinc-500 dark:text-zinc-400">Gere os utilizadores associados ao website selecionado.</p>
        </div>
        <x-site-workspace-selector :current-site="$site" route-name="users" />
    </div>

    @if(session('status'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/30 dark:text-emerald-300">{{ session('status') }}</div>
    @endif
    @if(session('error'))
        <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-medium text-red-800 dark:border-red-900 dark:bg-red-950/30 dark:text-red-300">{{ session('error') }}</div>
    @endif

    <section class="overflow-hidden rounded-3xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex flex-col gap-4 border-b border-zinc-200 p-6 dark:border-zinc-800 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-bold text-zinc-950 dark:text-white">Utilizadores associados</h2>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $users->count() }} utilizador(es) neste website.</p>
            </div>
            <input wire:model.live.debounce.300ms="search" type="search" placeholder="Pesquisar por nome ou email..." class="w-full rounded-xl border-zinc-200 bg-zinc-50 px-4 py-2.5 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white sm:w-80">
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[720px] text-left text-sm">
                <thead class="border-b border-zinc-200 bg-zinc-50 text-xs font-bold uppercase tracking-wider text-zinc-500 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-400">
                    <tr>
                        <th class="px-6 py-4">Utilizador</th>
                        <th class="px-6 py-4">Email</th>
                        <th class="px-6 py-4">Função</th>
                        <th class="px-6 py-4 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse($users as $user)
                        <tr wire:key="site-user-{{ $user->id }}" class="hover:bg-zinc-50/70 dark:hover:bg-zinc-800/40">
                            <td class="px-6 py-4 font-semibold text-zinc-900 dark:text-white">{{ $user->name }}</td>
                            <td class="px-6 py-4 text-zinc-600 dark:text-zinc-300">{{ $user->email }}</td>
                            <td class="px-6 py-4"><span class="rounded-full bg-zinc-100 px-3 py-1 text-xs font-semibold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">{{ ucfirst($user->role ?? 'utilizador') }}</span></td>
                            <td class="px-6 py-4 text-right">
                                <button type="button" wire:click="edit({{ $user->id }})" class="mr-3 text-sm font-semibold text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">Editar</button>
                                @if($user->id !== auth()->id())
                                    <button type="button" wire:click="delete({{ $user->id }})" wire:confirm="Eliminar este utilizador?" class="text-sm font-semibold text-red-600 hover:text-red-800">Eliminar</button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-6 py-14 text-center text-sm text-zinc-500 dark:text-zinc-400">Não existem utilizadores associados a este website.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @if($editingUserId !== null)
        <section class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between gap-4">
                <div><h2 class="text-lg font-bold text-zinc-950 dark:text-white">Editar utilizador</h2><p class="mt-1 text-sm text-zinc-500">Atualiza os dados e a função do utilizador.</p></div>
                <button type="button" wire:click="resetForm" class="rounded-lg px-3 py-2 text-sm font-semibold text-zinc-500 hover:bg-zinc-100 dark:hover:bg-zinc-800">Fechar</button>
            </div>
            <form wire:submit="save" class="mt-6 grid gap-5 md:grid-cols-2">
                <div><label class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-500">Nome</label><input wire:model="name" type="text" class="w-full rounded-xl border-zinc-200 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white"></div>
                <div><label class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-500">Email</label><input wire:model="email" type="email" class="w-full rounded-xl border-zinc-200 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white"></div>
                <div><label class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-500">Função</label><select wire:model="role" class="w-full rounded-xl border-zinc-200 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white"><option value="administrador">Administrador</option><option value="gestor">Gestor</option><option value="vendedor">Vendedor</option></select></div>
                <div class="flex items-end justify-end"><button type="submit" class="rounded-xl bg-zinc-950 px-5 py-3 text-sm font-bold text-white hover:bg-zinc-800 dark:bg-white dark:text-zinc-950">Guardar alterações</button></div>
            </form>
        </section>
    @endif
</div>