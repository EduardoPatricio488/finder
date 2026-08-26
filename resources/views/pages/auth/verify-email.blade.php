<x-layouts::auth title="Confirmar email">
    <div class="mt-4 flex flex-col gap-6">
        <div class="text-center">
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-amber-700">Finder</p>
            <h1 class="mt-3 text-2xl font-semibold text-stone-950">Confirme a sua conta</h1>
        </div>
        <flux:text class="text-center">
            Enviámos um link de confirmação para o seu email. Clique nesse link para ativar a sua conta.
        </flux:text>

        @if (session('status') == 'verification-link-sent')
            <flux:text class="text-center font-medium !dark:text-green-400 !text-green-600">
                Enviámos um novo link de confirmação para o email indicado no registo.
            </flux:text>
        @endif

        <div class="flex flex-col items-center justify-between space-y-3">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <flux:button type="submit" variant="primary" class="w-full">
                    Reenviar email de confirmação
                </flux:button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <flux:button variant="ghost" type="submit" class="text-sm cursor-pointer" data-test="logout-button">
                    Terminar sessão
                </flux:button>
            </form>
        </div>
    </div>
</x-layouts::auth>
