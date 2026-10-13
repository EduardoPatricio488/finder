<div class="mx-auto max-w-7xl space-y-6 px-6 py-8 lg:px-12">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-8">
            <h1 class="text-3xl font-bold tracking-tight text-zinc-950 dark:text-white">Todos os utilizadores</h1>
            <p class="mt-3 max-w-2xl text-sm text-zinc-500 dark:text-zinc-400">Gere os utilizadores dos teus websites num único catálogo e mantém os acessos organizados.</p>
        </div>
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
                <h2 class="text-lg font-bold text-zinc-950 dark:text-white">Utilizadores</h2>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $users->count() }} utilizador(es) encontrados.</p>
            </div>
            <select wire:model.live="catalogSiteId" class="w-full rounded-xl border-zinc-200 bg-zinc-50 px-4 py-2.5 text-sm dark:border-zinc-700 dark:bg-zinc-950 sm:w-56"><option value="">Todos os websites</option>@foreach($availableSites as $availableSite)<option value="{{ $availableSite->id }}">{{ $availableSite->name }}</option>@endforeach</select>
            <input wire:model.live.debounce.300ms="search" type="search" placeholder="Pesquisar por nome ou email..." class="w-full rounded-xl border-zinc-200 bg-zinc-50 px-4 py-2.5 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white sm:w-80">
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[720px] text-left text-sm">
                <thead class="border-b border-zinc-200 bg-zinc-50 text-xs font-bold uppercase tracking-wider text-zinc-500 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-400">
                    <tr>
                        <th class="px-6 py-4">Utilizador</th>
                        <th class="px-6 py-4">Email</th>
                        <th class="px-6 py-4">Websites</th>
                        <th class="px-6 py-4">Função</th>
                        <th class="px-6 py-4 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse($users as $user)
                        <tr wire:key="site-user-{{ $user->id }}" class="hover:bg-zinc-50/70 dark:hover:bg-zinc-800/40">
                            <td class="px-6 py-4 font-semibold text-zinc-900 dark:text-white">{{ $user->name }}</td>
                            <td class="px-6 py-4 text-zinc-600 dark:text-zinc-300">{{ $user->email }}</td>
                            <td class="px-6 py-4 text-zinc-600 dark:text-zinc-300">{{ $user->sites->whereIn('id', $availableSites->modelKeys())->pluck('name')->join(', ') ?: '—' }}</td>
                            <td class="px-6 py-4"><span class="rounded-full bg-zinc-100 px-3 py-1 text-xs font-semibold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">{{ ucfirst($user->role ?? 'utilizador') }}</span></td>
                            <td class="px-6 py-4 text-right">
                                <button type="button" wire:click="edit({{ $user->id }})" class="mr-3 text-sm font-semibold text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">Editar</button>
                                @if($user->id !== auth()->id())
                                    <button type="button" wire:click="delete({{ $user->id }})" wire:confirm="Eliminar este utilizador?" class="text-sm font-semibold text-red-600 hover:text-red-800">Eliminar</button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-14 text-center text-sm text-zinc-500 dark:text-zinc-400">Não existem utilizadores nos websites selecionados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @if($editingUserId !== null)
        <div class="fixed inset-0 z-[100] flex items-center justify-center overflow-y-auto bg-zinc-950/55 p-4 backdrop-blur-sm sm:p-6" role="dialog" aria-modal="true" aria-labelledby="edit-user-modal-title">
            <section class="my-auto w-full max-w-2xl overflow-hidden rounded-3xl border border-zinc-200 bg-white shadow-2xl dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-start justify-between gap-4 border-b border-zinc-100 px-6 py-5 dark:border-zinc-800 sm:px-8">
                    <div class="flex items-start gap-4">
                        <div class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-300">
                            <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2m6-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm7-1 2 2m-5 5 1-.2 5.2-5.2a1.4 1.4 0 0 0-2-2L12 15.8 11.8 17Z"/></svg>
                        </div>
                        <div>
                            <h2 id="edit-user-modal-title" class="text-xl font-bold tracking-tight text-zinc-950 dark:text-white">Editar utilizador</h2>
                            <p class="mt-1 max-w-md text-sm leading-5 text-zinc-500 dark:text-zinc-400">Atualiza os dados de perfil e define o nível de acesso deste utilizador.</p>
                        </div>
                    </div>
                    <button type="button" wire:click="resetForm" aria-label="Fechar janela" class="flex size-9 shrink-0 items-center justify-center rounded-xl text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-900 dark:hover:bg-zinc-800 dark:hover:text-white">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m6 6 12 12M18 6 6 18"/></svg>
                    </button>
                </div>

                <form wire:submit="save">
                    <div class="space-y-5 px-6 py-6 sm:px-8">
                        <div class="rounded-2xl border border-zinc-200 bg-zinc-50/70 p-4 dark:border-zinc-700 dark:bg-zinc-950/40">
                            <p class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Dados do perfil</p>
                            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Confirma que os dados estão corretos antes de guardar.</p>
                        </div>

                        <div>
                            <label for="edit-user-name" class="mb-2 block text-sm font-semibold text-zinc-700 dark:text-zinc-200">Nome completo</label>
                            <input id="edit-user-name" wire:model="name" type="text" autocomplete="name" required placeholder="Nome do utilizador" class="w-full rounded-xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-900 outline-none transition placeholder:text-zinc-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                            @error('name') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="edit-user-email" class="mb-2 block text-sm font-semibold text-zinc-700 dark:text-zinc-200">Endereço de email</label>
                            <input id="edit-user-email" wire:model="email" type="email" autocomplete="email" required placeholder="nome@exemplo.pt" class="w-full rounded-xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-900 outline-none transition placeholder:text-zinc-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                            @error('email') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="edit-user-role" class="mb-2 block text-sm font-semibold text-zinc-700 dark:text-zinc-200">Função e permissões</label>
                            <select id="edit-user-role" wire:model="role" required class="w-full rounded-xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-900 outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                                <option value="administrador">Administrador</option>
                                <option value="gestor">Gestor</option>
                                <option value="vendedor">Vendedor</option>
                            </select>
                            <p class="mt-1.5 text-xs text-zinc-500 dark:text-zinc-400">A função determina as ações que o utilizador pode executar no website.</p>
                            @error('role') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="flex flex-col-reverse gap-3 border-t border-zinc-100 bg-zinc-50/70 px-6 py-4 dark:border-zinc-800 dark:bg-zinc-950/30 sm:flex-row sm:justify-end sm:px-8">
                        <button type="button" wire:click="resetForm" class="inline-flex items-center justify-center rounded-xl border border-zinc-200 bg-white px-5 py-2.5 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800">Cancelar</button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="save" class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-indigo-700 disabled:cursor-wait disabled:opacity-60">
                            <span wire:loading.remove wire:target="save">Guardar alterações</span>
                            <span wire:loading.flex wire:target="save" class="items-center gap-2"><svg class="size-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 0 8-8v4a4 4 0 0 0-4 4H4z"/></svg>A guardar...</span>
                        </button>
                    </div>
                </form>
            </section>
        </div>
    @endif
</div>