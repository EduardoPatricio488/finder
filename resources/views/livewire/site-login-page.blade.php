@php
    $accent = $site->theme['primary'] ?? $site->primary_color ?? '#4f46e5';
    $homeUrl = route('site.public', ['site' => $site, 'pageSlug' => null]);
@endphp

<div class="min-h-screen bg-zinc-50 px-4 py-10 sm:px-6 lg:py-16" style="--finder-primary: {{ $accent }}">
    <div class="mx-auto max-w-6xl">
        <div class="mb-8 flex items-center justify-between gap-4">
            <a href="{{ $homeUrl }}" class="inline-flex min-w-0 items-center gap-3">
                <span class="flex size-11 shrink-0 items-center justify-center rounded-2xl text-lg font-black text-white shadow-sm" style="background: {{ $accent }}">{{ mb_strtoupper(mb_substr($site->name, 0, 1)) }}</span>
                <span class="min-w-0">
                    <span class="block truncate text-lg font-extrabold tracking-tight text-zinc-950">{{ $site->name }}</span>
                    <span class="block text-xs text-zinc-500">A tua conta</span>
                </span>
            </a>
            <a href="{{ $homeUrl }}" class="rounded-xl px-3 py-2 text-sm font-semibold text-zinc-600 transition hover:bg-white hover:text-zinc-950">← Voltar ao website</a>
        </div>

        <div class="grid overflow-hidden rounded-[2rem] border border-zinc-200 bg-white shadow-xl shadow-zinc-200/50 lg:min-h-[620px] lg:grid-cols-2">
            <section class="relative flex flex-col justify-between overflow-hidden p-8 text-white sm:p-12 lg:p-14" style="background: linear-gradient(145deg, {{ $accent }}, color-mix(in srgb, {{ $accent }} 68%, #18181b))">
                <div class="pointer-events-none absolute -right-24 -top-24 size-80 rounded-full border border-white/15"></div>
                <div class="pointer-events-none absolute -right-8 -top-8 size-48 rounded-full border border-white/15"></div>
                <div class="relative">
                    <span class="inline-flex items-center gap-2 rounded-full border border-white/25 bg-white/10 px-3 py-1.5 text-xs font-semibold tracking-wide text-white/90">
                        <span class="size-1.5 rounded-full bg-white"></span> Bem-vindo de volta
                    </span>
                    <h1 class="mt-10 max-w-md text-4xl font-black leading-tight tracking-tight sm:text-5xl">Entra na tua conta.</h1>
                    <p class="mt-5 max-w-md text-base leading-7 text-white/80">Acede à tua área pessoal de forma simples e continua de onde ficaste.</p>
                </div>
                <div class="relative mt-14 border-t border-white/20 pt-5 text-sm text-white/70">
                    <p>Bem-vindo ao {{ $site->name }}.</p>
                </div>
            </section>

            <section class="flex items-center justify-center p-6 sm:p-10 lg:p-14">
                <div class="w-full max-w-md">
                    <div class="mb-8">
                        <p class="text-sm font-bold uppercase tracking-[.18em]" style="color: {{ $accent }}">Área de cliente</p>
                        <h2 class="mt-2 text-3xl font-extrabold tracking-tight text-zinc-950">Iniciar sessão</h2>
                        <p class="mt-2 text-sm leading-6 text-zinc-500">Introduz os teus dados para continuares.</p>
                    </div>

                    @if (session('status'))
                        <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="space-y-5">
                        @csrf
                        <div>
                            <label for="email" class="mb-2 block text-sm font-semibold text-zinc-800">Email</label>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus class="w-full rounded-xl border border-zinc-200 bg-white px-4 py-3.5 text-sm text-zinc-950 outline-none transition placeholder:text-zinc-400 focus:ring-4" style="--tw-ring-color: color-mix(in srgb, {{ $accent }} 18%, transparent)" placeholder="nome@exemplo.pt">
                            @error('email') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <div class="mb-2 flex items-center justify-between gap-3">
                                <label for="password" class="block text-sm font-semibold text-zinc-800">Palavra-passe</label>
                                @if (\Illuminate\Support\Facades\Route::has('password.request'))
                                    <a href="{{ route('password.request') }}" class="text-xs font-semibold hover:underline" style="color: {{ $accent }}">Esqueceste-te?</a>
                                @endif
                            </div>
                            <input id="password" name="password" type="password" autocomplete="current-password" required class="w-full rounded-xl border border-zinc-200 bg-white px-4 py-3.5 text-sm text-zinc-950 outline-none transition focus:ring-4" style="--tw-ring-color: color-mix(in srgb, {{ $accent }} 18%, transparent)" placeholder="A tua palavra-passe">
                            @error('password') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <label class="flex cursor-pointer items-center gap-2.5 text-sm text-zinc-600">
                            <input type="checkbox" name="remember" value="1" class="size-4 rounded border-zinc-300" style="accent-color: {{ $accent }}">
                            Manter sessão iniciada
                        </label>
                        <button type="submit" class="flex w-full items-center justify-center rounded-xl px-5 py-3.5 text-sm font-bold text-white shadow-lg transition hover:brightness-95 focus:outline-none focus:ring-4" style="background: {{ $accent }}; --tw-ring-color: color-mix(in srgb, {{ $accent }} 22%, transparent)">
                            Entrar na minha conta <span class="ml-2" aria-hidden="true">→</span>
                        </button>
                    </form>

                    @if (Route::has('register'))
                        <p class="mt-7 text-center text-sm text-zinc-500">Ainda não tens conta?
                            <a href="{{ route('register') }}" class="font-bold hover:underline" style="color: {{ $accent }}">Criar conta</a>
                        </p>
                    @endif
                    <p class="mt-8 text-center text-xs leading-5 text-zinc-400">A tua sessão é protegida. Nunca partilhes a tua palavra-passe.</p>
                </div>
            </section>
        </div>
        <p class="mt-6 text-center text-xs text-zinc-400">© {{ date('Y') }} {{ $site->name }}</p>
    </div>
</div>
