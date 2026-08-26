<div class="min-h-screen">
    <header class="border-b border-stone-900/10 bg-[#f4f1ea]/90 backdrop-blur-xl">
        <div class="mx-auto flex h-[4.75rem] max-w-7xl items-center justify-between gap-3 px-6 lg:px-12">
            <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-3">
                <span class="flex size-10 items-center justify-center rounded-2xl bg-stone-950 text-sm font-bold text-amber-200">F</span>
                <span>
                    <span class="block text-sm font-semibold tracking-tight">Finder</span>
                    <span class="block text-[10px] font-semibold uppercase tracking-[0.18em] text-stone-400">Gestão de sites</span>
                </span>
            </a>

            <div class="flex items-center gap-2 sm:gap-3">
                @if ($canCreateSites)
                    <button
                        type="button"
                        wire:click="create"
                        class="rounded-xl bg-stone-950 px-3 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-400 hover:text-stone-950"
                    >
                        Criar novo site
                    </button>
                @elseif ($canManageSites)
                    <a href="#planos" class="rounded-xl bg-white px-3 py-2 text-sm font-semibold text-stone-950 shadow-sm transition hover:bg-amber-100">Ver Premium</a>
                @endif

                @auth
                    <a href="{{ route('entry') }}" wire:navigate class="rounded-xl px-3 py-2 text-sm font-semibold text-stone-700 transition hover:bg-white">Entrar na área</a>
                @else
                    <a href="{{ route('login') }}" wire:navigate class="rounded-xl px-3 py-2 text-sm font-semibold text-stone-700 transition hover:bg-white">Entrar</a>
                    <a href="{{ route('register') }}" wire:navigate class="rounded-xl bg-white px-3 py-2 text-sm font-semibold text-stone-950 shadow-sm transition hover:bg-amber-100">Criar conta</a>
                @endauth
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-6 py-12 lg:px-12 lg:py-16">
        <div class="max-w-2xl">
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-amber-700">Página principal</p>
            <h1 class="mt-4 text-4xl font-semibold tracking-tight sm:text-5xl">Os seus sites, num só lugar.</h1>
            <p class="mt-5 max-w-xl text-lg leading-8 text-stone-600">Gerir cada projeto a partir daqui.</p>
            @auth
                <p class="mt-4 inline-flex rounded-full border border-stone-200 bg-white px-3 py-1.5 text-xs font-semibold text-stone-600">Plano atual: {{ $currentPlanLabel }}</p>
            @endauth
        </div>

        <section id="planos" class="mt-10 grid gap-4 lg:grid-cols-2">
            <article class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-stone-400">Finder Free</p>
                        <h2 class="mt-2 text-2xl font-semibold text-stone-950">Gestão básica</h2>
                    </div>
                    <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-semibold text-stone-600">Incluído</span>
                </div>
                <p class="mt-4 text-sm leading-6 text-stone-600">Acesso à conta, consulta dos sites disponíveis e gestão dos projetos onde já pertence.</p>
                <ul class="mt-5 space-y-2 text-sm text-stone-600">
                    <li>Consultar sites publicados</li>
                    <li>Entrar em sites onde já tem acesso</li>
                    <li>Conta e definições pessoais</li>
                </ul>
            </article>

            <article class="rounded-2xl border border-amber-300 bg-stone-950 p-6 text-white shadow-xl">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-amber-300">Finder Premium</p>
                        <h2 class="mt-2 text-2xl font-semibold">Criar múltiplos sites</h2>
                    </div>
                    <span class="rounded-full bg-amber-300 px-3 py-1 text-xs font-semibold text-stone-950">Premium</span>
                </div>
                <p class="mt-4 text-sm leading-6 text-stone-300">O plano necessário para criar novos sites com modelos prontos, incluindo o modelo Casa &amp; Co.</p>
                <ul class="mt-5 space-y-2 text-sm text-stone-300">
                    <li>Criar novos sites e lojas</li>
                    <li>Usar modelos prontos por tipo de negócio</li>
                    <li>Preparado para domínios, equipa e personalização</li>
                </ul>
                @if ($canCreateSites)
                    <button type="button" wire:click="create" class="mt-6 rounded-xl bg-amber-300 px-4 py-2.5 text-sm font-semibold text-stone-950 transition hover:bg-amber-200">Criar novo site</button>
                @else
                    <div class="mt-6 rounded-xl border border-white/10 bg-white/10 px-4 py-3 text-sm text-stone-200">Atualize para Finder Premium para desbloquear a criação de novos sites.</div>
                @endif
            </article>
        </section>

        @if (session('status'))
            <div class="mt-8 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">{{ session('status') }}</div>
        @endif

        @if ($showForm && $canCreateSites)
            <form wire:submit="save" class="mt-10 space-y-4 rounded-3xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-amber-700">{{ $editingSiteId ? 'Editar site' : 'Criar novo site' }}</p>
                        <h2 class="mt-2 text-2xl font-semibold tracking-tight">{{ $editingSiteId ? 'Atualizar este projeto' : 'Como se chama o seu site?' }}</h2>
                    </div>
                    <button type="button" wire:click="cancel" class="text-sm font-semibold text-stone-500 hover:text-stone-950">Cancelar</button>
                </div>

                @unless ($editingSiteId)
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ($siteTemplates as $templateKey => $template)
                            <button
                                type="button"
                                wire:click="chooseTemplate('{{ $templateKey }}')"
                                @class([
                                    'rounded-2xl border p-4 text-left transition hover:-translate-y-0.5 hover:border-amber-300 hover:bg-amber-50',
                                    'border-stone-950 bg-stone-950 text-white hover:bg-stone-900' => $selectedTemplate === $templateKey,
                                    'border-stone-200 bg-white text-stone-800' => $selectedTemplate !== $templateKey,
                                ])
                            >
                                <span class="block text-sm font-semibold">{{ $template['label'] }}</span>
                                <span @class([
                                    'mt-2 block text-xs leading-5',
                                    'text-stone-300' => $selectedTemplate === $templateKey,
                                    'text-stone-500' => $selectedTemplate !== $templateKey,
                                ])>{{ $template['description'] }}</span>
                            </button>
                        @endforeach
                    </div>
                @endunless

                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block sm:col-span-2">
                        <span class="mb-1.5 block text-sm font-medium text-stone-700">Nome</span>
                        <input wire:model="name" type="text" class="w-full rounded-xl border-stone-300 text-sm" placeholder="Casa & Co.">
                        @error('name') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
                    </label>
                    <label class="block">
                        <span class="mb-1.5 block text-sm font-medium text-stone-700">Identificador</span>
                        <input wire:model="slug" type="text" class="w-full rounded-xl border-stone-300 text-sm" placeholder="casa-co">
                        @error('slug') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
                    </label>
                    <label class="block">
                        <span class="mb-1.5 block text-sm font-medium text-stone-700">Tipo</span>
                        <select wire:model="type" class="w-full rounded-xl border-stone-300 text-sm">
                            <option value="online_store">Loja online</option>
                            <option value="restaurant">Restaurante</option>
                            <option value="services">Serviços</option>
                            <option value="portfolio">Portfólio</option>
                            <option value="blog">Blog</option>
                        </select>
                        @error('type') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
                    </label>
                    <label class="block sm:col-span-2">
                        <span class="mb-1.5 block text-sm font-medium text-stone-700">Frase de apresentação</span>
                        <input wire:model="tagline" type="text" class="w-full rounded-xl border-stone-300 text-sm" placeholder="Coisas bonitas para viver melhor.">
                        @error('tagline') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
                    </label>
                    <label class="block sm:col-span-2">
                        <span class="mb-1.5 block text-sm font-medium text-stone-700">Descrição</span>
                        <textarea wire:model="description" rows="3" class="w-full rounded-xl border-stone-300 text-sm"></textarea>
                        @error('description') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
                    </label>
                    <label class="block">
                        <span class="mb-1.5 block text-sm font-medium text-stone-700">Cor</span>
                        <select wire:model="accent" class="w-full rounded-xl border-stone-300 text-sm">
                            <option value="amber">Âmbar</option>
                            <option value="stone">Pedra</option>
                            <option value="emerald">Esmeralda</option>
                            <option value="sky">Céu</option>
                        </select>
                    </label>
                    <label class="block">
                        <span class="mb-1.5 block text-sm font-medium text-stone-700">Estado</span>
                        <select wire:model="status" class="w-full rounded-xl border-stone-300 text-sm">
                            <option value="online">Online</option>
                            <option value="offline">Offline</option>
                        </select>
                    </label>
                    <label class="block">
                        <span class="mb-1.5 block text-sm font-medium text-stone-700">Cor principal</span>
                        <input wire:model="primaryColor" type="color" class="h-10 w-full rounded-xl border-stone-300 text-sm">
                    </label>
                    <label class="block">
                        <span class="mb-1.5 block text-sm font-medium text-stone-700">Cor secundária</span>
                        <input wire:model="secondaryColor" type="color" class="h-10 w-full rounded-xl border-stone-300 text-sm">
                    </label>
                    <label class="flex items-center gap-3 pt-7">
                        <input wire:model="isPublished" type="checkbox" class="rounded border-stone-300 text-stone-950">
                        <span class="text-sm font-medium text-stone-700">Visível na página principal</span>
                    </label>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="rounded-xl bg-stone-950 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-amber-400 hover:text-stone-950">Guardar site</button>
                </div>
            </form>
        @endif

        <section class="mt-12 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($sites as $site)
                <article wire:key="site-{{ $site->id }}" class="store-card flex flex-col overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-sm">
                    <div @class([
                        'flex min-h-40 flex-col justify-between p-6 text-white',
                        'bg-stone-950' => $site->accent === 'amber',
                        'bg-stone-800' => $site->accent === 'stone',
                        'bg-emerald-900' => $site->accent === 'emerald',
                        'bg-sky-950' => $site->accent === 'sky',
                    ])>
                        <div class="flex items-center justify-between text-[11px] font-semibold uppercase tracking-[0.18em] text-white/70">
                            <span>{{ $site->category_label }}</span>
                            <span class="rounded-full bg-white/15 px-2 py-0.5">{{ $site->statusLabel() }}</span>
                        </div>
                        <div>
                            <h2 class="text-2xl font-semibold tracking-tight">{{ $site->name }}</h2>
                            <p class="mt-2 max-w-xs text-sm leading-6 text-white/75">{{ $site->tagline }}</p>
                        </div>
                    </div>
                    <div class="flex flex-1 flex-col justify-between gap-5 p-6">
                        <p class="text-sm leading-6 text-stone-600">{{ $site->description }}</p>
                        <dl class="grid grid-cols-3 gap-3 text-sm">
                            <div>
                                <dt class="text-xs text-stone-400">Produtos</dt>
                                <dd class="mt-1 font-semibold text-stone-900">{{ $site->products_count ?? 0 }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-stone-400">Vendas</dt>
                                <dd class="mt-1 font-semibold text-stone-900">{{ $site->sales_count ?? 0 }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-stone-400">Atividade</dt>
                                <dd class="mt-1 font-semibold text-stone-900">{{ $site->orders_max_updated_at ? \Illuminate\Support\Carbon::parse($site->orders_max_updated_at)->format('d/m') : 'Sem dados' }}</dd>
                            </div>
                        </dl>
                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ $site->destinationUrl() }}" wire:navigate class="inline-flex rounded-xl bg-stone-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-amber-400 hover:text-stone-950">
                                Abrir site
                            </a>
                            @if ($canManageSites)
                                <a href="{{ $site->adminUrl() }}" wire:navigate class="rounded-xl bg-amber-100 px-3 py-2 text-sm font-semibold text-stone-900 transition hover:bg-amber-200">Gerir</a>
                                <button type="button" wire:click="edit({{ $site->id }})" class="rounded-xl px-3 py-2 text-sm font-semibold text-stone-600 transition hover:bg-stone-100 hover:text-stone-950">Editar</button>
                                @unless ($site->isProtected())
                                    <button type="button" wire:click="delete({{ $site->id }})" wire:confirm="Remover este site da coleção?" class="rounded-xl px-3 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-50">Remover</button>
                                @endunless
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach

            @if ($canCreateSites)
                <button type="button" wire:click="create" class="flex min-h-72 flex-col items-center justify-center gap-3 rounded-3xl border border-dashed border-stone-300 bg-white/40 p-8 text-stone-500 transition hover:border-amber-400 hover:bg-white hover:text-stone-950">
                    <span class="flex size-12 items-center justify-center rounded-full border border-current text-2xl">+</span>
                    <span class="text-sm font-semibold">Criar novo site</span>
                </button>
            @elseif ($canManageSites)
                <a href="#planos" class="flex min-h-72 flex-col items-center justify-center gap-3 rounded-3xl border border-dashed border-amber-300 bg-amber-50/70 p-8 text-center text-stone-700 transition hover:bg-amber-100">
                    <span class="text-sm font-semibold text-amber-800">Finder Premium</span>
                    <span class="max-w-xs text-sm">Só com Premium pode adicionar um novo site.</span>
                </a>
            @endif
        </section>
    </main>
</div>
