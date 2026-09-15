<x-layouts::auth :title="__('Criar Conta')">
    <div class="flex flex-col gap-9 py-4">
        {{-- HEADER --}}
        <div class="text-left">
            <div class="mb-6 inline-flex size-12 items-center justify-center rounded-2xl bg-stone-950 text-amber-400 shadow-xl">
                <span class="text-2xl font-black italic">F</span>
            </div>
            <h1 class="text-3xl font-black italic leading-none tracking-tight text-stone-950 sm:text-4xl">
                Criar conta no Finder
            </h1>
            <p class="mt-3 max-w-sm text-sm leading-relaxed text-stone-600">
                Começa hoje a gerir os teus sites e lojas num ecossistema profissional.
            </p>
        </div>

        {{-- STATUS DA SESSÃO --}}
        <x-auth-session-status class="text-left text-xs font-semibold text-red-600" :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-5">
            @csrf

            <!-- NOME -->
            <flux:input
                name="name"
                label="Nome completo"
                :value="old('name')"
                type="text"
                required
                autofocus
                placeholder="Ex: João Cândido"
            />

            <!-- EMAIL -->
            <flux:input
                name="email"
                label="Endereço de email"
                :value="old('email')"
                type="email"
                required
                placeholder="nome@exemplo.com"
            />

            <!-- GRID DE PASSWORDS -->
            <div class="grid gap-5 sm:grid-cols-2">
                <flux:input
                    name="password"
                    label="Palavra-passe"
                    type="password"
                    required
                    viewable
                    placeholder="••••••••"
                />

                <flux:input
                    name="password_confirmation"
                    label="Confirmar palavra-passe"
                    type="password"
                    required
                    viewable
                    placeholder="••••••••"
                />
            </div>

            {{-- TERMOS --}}
            <p class="px-0.5 text-xs leading-relaxed text-stone-500">
                Ao clicar em <span class="font-semibold text-stone-700">Criar conta</span>, aceitas os nossos
                <a href="#" class="font-semibold text-stone-900 underline underline-offset-2 hover:text-amber-700">Termos de Serviço</a>
                e a nossa política de privacidade.
            </p>

            <div class="mt-2">
                <flux:button
                    type="submit"
                    variant="primary"
                    class="w-full rounded-2xl !bg-stone-950 py-6 text-xs font-bold uppercase tracking-[0.2em] text-white shadow-lg transition-all hover:!bg-black hover:-translate-y-0.5 active:scale-[0.98]"
                >
                    Criar conta
                </flux:button>
            </div>
        </form>

        {{-- FOOTER --}}
        <div class="border-t border-stone-200 pt-6 text-center">
            <span class="text-xs text-stone-500">{{ __('Já tens uma conta?') }}</span>
            <flux:link
                :href="route('login')"
                wire:navigate
                class="ml-1 text-xs font-bold text-amber-700 hover:text-stone-950"
            >
                {{ __('Entrar aqui') }}
            </flux:link>
        </div>

        <a
            href="{{ route('home') }}"
            class="text-center text-[11px] font-bold uppercase tracking-[0.25em] text-stone-400 transition-colors hover:text-amber-700"
        >
            &larr; Voltar ao Hub
        </a>
    </div>
</x-layouts::auth>
