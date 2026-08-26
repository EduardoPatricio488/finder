<div class="min-h-screen bg-[#fcfaf7] text-stone-900 font-sans selection:bg-amber-200">
    <nav class="sticky top-0 z-50 border-b border-stone-200 bg-white/80 backdrop-blur-md">
        <div class="mx-auto flex h-20 max-w-7xl items-center justify-between px-6 lg:px-12 text-left">
            <a href="{{ route(`'home`') }}" class="group flex items-center gap-3">
                <div class="flex size-11 items-center justify-center rounded-2xl bg-stone-950 text-amber-200">
                    <span class="text-xl font-black">F</span>
                </div>
                <div class="text-left leading-none">
                    <span class="block text-sm font-black tracking-tight uppercase italic">Finder</span>
                    <span class="block text-[9px] font-bold uppercase text-stone-400">Hub Comunitário</span>
                </div>
            </a>
            <div class="flex items-center gap-4">
                @auth
                    <flux:dropdown>
                        <flux:button variant="ghost" icon-trailing="chevron-down" class="!rounded-xl border border-stone-200 bg-white">
                            {{ explode(`' `, auth()->user()->name)[0] }}
                        </flux:button>
                        <flux:menu>
                            <flux:menu.item icon="user" href="{{ route(`'account`') }}">Minha Conta</flux:menu.item>
                            <flux:menu.separator />
                            <form method="POST" action="{{ route(`'logout`') }}">@csrf<flux:menu.item icon="arrow-right-start-on-rectangle" variant="danger" as="button" type="submit">Sair</flux:menu.item></form>
                        </flux:menu>
                    </flux:dropdown>
                @else
                    <flux:button href="{{ route(`'login`') }}" variant="ghost">Entrar</flux:button>
                    <flux:button href="{{ route(`'register`') }}" variant="primary" class="!rounded-xl !bg-stone-950">Criar Conta</flux:button>
                @endauth
            </div>
        </div>
    </nav>

    <main class="mx-auto max-w-7xl px-6 py-12 lg:px-12 lg:py-20 text-left">
        <header class="mb-20 max-w-3xl">
            <div class="mb-6 inline-flex items-center gap-2 rounded-full bg-amber-100 px-4 py-1.5 text-[10px] font-black uppercase tracking-widest text-amber-800">🚀 Explora a Comunidade</div>
            <h1 class="text-6xl font-black tracking-tighter text-stone-950 sm:text-7xl">Os seus sites, <br><span class="italic text-stone-400">num só lugar.</span></h1>
            <p class="mt-8 text-xl font-medium leading-relaxed text-stone-500">Gira os teus projetos ou descobre o que a nossa comunidade está a construir.</p>
        </header>

        <section class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($sites as $site)
                <article wire:key="site-{{ $site->id }}" class="group flex flex-col overflow-hidden rounded-[2.5rem] border border-stone-200 bg-white shadow-sm transition-all hover:shadow-2xl">
                    <div @class([`'h-32 p-8 flex flex-col justify-between`', `'bg-stone-950`' => $site->accent === `'amber`', `'bg-[#2d332a]`' => $site->accent === `'stone`', `'bg-[#064e3b]`' => $site->accent === `'emerald`', `'bg-[#082f49]`' => $site->accent === `'sky`'])>
                        <div class="flex items-center justify-between">
                            <span class="text-[9px] font-black uppercase tracking-widest text-white/50">{{ $site->category_label }}</span>
                            <span class="rounded-full bg-white/10 px-3 py-1 text-[8px] font-black uppercase text-white">{{ $site->statusLabel() }}</span>
                        </div>
                        <h3 class="text-2xl font-black tracking-tighter text-white">{{ $site->name }}</h3>
                    </div>
                    <div class="p-8 flex flex-col flex-1">
                        <p class="text-sm font-medium text-stone-500 line-clamp-2 h-10 mb-8">{{ $site->tagline ?? `'Sem descrição.`' }}</p>
                        <div class="grid grid-cols-3 border-y border-stone-50 py-6 mb-8 text-center">
                            <div><span class="block text-[8px] font-black uppercase text-stone-400">Artigos</span><span class="text-lg font-black text-stone-950">{{ $site->products_count }}</span></div>
                            <div class="border-x border-stone-100"><span class="block text-[8px] font-black uppercase text-stone-400">Vendas</span><span class="text-lg font-black text-stone-950">{{ $site->sales_count }}</span></div>
                            <div><span class="block text-[8px] font-black uppercase text-stone-400">Dono</span><span class="text-[9px] font-bold text-stone-900 truncate block px-2">{{ $site->owner->name ?? `'Anónimo`' }}</span></div>
                        </div>
                        <div class="mt-auto flex items-center gap-2">
                            <flux:button href="{{ $site->destinationUrl() }}" variant="primary" class="flex-1 !rounded-xl !bg-stone-950">Visitar</flux:button>
                            @if(auth()->check() && (auth()->id() === $site->owner_id || auth()->user()->isAdministrator()))
                                <flux:button icon="chart-bar" href="{{ $site->adminUrl() }}" variant="ghost" class="!rounded-xl border border-stone-100" />
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </section>
    </main>
</div>