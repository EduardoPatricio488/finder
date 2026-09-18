<div class="min-h-full bg-stone-50">
    <div class="mx-auto max-w-6xl px-5 py-8 sm:px-8 lg:px-10 lg:py-12">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-amber-700">Área pessoal</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight text-stone-950 sm:text-4xl">A minha conta</h1>
                <p class="mt-2 max-w-2xl text-sm text-stone-600">Gere os teus dados pessoais, acesso e segurança da conta Finder.</p>
            </div>
            <a href="{{ route('dashboard') }}" wire:navigate class="inline-flex w-fit items-center rounded-lg border border-stone-200 bg-white px-4 py-2.5 text-sm font-semibold text-stone-700 shadow-sm hover:border-stone-300 hover:bg-stone-100">
                ← Voltar aos meus websites
            </a>
        </div>

        @if (session('account-status'))
            <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                {{ session('account-status') }}
            </div>
        @endif

        <div class="mt-8 grid gap-6 lg:grid-cols-[240px_1fr]">
            <aside class="h-fit rounded-2xl border border-stone-200 bg-white p-2 shadow-sm">
                <div class="px-3 py-4">
                    <div class="flex size-12 items-center justify-center rounded-full bg-stone-950 text-sm font-bold text-white">
                        {{ auth()->user()->initials() }}
                    </div>
                    <p class="mt-3 truncate text-sm font-semibold text-stone-950">{{ auth()->user()->name }}</p>
                    <p class="truncate text-xs text-stone-500">{{ auth()->user()->email }}</p>
                </div>
                <nav class="space-y-1 border-t border-stone-100 pt-2">
                    <a href="#perfil" class="block rounded-lg px-3 py-2.5 text-sm font-medium text-stone-700 hover:bg-stone-100">Perfil</a>
                    <a href="#seguranca" class="block rounded-lg px-3 py-2.5 text-sm font-medium text-stone-700 hover:bg-stone-100">Segurança</a>
                    <a href="{{ route('profile.edit') }}" wire:navigate class="block rounded-lg px-3 py-2.5 text-sm font-medium text-stone-700 hover:bg-stone-100">Definições do perfil</a>
                </nav>
            </aside>

            <main class="space-y-6">
                <section id="perfil" class="scroll-mt-6 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8">
                    <div>
                        <h2 class="text-xl font-semibold text-stone-950">Dados pessoais</h2>
                        <p class="mt-1 text-sm text-stone-500">Atualiza o nome e o email usados na tua conta.</p>
                    </div>

                    <form wire:submit="saveProfile" class="mt-6 grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="account-name" class="text-sm font-medium text-stone-700">Nome</label>
                            <input id="account-name" type="text" wire:model="name" autocomplete="name" class="mt-2 block w-full rounded-lg border-stone-300 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
                            @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="account-email" class="text-sm font-medium text-stone-700">Email</label>
                            <input id="account-email" type="email" wire:model="email" autocomplete="email" class="mt-2 block w-full rounded-lg border-stone-300 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
                            @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div class="sm:col-span-2 flex justify-end">
                            <button type="submit" class="rounded-lg bg-stone-950 px-5 py-2.5 text-sm font-semibold text-white hover:bg-stone-800">Guardar alterações</button>
                        </div>
                    </form>
                </section>

                <section id="seguranca" class="scroll-mt-6 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8">
                    <div>
                        <h2 class="text-xl font-semibold text-stone-950">Segurança</h2>
                        <p class="mt-1 text-sm text-stone-500">Altera a tua palavra-passe para proteger a conta.</p>
                    </div>

                    @if (session('password-status'))
                        <div class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('password-status') }}</div>
                    @endif

                    <form wire:submit="changePassword" class="mt-6 grid gap-5 sm:max-w-2xl">
                        <div>
                            <label for="current-password" class="text-sm font-medium text-stone-700">Palavra-passe atual</label>
                            <input id="current-password" type="password" wire:model="currentPassword" autocomplete="current-password" class="mt-2 block w-full rounded-lg border-stone-300 px-3 py-2.5 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
                            @error('currentPassword')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="new-password" class="text-sm font-medium text-stone-700">Nova palavra-passe</label>
                            <input id="new-password" type="password" wire:model="newPassword" autocomplete="new-password" class="mt-2 block w-full rounded-lg border-stone-300 px-3 py-2.5 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
                            @error('newPassword')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="new-password-confirmation" class="text-sm font-medium text-stone-700">Confirmar nova palavra-passe</label>
                            <input id="new-password-confirmation" type="password" wire:model="newPasswordConfirmation" autocomplete="new-password" class="mt-2 block w-full rounded-lg border-stone-300 px-3 py-2.5 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="rounded-lg bg-stone-950 px-5 py-2.5 text-sm font-semibold text-white hover:bg-stone-800">Alterar palavra-passe</button>
                        </div>
                    </form>
                </section>

                <section class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-xl font-semibold text-stone-950">Sessão e acesso</h2>
                            <p class="mt-1 text-sm text-stone-500">Para outras opções de conta e autenticação, usa as definições do perfil.</p>
                        </div>
                        <a href="{{ route('profile.edit') }}" wire:navigate class="inline-flex w-fit rounded-lg border border-stone-300 px-4 py-2.5 text-sm font-semibold text-stone-700 hover:bg-stone-50">
                            Abrir definições
                        </a>
                    </div>
                </section>

                <section class="rounded-2xl border border-red-200 bg-red-50 p-6 sm:p-8">
                    <div>
                        <h2 class="text-lg font-semibold text-red-950">Terminar sessão</h2>
                        <p class="mt-1 text-sm text-red-800">Sai da tua conta neste dispositivo.</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="mt-4">
                        @csrf
                        <button type="submit" class="rounded-lg border border-red-300 bg-white px-4 py-2.5 text-sm font-semibold text-red-700 hover:bg-red-100">Terminar sessão</button>
                    </form>
                </section>
            </main>
        </div>
    </div>
</div>
