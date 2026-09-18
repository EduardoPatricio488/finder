<div class="w-full">
    <div class="mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <flux:heading size="xl" level="1">Conta</flux:heading>
                <flux:subheading class="mt-1 max-w-2xl">Gere o teu perfil, segurança, autenticação e aparência do Finder num único lugar.</flux:subheading>
            </div>
            <flux:button variant="outline" :href="route('dashboard')" wire:navigate icon="arrow-left">Meus websites</flux:button>
        </div>

        @if (session('status') === 'verification-link-sent')
            <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-300">
                Foi enviado um novo email de verificação para {{ $email }}.
            </div>
        @endif

        <div class="mt-8 grid gap-6 lg:grid-cols-[230px_minmax(0,1fr)]">
            <aside class="h-fit lg:sticky lg:top-6">
                <div class="rounded-2xl border border-zinc-200 bg-white p-2 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="px-3 py-4">
                        <flux:avatar :name="$name" :initials="auth()->user()->initials()" />
                        <flux:heading class="mt-3 truncate">{{ $name }}</flux:heading>
                        <flux:text class="truncate">{{ $email }}</flux:text>
                    </div>

                    <nav class="space-y-1 border-t border-zinc-100 pt-2 dark:border-zinc-800">
                        <a href="#perfil" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800">
                            <flux:icon.user class="size-4" /> Perfil
                        </a>
                        <a href="#seguranca" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800">
                            <flux:icon.shield-check class="size-4" /> Segurança
                        </a>
                        <a href="#aparencia" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800">
                            <flux:icon.paint-brush class="size-4" /> Aparência
                        </a>
                    </nav>
                </div>
            </aside>

            <main class="min-w-0 space-y-6">
                <section id="perfil" class="scroll-mt-6 rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 sm:p-8">
                    <div>
                        <flux:heading size="lg">Perfil</flux:heading>
                        <flux:subheading>Atualiza o teu nome e endereço de email.</flux:subheading>
                    </div>

                    <form wire:submit="updateProfileInformation" class="mt-6 space-y-5">
                        <div class="grid gap-5 sm:grid-cols-2">
                            <flux:input wire:model="name" label="Nome" type="text" required autocomplete="name" />
                            <flux:input wire:model="email" label="Email" type="email" required autocomplete="email" />
                        </div>

                        @if ($this->hasUnverifiedEmail)
                            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-900 dark:bg-amber-950/30">
                                <flux:text class="text-amber-800 dark:text-amber-300">O teu endereço de email ainda não foi verificado.</flux:text>
                                <flux:button variant="ghost" size="sm" class="mt-2" wire:click="resendVerificationNotification">Reenviar email de verificação</flux:button>
                            </div>
                        @endif

                        <div class="flex justify-end">
                            <flux:button variant="primary" type="submit">Guardar alterações</flux:button>
                        </div>
                    </form>
                </section>

                <section id="seguranca" class="scroll-mt-6 rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 sm:p-8">
                    <div>
                        <flux:heading size="lg">Segurança</flux:heading>
                        <flux:subheading>Palavra-passe, autenticação de dois fatores e passkeys.</flux:subheading>
                    </div>

                    <form wire:submit="updatePassword" class="mt-6 max-w-2xl space-y-5">
                        <flux:input wire:model="current_password" label="Palavra-passe atual" type="password" required autocomplete="current-password" viewable />
                        <flux:input wire:model="password" label="Nova palavra-passe" type="password" required autocomplete="new-password" passwordrules="{{ IlluminateValidationRulesPassword::defaults()->toPasswordRulesString() }}" viewable />
                        <flux:input wire:model="password_confirmation" label="Confirmar nova palavra-passe" type="password" required autocomplete="new-password" viewable />
                        <div class="flex justify-end">
                            <flux:button variant="primary" type="submit">Alterar palavra-passe</flux:button>
                        </div>
                    </form>

                    @if ($canManageTwoFactor)
                        <div class="mt-10 border-t border-zinc-200 pt-8 dark:border-zinc-700">
                            <flux:heading>Autenticação de dois fatores</flux:heading>
                            <flux:subheading>Adiciona uma camada extra de proteção ao iniciar sessão.</flux:subheading>

                            <div class="mt-5">
                                @if ($twoFactorEnabled)
                                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-900 dark:bg-emerald-950/30">
                                        <flux:text class="text-emerald-800 dark:text-emerald-300">A autenticação de dois fatores está ativa.</flux:text>
                                    </div>
                                    <div class="mt-4 flex flex-wrap gap-3">
                                        <flux:button variant="danger" wire:click="disableTwoFactor">Desativar 2FA</flux:button>
                                    </div>
                                    <div class="mt-6">
                                        <livewire:pages::settings.two-factor.recovery-codes :$requiresConfirmation />
                                    </div>
                                @else
                                    <flux:text variant="subtle">Protege a conta com um código seguro através de uma aplicação de autenticação.</flux:text>
                                    <div class="mt-4">
                                        <flux:modal.trigger name="two-factor-setup-modal">
                                            <flux:button variant="primary" wire:click="$dispatch('start-two-factor-setup')">Ativar 2FA</flux:button>
                                        </flux:modal.trigger>
                                        <livewire:pages::settings.two-factor-setup-modal :requires-confirmation="$requiresConfirmation" />
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if ($canManagePasskeys)
                        <div class="mt-10 border-t border-zinc-200 pt-8 dark:border-zinc-700">
                            <flux:heading>Passkeys</flux:heading>
                            <flux:subheading>Inicia sessão sem palavra-passe através de uma passkey.</flux:subheading>

                            <div class="mt-5 overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
                                @forelse ($passkeys as $passkey)
                                    <div class="flex items-center justify-between gap-4 p-4 {{ ! $loop->last ? 'border-b border-zinc-200 dark:border-zinc-700' : '' }}">
                                        <div class="flex min-w-0 items-center gap-3">
                                            <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-zinc-100 dark:bg-zinc-800">
                                                <flux:icon.key class="size-5 text-zinc-500" />
                                            </div>
                                            <div class="min-w-0">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <flux:text class="font-medium">{{ $passkey['name'] }}</flux:text>
                                                    @if ($passkey['authenticator'])
                                                        <flux:badge size="sm">{{ $passkey['authenticator'] }}</flux:badge>
                                                    @endif
                                                </div>
                                                <flux:text size="sm">Adicionada {{ $passkey['created_at_diff'] }}</flux:text>
                                            </div>
                                        </div>
                                        <flux:button variant="ghost" icon="trash" wire:click="confirmDelete({{ $passkey['id'] }})" aria-label="Remover passkey" />
                                    </div>
                                @empty
                                    <div class="p-6 text-center">
                                        <flux:icon.key class="mx-auto size-7 text-zinc-400" />
                                        <flux:text class="mt-2">Ainda não tens passkeys configuradas.</flux:text>
                                    </div>
                                @endforelse
                            </div>

                            <div class="mt-4">
                                <x-passkey-registration />
                            </div>
                        </div>
                    @endif
                </section>

                <section id="aparencia" class="scroll-mt-6 rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 sm:p-8">
                    <div>
                        <flux:heading size="lg">Aparência</flux:heading>
                        <flux:subheading>Escolhe como o Finder aparece neste dispositivo.</flux:subheading>
                    </div>

                    <div class="mt-6">
                        <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
                            <flux:radio value="light" icon="sun">Claro</flux:radio>
                            <flux:radio value="dark" icon="moon">Escuro</flux:radio>
                            <flux:radio value="system" icon="computer-desktop">Sistema</flux:radio>
                        </flux:radio.group>
                    </div>
                </section>

                <section class="rounded-2xl border border-red-200 bg-red-50 p-6 dark:border-red-900/60 dark:bg-red-950/20 sm:p-8">
                    <div>
                        <flux:heading size="lg" class="text-red-900 dark:text-red-300">Terminar sessão</flux:heading>
                        <flux:subheading class="text-red-800 dark:text-red-400">Sair da tua conta neste dispositivo.</flux:subheading>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="mt-4">
                        @csrf
                        <flux:button type="submit" variant="danger">Terminar sessão</flux:button>
                    </form>
                </section>
            </main>
        </div>
    </div>

    @if ($canManagePasskeys)
        <flux:modal name="delete-passkey-modal" class="max-w-md" wire:model="showDeleteModal" @close="closeDeleteModal">
            <div class="space-y-6">
                <div class="space-y-2">
                    <flux:heading size="lg">Remover passkey</flux:heading>
                    <flux:text>Tens a certeza que queres remover a passkey "{{ $deletingPasskeyName }}"?</flux:text>
                </div>
                <div class="flex justify-end gap-3">
                    <flux:button variant="outline" wire:click="closeDeleteModal">Cancelar</flux:button>
                    <flux:button variant="danger" wire:click="deletePasskey">Remover passkey</flux:button>
                </div>
            </div>
        </flux:modal>
    @endif
</div>
