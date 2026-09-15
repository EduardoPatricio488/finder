<div class="min-h-screen bg-[#fcfaf7] text-stone-900 font-sans selection:bg-amber-200">
    <nav class="sticky top-0 z-50 border-b border-stone-200 bg-white/80 backdrop-blur-md">
        <div class="mx-auto flex h-20 max-w-7xl items-center justify-between px-6 lg:px-12 text-left">
            <a href="{{ route('home') }}" class="group flex items-center gap-3">
                <div class="flex size-11 items-center justify-center rounded-2xl bg-stone-950 text-amber-200 shadow-lg shadow-stone-950/20 transition-transform group-hover:rotate-3">
                    <span class="text-xl font-black">F</span>
                </div>
                <div class="text-left leading-none">
                    <span class="block text-sm font-black tracking-tight uppercase italic">Finder</span>
                    <span class="block text-[9px] font-bold uppercase tracking-[0.2em] text-stone-400">Hub Central</span>
                </div>
            </a>

            <div class="flex items-center gap-4">
                <a href="{{ route('sales') }}" class="hidden sm:inline-flex text-[10px] font-black uppercase tracking-[0.2em] text-stone-500 hover:text-stone-950">Finder Premium</a>
                @auth
                    <div class="hidden sm:flex flex-col items-end leading-none">
                        <span class="text-[9px] font-black uppercase tracking-widest text-stone-400">Plano Atual</span>
                        <span class="mt-1 text-xs font-bold text-amber-700">{{ $currentPlanLabel }}</span>
                    </div>
                    <div class="h-8 w-px bg-stone-200 mx-2"></div>
                    <flux:dropdown>
                        <flux:button variant="ghost" class="!rounded-2xl border border-stone-200 bg-white !p-1.5">
                            <div class="flex items-center gap-3 pr-2">
                                <flux:avatar initials="{{ substr(auth()->user()->name, 0, 2) }}" size="sm" class="!rounded-xl bg-stone-800 text-white shadow-sm" />
                                <flux:icon name="chevron-down" variant="micro" class="text-stone-400" />
                            </div>
                        </flux:button>
                        <flux:menu class="min-w-[200px]">
                            <flux:menu.item icon="user" href="{{ route('account') }}">Minha Conta</flux:menu.item>
                            @if($canCreateSites) <flux:menu.item icon="plus" wire:click="create">Novo Projeto</flux:menu.item> @endif
                            <flux:menu.separator />
                            <form method="POST" action="{{ route('logout') }}" class="w-full">
                                @csrf
                                <flux:menu.item icon="arrow-right-start-on-rectangle" variant="danger" as="button" type="submit" class="w-full">Sair</flux:menu.item>
                            </form>
                        </flux:menu>
                    </flux:dropdown>
                @else
                    <div class="flex items-center gap-6">
                        <a href="{{ route('login') }}" wire:navigate class="text-[10px] font-black uppercase tracking-[0.2em] text-stone-500 hover:text-stone-900 transition-colors">Entrar</a>
                        <flux:button href="{{ route('register') }}" variant="primary" class="!rounded-xl !bg-stone-950 !px-6 !py-2.5 !text-[10px] !font-black !uppercase !tracking-[0.2em] !text-white shadow-xl shadow-stone-950/20 hover:!bg-stone-800 transition-all">Criar Conta</flux:button>
                    </div>
                @endauth
            </div>
        </div>
    </nav>

    <main class="mx-auto max-w-7xl px-6 py-12 lg:px-12 lg:py-20 text-left">
        <header class="mb-24 flex flex-col items-start justify-between gap-10 md:flex-row md:items-end">
            <div class="max-w-2xl text-left">
                <div class="mb-6 inline-flex items-center gap-2 rounded-full bg-amber-100 px-4 py-1.5 text-[10px] font-black uppercase tracking-widest text-amber-800">✨ Comunidade Finder</div>
                <h1 class="text-6xl font-black tracking-tighter text-stone-950 sm:text-7xl leading-[0.9]">Os teus sites, <br><span class="italic text-stone-300">num só lugar.</span></h1>
                <p class="mt-8 text-xl font-medium leading-relaxed text-stone-500">Gere os teus projetos ou descobre o que a comunidade está a construir.</p>
            </div>

            @auth
                @if ($canCreateSites)
                    <flux:button wire:click="create" variant="primary" class="!rounded-2xl !bg-stone-950 !px-10 !py-5 shadow-2xl hover:scale-105 transition-all font-black uppercase tracking-widest text-xs">Lançar Novo Site</flux:button>
                @else
                    <flux:button href="{{ route('saas.upgrade') }}" variant="primary" class="!rounded-2xl !bg-amber-400 !text-stone-950 !px-10 !py-5 shadow-2xl hover:scale-105 transition-all font-black uppercase tracking-widest text-xs border-none">Obter Premium para Criar</flux:button>
                @endif
            @endauth
        </header>

        <section class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
            @forelse ($sites as $site)
                <article wire:key="site-{{ $site->id }}" class="group flex flex-col overflow-hidden rounded-[2.5rem] border border-stone-200 bg-white transition-all hover:shadow-2xl hover:-translate-y-1">
                    <div @class([
                        'relative h-44 p-8 flex flex-col justify-between overflow-hidden text-white',
                        'bg-gradient-to-br from-amber-400 to-orange-500' => $site->accent === 'amber',
                        'bg-gradient-to-br from-stone-600 to-stone-800' => $site->accent === 'stone',
                        'bg-gradient-to-br from-emerald-400 to-teal-600' => $site->accent === 'emerald',
                        'bg-gradient-to-br from-sky-400 to-indigo-600' => $site->accent === 'sky',
                        'bg-gradient-to-br from-indigo-500 to-violet-700' => ! in_array($site->accent, ['amber', 'stone', 'emerald', 'sky'], true),
                    ])>
                        <div class="absolute -right-6 -top-6 size-32 rounded-full bg-white/20 blur-2xl group-hover:scale-150 transition-transform duration-700"></div>
                        <div class="absolute -left-10 -bottom-10 size-32 rounded-full bg-black/10 blur-xl"></div>

                        <div class="relative z-10 flex items-center justify-between">
                            <span class="rounded-full bg-white/20 px-3 py-1 text-[9px] font-black uppercase tracking-widest backdrop-blur-md">{{ str_replace('_', ' ', $site->type) }}</span>
                            <div class="flex items-center gap-1.5 rounded-full bg-emerald-500/90 px-2 py-0.5 text-[9px] font-black uppercase shadow-sm">
                                <span class="size-1.5 rounded-full bg-white animate-pulse"></span>
                                {{ auth()->check() && auth()->user()->isAdministrator() ? 'Online' : $site->statusLabel() }}
                            </div>
                        </div>

                        <div class="relative z-10">
                            <h3 class="text-3xl font-black tracking-tighter drop-shadow-sm">{{ $site->name }}</h3>
                            <p class="mt-1 text-[10px] font-bold uppercase tracking-[0.2em] opacity-80">{{ $site->category_label }}</p>
                        </div>
                    </div>

                    <div class="flex flex-1 flex-col p-8 text-left">
                        <p class="text-sm font-medium leading-relaxed text-stone-500 line-clamp-2 h-10 mb-8 italic">"{{ $site->tagline ?? 'Um projeto em desenvolvimento.' }}"</p>

                        <div class="grid grid-cols-2 gap-4 mb-8">
                            <div class="rounded-2xl bg-stone-50 p-4 border border-stone-100 transition-colors group-hover:bg-white group-hover:border-amber-200">
                                <div class="flex items-center gap-2 mb-1"><flux:icon name="shopping-bag" variant="micro" class="text-amber-500 size-4" /><span class="text-[9px] font-black uppercase text-stone-400 tracking-widest">Catálogo</span></div>
                                <p class="text-lg font-black text-stone-900">{{ $site->products_count }} <small class="text-[10px] text-stone-400 font-bold uppercase">Itens</small></p>
                            </div>
                            <div class="rounded-2xl bg-stone-50 p-4 border border-stone-100 transition-colors group-hover:bg-white group-hover:border-emerald-200">
                                <div class="flex items-center gap-2 mb-1"><flux:icon name="chart-bar" variant="micro" class="text-emerald-500 size-4" /><span class="text-[9px] font-black uppercase text-stone-400 tracking-widest">Vendas</span></div>
                                <p class="text-lg font-black text-stone-900">{{ $site->sales_count }} <small class="text-[10px] text-stone-400 font-bold uppercase">Ordens</small></p>
                            </div>
                        </div>

                        <div class="mt-auto flex items-center justify-between pt-6 border-t border-stone-100">
                            <div class="flex items-center gap-3">
                                <div class="size-8 rounded-full bg-stone-100 border border-stone-200 flex items-center justify-center text-[10px] font-black text-stone-500 uppercase shadow-inner">{{ substr($site->owner->name ?? 'A', 0, 2) }}</div>
                                <div class="leading-none">
                                    <span class="block text-[10px] font-black text-stone-400 uppercase tracking-widest mb-0.5">Criador</span>
                                    <span class="text-xs font-bold text-stone-900">{{ $site->owner->name ?? 'Anónimo' }}</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                @if(auth()->check() && (auth()->id() === $site->owner_id || auth()->user()->isAdministrator()))
                                    <flux:button icon="chart-bar" href="{{ $site->adminUrl() }}" variant="ghost" class="!rounded-xl border border-stone-100" />
                                @endif
                                <flux:button href="{{ $site->destinationUrl() }}" variant="primary" class="!rounded-xl !py-2.5 !px-5 !font-black !uppercase !text-[10px] !tracking-widest !border-none shadow-lg transition-all hover:scale-105 bg-stone-950 text-white">Visitar</flux:button>
                            </div>
                        </div>
                    </div>
                </article>
            @empty
                <div class="md:col-span-2 lg:col-span-3 rounded-[2rem] border border-dashed border-stone-300 bg-white p-12 text-center">
                    <h2 class="text-2xl font-black tracking-tight">Ainda não há websites publicados.</h2>
                    <p class="mt-2 text-sm text-stone-500">Quando um website estiver publicado, aparecerá aqui.</p>
                    <a href="{{ route('sales') }}" class="mt-6 inline-flex rounded-xl bg-stone-950 px-5 py-3 text-xs font-black uppercase tracking-widest text-white">Conhecer o Finder Premium</a>
                </div>
            @endforelse
        </section>

        <div id="planos" class="mt-32 pt-10"></div>
    </main>
</div>
