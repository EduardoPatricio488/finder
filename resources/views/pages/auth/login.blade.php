<x-layouts::auth :title="__('Entrar')">
    <div class="flex flex-col gap-6">
        <div class="text-center">
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-amber-700">Finder</p>
            <h1 class="mt-3 text-3xl font-black tracking-tight text-stone-950">Entrar na sua conta</h1>
            <p class="mt-2 text-sm text-stone-500">Gestão de sites</p>
            <p class="mt-1 text-sm text-stone-500">Os seus sites, num só lugar.</p>
        </div>

        <x-auth-session-status class="text-center" :status="session('status')" />

        <x-passkey-verify />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6">
            @csrf

            <flux:input
                name="email"
                :label="__('Email')"
                :value="old('email')"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="email@example.com"
            />

            <div class="relative">
                <flux:input
                    name="password"
                    :label="__('Palavra-passe')"
                    type="password"
                    required
                    autocomplete="current-password"
                    :placeholder="__('Palavra-passe')"
                    viewable
                />

                @if (Route::has('password.request'))
                    <flux:link class="absolute top-0 text-sm end-0" :href="route('password.request')" wire:navigate>
                        {{ __('Esqueceu-se da palavra-passe?') }}
                    </flux:link>
                @endif
            </div>

            <flux:checkbox name="remember" :label="__('Manter sessão iniciada')" :checked="old('remember')" />

            <div class="flex items-center justify-end">
                <flux:button variant="primary" type="submit" class="w-full" data-test="login-button">
                    Entrar
                </flux:button>
            </div>
        </form>

        <div class="space-x-1 text-center text-sm text-stone-500 rtl:space-x-reverse">
            <span>Ainda não tem conta?</span>
            <flux:link :href="route('register')" wire:navigate>Criar conta</flux:link>
        </div>

        <div class="text-center">
            <a href="{{ route('home') }}" wire:navigate class="text-sm font-semibold text-stone-500 hover:text-stone-950">Voltar aos sites</a>
        </div>
    </div>
</x-layouts::auth>
