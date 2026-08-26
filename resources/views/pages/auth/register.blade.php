<x-layouts::auth :title="__('Criar Conta')">
    <div class="flex flex-col gap-10 py-4">
        {{-- HEADER COM TEXTO A PRETO --}}
        <div class="text-left">
            <div class="inline-flex items-center justify-center size-12 rounded-2xl bg-stone-950 text-amber-400 mb-6 shadow-xl">
                <span class="text-2xl font-black italic">F</span>
            </div>
            <h1 class="text-4xl font-black tracking-tighter text-black uppercase italic leading-none">
                Criar Conta <br><span class="text-stone-950">no Finder</span>
            </h1>
            <p class="mt-4 text-sm font-bold text-black leading-relaxed">
                Começa hoje a gerir os teus sites e lojas num ecossistema profissional.
            </p>
        </div>

        {{-- STATUS DA SESSÃO --}}
        <x-auth-session-status class="text-left text-xs font-black text-red-600 uppercase tracking-widest" :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-5">
            @csrf

            <!-- NOME -->
            <flux:input
                name="name"
                label="NOME COMPLETO"
                :value="old('name')"
                type="text"
                required
                autofocus
                placeholder="Ex: João Cândido"
                class="!bg-white !text-black shadow-sm font-bold"
            />

            <!-- EMAIL -->
            <flux:input
                name="email"
                label="ENDEREÇO DE EMAIL"
                :value="old('email')"
                type="email"
                required
                placeholder="nome@exemplo.com"
                class="!bg-white !text-black shadow-sm font-bold"
            />

            <!-- GRID DE PASSWORDS -->
            <div class="grid gap-5 sm:grid-cols-2">
                <flux:input
                    name="password"
                    label="PALAVRA-PASSE"
                    type="password"
                    required
                    viewable
                    placeholder="••••••••"
                    class="!bg-white !text-black shadow-sm font-bold"
                />

                <flux:input
                    name="password_confirmation"
                    label="CONFIRMAR"
                    type="password"
                    required
                    viewable
                    placeholder="••••••••"
                    class="!bg-white !text-black shadow-sm font-bold"
                />
            </div>

            {{-- TERMOS A PRETO --}}
            <p class="text-[10px] text-black font-bold leading-relaxed px-1 uppercase tracking-tight">
                Ao clicar em criar conta, aceitas os nossos <a href="#" class="text-black underline font-black">Termos de Serviço</a> e a nossa política de privacidade.
            </p>

            <div class="mt-4">
                <flux:button
                    type="submit"
                    variant="primary"
                    class="w-full !rounded-2xl !bg-stone-950 !py-6 !text-[11px] !font-black !uppercase !tracking-[0.3em] !text-white shadow-2xl hover:!bg-black transition-all transform hover:-translate-y-0.5 active:scale-95 border-none"
                >
                    Finalizar Registo 🚀
                </flux:button>
            </div>
        </form>

        {{-- FOOTER COM ALTO CONTRASTE --}}
        <div class="text-center pt-6 border-t border-stone-200">
            <span class="text-xs font-bold text-black uppercase tracking-tight">{{ __('Já tens uma conta?') }}</span>
            <flux:link
                :href="route('login')"
                wire:navigate
                class="ml-1 text-xs font-black uppercase tracking-widest text-amber-700 hover:text-black transition-colors"
            >
                {{ __('Entrar aqui') }}
            </flux:link>
        </div>

        <a href="{{ route('home') }}" class="text-center text-[10px] font-black uppercase tracking-[0.3em] text-black hover:text-amber-700 transition-colors">
            &larr; Voltar ao Hub
        </a>
    </div>
</x-layouts::auth>
