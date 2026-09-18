<div class="min-h-screen bg-zinc-50 px-4 py-8 text-zinc-950 dark:bg-zinc-950 dark:text-white sm:px-6 lg:px-8">
    <div class="mx-auto max-w-6xl" x-data="{ help: false }">
        <div class="mb-8 flex items-start justify-between gap-4">
            <div>
                <a href="{{ route('dashboard') }}" wire:navigate class="text-xs font-bold uppercase tracking-widest text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200">← Finder</a>
                <h1 class="mt-3 text-3xl font-black tracking-tight sm:text-4xl">Vamos criar o teu website</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-zinc-500 dark:text-zinc-400">O Finder adapta a criação ao tipo de website que escolheres, com uma experiência simples, moderna e orientada ao resultado.</p>
            </div>
            <button type="button" @click="help=true" class="flex size-10 shrink-0 items-center justify-center rounded-xl border border-zinc-200 bg-white text-zinc-500 shadow-sm hover:bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-900" title="Como funciona?"><flux:icon name="question-mark-circle" class="size-5" /></button>
        </div>

        <div class="mb-8 grid grid-cols-4 gap-2">
            @foreach(['Começar','Modelo','Informação','Páginas'] as $index => $label)
                @php($number=$index+1)
                <div><div class="flex items-center gap-2"><span class="flex size-7 items-center justify-center rounded-full text-[10px] font-black {{ $step >= $number ? 'bg-indigo-600 text-white' : 'bg-zinc-200 text-zinc-500 dark:bg-zinc-800' }}">{{ $step > $number ? '✓' : $number }}</span><span class="hidden text-xs font-bold sm:inline">{{ $label }}</span></div><div class="mt-2 h-1 rounded-full {{ $step >= $number ? 'bg-indigo-600' : 'bg-zinc-200 dark:bg-zinc-800' }}"></div></div>
            @endforeach
        </div>

        @if($step === 1)
            <section class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-8">
                <p class="text-xs font-black uppercase tracking-widest text-indigo-600">1 · Começar</p>
                <h2 class="mt-2 text-2xl font-black">Como queres criar o website?</h2>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-zinc-500">Usamos o modelo pessoal Studio e deixamos a IA transformar a tua informação num website pronto a apresentar.</p>
                <div class="mt-8 grid gap-4 lg:grid-cols-3">
                    <div class="relative cursor-not-allowed rounded-2xl border border-zinc-200 bg-zinc-50 p-5 opacity-50 dark:border-zinc-700 dark:bg-zinc-800/60" aria-disabled="true"><span class="absolute right-4 top-4 rounded-full bg-zinc-200 px-2.5 py-1 text-[10px] font-black uppercase tracking-wide text-zinc-500 dark:bg-zinc-700 dark:text-zinc-300">Bloqueado</span><span class="flex size-11 items-center justify-center rounded-xl bg-zinc-200 text-zinc-400 dark:bg-zinc-700"><flux:icon name="sparkles" class="size-5" /></span><h3 class="mt-4 font-bold">Criar com AI</h3><p class="mt-1 text-xs leading-5 text-zinc-500">Opção avançada bloqueada. A geração inteligente já está incluída no modelo pessoal.</p></div>
                    <button type="button" wire:click="selectCreationMode('ai')" class="relative rounded-2xl border border-indigo-500 bg-indigo-50/70 p-5 text-left ring-2 ring-indigo-500/15 dark:bg-indigo-500/10"><span class="absolute right-4 top-4 flex size-6 items-center justify-center rounded-full bg-indigo-600 text-xs font-black text-white">✓</span><span class="flex size-11 items-center justify-center rounded-xl bg-indigo-600 text-white"><flux:icon name="squares-2x2" class="size-5" /></span><h3 class="mt-4 font-bold">Modelo pessoal</h3><p class="mt-1 text-xs leading-5 text-zinc-500">A IA personaliza automaticamente o modelo Studio com o que nos contares.</p></button>
                    <div class="relative cursor-not-allowed rounded-2xl border border-zinc-200 bg-zinc-50 p-5 opacity-50 dark:border-zinc-700 dark:bg-zinc-800/60" aria-disabled="true"><span class="absolute right-4 top-4 rounded-full bg-zinc-200 px-2.5 py-1 text-[10px] font-black uppercase tracking-wide text-zinc-500 dark:bg-zinc-700 dark:text-zinc-300">Bloqueado</span><span class="flex size-11 items-center justify-center rounded-xl bg-zinc-200 text-zinc-400 dark:bg-zinc-700"><flux:icon name="adjustments-horizontal" class="size-5" /></span><h3 class="mt-4 font-bold">Começar de forma livre</h3><p class="mt-1 text-xs leading-5 text-zinc-500">Não é necessário começar de uma página vazia.</p></div>
                </div>
                <div class="mt-8 flex justify-end"><flux:button variant="primary" icon:trailing="arrow-right" wire:click="next">Continuar</flux:button></div>
            </section>
        @elseif($step === 2)
            <section class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-8">
                <p class="text-xs font-black uppercase tracking-widest text-indigo-600">2 · Modelo e tipo</p><h2 class="mt-2 text-2xl font-black">Escolhe o tipo de website</h2><p class="mt-2 text-sm text-zinc-500">O Finder adapta o modelo e os campos seguintes ao tipo que escolheres.</p>
                <h3 class="mt-7 text-sm font-bold">Tipo de website</h3>
                <div class="mt-3 grid gap-2 sm:grid-cols-3 lg:grid-cols-6">
                    @foreach($types as $key=>$item)
                        @if(in_array($key, ['personal', 'online_store'], true))
                            <button type="button" wire:click="$set('type','{{ $key }}')" class="relative rounded-xl border p-3 text-left transition {{ $type === $key ? 'border-indigo-500 bg-indigo-50 text-indigo-700 ring-2 ring-indigo-500/15 dark:bg-indigo-500/10' : 'border-zinc-200 bg-white hover:border-indigo-300 dark:border-zinc-700 dark:bg-zinc-900' }}">
                                @if($type === $key)<span class="absolute right-2 top-2 flex size-5 items-center justify-center rounded-full bg-indigo-600 text-[10px] font-black text-white">✓</span>@endif
                                <span class="block text-xs font-bold">{{ $item['label'] }}</span>
                                <span class="mt-1 block text-[10px] leading-4 text-zinc-400">{{ $item['description'] }}</span>
                            </button>
                        @else
                            <div class="relative cursor-not-allowed rounded-xl border border-zinc-200 bg-zinc-50 p-3 opacity-50 dark:border-zinc-700 dark:bg-zinc-800/60" aria-disabled="true"><span class="absolute right-2 top-2 rounded-full bg-zinc-200 px-1.5 py-0.5 text-[8px] font-black uppercase tracking-wide text-zinc-500 dark:bg-zinc-700 dark:text-zinc-300">Em breve</span><span class="block text-xs font-bold">{{ $item['label'] }}</span></div>
                        @endif
                    @endforeach
                </div>
                <h3 class="mt-8 text-sm font-bold">Estilo visual</h3>
                <div class="mt-3 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($templates as $key=>$item)
                        @if(in_array($key, ['studio', 'commerce'], true))
                            <button type="button" wire:click="$set('template','{{ $key }}')" class="overflow-hidden rounded-2xl border text-left {{ $template === $key ? 'border-indigo-500 ring-2 ring-indigo-500/15' : 'border-zinc-200 dark:border-zinc-700' }}">
                                <div class="aspect-[16/9] p-4" style="background:linear-gradient(135deg, {{ $item['accent'] ?? '#635bff' }}22, #ffffff 65%)"><div class="h-full rounded-xl border border-white/70 bg-white/70 p-3 shadow-sm"><div class="flex gap-1"><i class="size-1.5 rounded-full bg-zinc-300"></i><i class="size-1.5 rounded-full bg-zinc-300"></i><i class="size-1.5 rounded-full bg-zinc-300"></i></div><div class="mt-4 h-2 w-2/3 rounded bg-zinc-800/70"></div><div class="mt-2 h-1.5 w-4/5 rounded bg-zinc-300"></div><div class="mt-4 h-5 w-16 rounded-md" style="background:{{ $item['accent'] ?? '#635bff' }}"></div></div></div>
                                <div class="p-4"><div class="flex items-center justify-between"><span class="font-bold">{{ $item['label'] }}</span>@if($template === $key)<span class="text-xs font-bold text-indigo-600">✓ Selecionado</span>@endif</div><p class="mt-1 text-xs leading-5 text-zinc-500">{{ $item['description'] }}</p></div>
                            </button>
                        @else
                            <div class="relative overflow-hidden rounded-2xl border border-zinc-200 bg-zinc-50 opacity-50 dark:border-zinc-700 dark:bg-zinc-800/60" aria-disabled="true"><span class="absolute right-3 top-3 z-10 rounded-full bg-zinc-200 px-2.5 py-1 text-[10px] font-black uppercase tracking-wide text-zinc-500 dark:bg-zinc-700 dark:text-zinc-300">Em breve</span><div class="aspect-[16/9] p-4"><div class="h-full rounded-xl border border-white/70 bg-white/70 p-3 shadow-sm"></div></div><div class="p-4"><div class="font-bold">{{ $item['label'] }}</div></div></div>
                        @endif
                    @endforeach
                </div>
                <div class="mt-8 flex justify-between"><flux:button icon="arrow-left" wire:click="previous">Voltar</flux:button><flux:button variant="primary" icon:trailing="arrow-right" wire:click="next">Continuar</flux:button></div>
            </section>
        @elseif($step === 3)
            <section class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-8">
                <p class="text-xs font-black uppercase tracking-widest text-indigo-600">3 · Informação</p><h2 class="mt-2 text-2xl font-black">{{ $type === 'online_store' ? 'Informação para criar uma loja online' : 'Conta-nos o essencial' }}</h2><p class="mt-2 text-sm text-zinc-500">{{ $type === 'online_store' ? 'Preenche os dados essenciais da loja. O Finder usa-os para criar a estrutura inicial e a comunicação da marca.' : 'A IA vai transformar estas poucas informações num website pessoal completo.' }}</p>
                <div class="mt-4 flex items-start gap-3 rounded-2xl border border-indigo-100 bg-indigo-50/70 px-4 py-3 dark:border-indigo-900/50 dark:bg-indigo-500/10"><flux:icon name="information-circle" class="mt-0.5 size-5 shrink-0 text-indigo-600 dark:text-indigo-400" /><p class="text-xs leading-5 text-indigo-800 dark:text-indigo-200"><span class="font-bold">Podes adicionar mais conteúdo depois.</span> Começa pelo essencial — os restantes dados podem ser completados na configuração da loja.</p></div>
                @if($type === 'online_store')
                    <div class="mt-7 grid gap-5 md:grid-cols-2">
                        <flux:input wire:model.live="name" label="Nome da loja" placeholder="Ex.: Minha Loja" />
                        <flux:input wire:model.live="headline" label="Mensagem principal" placeholder="Ex.: Produtos escolhidos para o teu dia a dia." />
                        <div class="md:col-span-2"><flux:textarea wire:model.live="about" label="Sobre a marca" placeholder="Conta-nos a história, posicionamento ou o que torna a tua loja diferente." rows="4" /></div>
                        <div class="md:col-span-2"><flux:textarea wire:model.live="featuredProducts" label="Produtos em destaque" placeholder="Indica produtos, categorias ou coleções que queres destacar." rows="3" /></div>
                        <flux:textarea wire:model.live="shipping" label="Envios e entregas" placeholder="Prazos, zonas e condições de entrega." rows="3" />
                        <flux:textarea wire:model.live="returns" label="Trocas e devoluções" placeholder="Indica a política ou deixa em branco para configurar depois." rows="3" />
                        <flux:input wire:model.live="contactEmail" type="email" label="Email de apoio" placeholder="apoio@loja.pt" />
                    </div>
                @else
                    <div class="mt-7 grid gap-5"><flux:input wire:model.live="name" label="O teu nome" placeholder="Ex.: Eduardo Patrício" /><flux:textarea wire:model.live="description" label="Fala-nos um pouco sobre ti" placeholder="Quem és, o que fazes e o que gostarias de apresentar no teu website." rows="4" /></div>
                @endif
                <div class="mt-8 flex justify-between"><flux:button icon="arrow-left" wire:click="previous">Voltar</flux:button><flux:button variant="primary" icon:trailing="arrow-right" wire:click="next">Escolher páginas</flux:button></div>
            </section>
        @else
            <section class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-8">
                <p class="text-xs font-black uppercase tracking-widest text-indigo-600">4 · Páginas</p>
                <h2 class="mt-2 text-2xl font-black">{{ $type === 'online_store' ? 'Estrutura da tua loja online' : 'Que páginas queres começar por ter?' }}</h2>
                <p class="mt-2 text-sm text-zinc-500">
                    {{ $type === 'online_store'
                        ? 'O Finder recomenda uma estrutura preparada para catálogo, marca e contacto. Podes adicionar ou remover páginas antes de criar a loja.'
                        : 'A estrutura inicial adapta-se ao tipo de website escolhido.' }}
                </p>

                <div class="mt-4 flex items-start gap-3 rounded-2xl border border-indigo-100 bg-indigo-50/60 px-4 py-3 dark:border-indigo-900/50 dark:bg-indigo-500/10">
                    <flux:icon name="information-circle" class="mt-0.5 size-5 shrink-0 text-indigo-600 dark:text-indigo-400" />
                    <p class="text-xs leading-5 text-indigo-800 dark:text-indigo-200">
                        <span class="font-bold">{{ $type === 'online_store' ? 'Estrutura recomendada para ecommerce.' : 'Podes adicionar mais páginas depois.' }}</span>
                        {{ $type === 'online_store'
                            ? 'Início apresenta a marca, Produtos mostra o catálogo, Sobre explica a marca e Contactos facilita o apoio ao cliente.'
                            : 'Começa apenas com o essencial — depois de criares o website, podes adicionar novos separadores quando quiseres.' }}
                    </p>
                </div>

                @if($type === 'online_store')
                    <div class="mt-7 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach(['home' => ['label' => 'Início', 'description' => 'Apresentação da loja e destaques.', 'icon' => 'home'], 'products' => ['label' => 'Produtos', 'description' => 'Catálogo real da loja.', 'icon' => 'archive-box'], 'about' => ['label' => 'Sobre a marca', 'description' => 'História e posicionamento.', 'icon' => 'information-circle'], 'contact' => ['label' => 'Contactos', 'description' => 'Apoio e contacto.', 'icon' => 'envelope']] as $key => $page)
                            <button type="button" wire:click="togglePage('{{ $key }}')" class="relative flex min-h-36 flex-col rounded-2xl border p-4 text-left transition {{ in_array($key, $pages, true) ? 'border-indigo-500 bg-indigo-50/60 ring-2 ring-indigo-500/10 dark:bg-indigo-500/10' : 'border-zinc-200 bg-white hover:border-indigo-300 dark:border-zinc-700 dark:bg-zinc-900' }}">
                                <span class="flex size-9 items-center justify-center rounded-xl {{ in_array($key, $pages, true) ? 'bg-indigo-600 text-white' : 'bg-zinc-100 text-zinc-400 dark:bg-zinc-800' }}">
                                    @if(in_array($key, $pages, true))
                                        ✓
                                    @else
                                        <flux:icon :name="$page['icon']" class="size-4" />
                                    @endif
                                </span>
                                <span class="mt-4 text-sm font-bold text-zinc-900 dark:text-white">{{ $page['label'] }}</span>
                                <span class="mt-1 text-xs leading-5 text-zinc-500">{{ $page['description'] }}</span>
                                <span class="mt-auto pt-3 text-[9px] font-black uppercase tracking-widest {{ in_array($key, $pages, true) ? 'text-indigo-600' : 'text-zinc-400' }}">{{ in_array($key, $pages, true) ? 'Selecionada' : 'Adicionar' }}</span>
                            </button>
                        @endforeach
                    </div>

                    <div class="mt-4 grid gap-3 sm:grid-cols-3">
                        @foreach(['faq' => ['label' => 'FAQ', 'description' => 'Dúvidas frequentes dos clientes.'], 'gallery' => ['label' => 'Galeria', 'description' => 'Imagens da marca ou produtos.'], 'blog' => ['label' => 'Blog', 'description' => 'Conteúdo e novidades da loja.']] as $key => $page)
                            <button type="button" wire:click="togglePage('{{ $key }}')" class="flex items-center gap-3 rounded-2xl border p-4 text-left transition {{ in_array($key, $pages, true) ? 'border-indigo-500 bg-indigo-50/60 dark:bg-indigo-500/10' : 'border-zinc-200 bg-white hover:border-indigo-300 dark:border-zinc-700 dark:bg-zinc-900' }}">
                                <span class="flex size-9 shrink-0 items-center justify-center rounded-xl {{ in_array($key, $pages, true) ? 'bg-indigo-600 text-white' : 'bg-zinc-100 text-zinc-400 dark:bg-zinc-800' }}">{{ in_array($key, $pages, true) ? '✓' : '+' }}</span>
                                <span><span class="block text-sm font-bold text-zinc-900 dark:text-white">{{ $page['label'] }}</span><span class="mt-1 block text-xs text-zinc-500">{{ $page['description'] }}</span></span>
                            </button>
                        @endforeach
                    </div>
                @else
                    <div class="mt-7 grid gap-3 sm:grid-cols-3">
                        @foreach(['home' => 'Início', 'about' => 'Sobre mim', 'contact' => 'Contactos'] as $key => $label)
                            <div class="flex items-center gap-3 rounded-2xl border border-indigo-500 bg-indigo-50/60 p-4 dark:bg-indigo-500/10"><span class="flex size-9 items-center justify-center rounded-xl bg-indigo-600 text-white">✓</span><span class="text-sm font-bold">{{ $label }}</span><span class="ml-auto text-[9px] font-black uppercase text-indigo-600">Essencial</span></div>
                        @endforeach
                    </div>
                @endif

                <div class="mt-8 rounded-2xl border border-indigo-100 bg-indigo-50/60 p-5 dark:border-indigo-900/50 dark:bg-indigo-500/10">
                    <p class="text-sm font-bold">{{ $type === 'online_store' ? 'A loja fica preparada para crescer' : 'O Finder trata do resto' }}</p>
                    <p class="mt-1 text-xs leading-5 text-zinc-500">
                        {{ $type === 'online_store'
                            ? 'O catálogo usa os produtos reais da loja. Páginas como FAQ, Galeria e Blog são opcionais e podem ser adicionadas agora ou mais tarde.'
                            : 'A estrutura, textos, títulos e chamadas para ação são adaptados ao tipo de website e à informação que forneceste. Não serão inventados dados que não tenhas fornecido.' }}
                    </p>
                </div>

                <div class="mt-8 flex justify-between"><flux:button icon="arrow-left" wire:click="previous">Voltar</flux:button><flux:button variant="primary" icon:trailing="rocket-launch" wire:click="create" wire:loading.attr="disabled"><span wire:loading.remove>{{ $type === 'online_store' ? 'Criar a minha loja' : 'Criar o meu website' }}</span><span wire:loading>{{ $type === 'online_store' ? 'A criar a tua loja…' : 'A criar o teu website…' }}</span></flux:button></div>


        <div x-cloak x-show="help" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-zinc-950/50 p-4" @click.self="help=false">
            <div class="w-full max-w-lg rounded-3xl bg-white p-7 shadow-2xl dark:bg-zinc-900">
                <div class="flex items-start justify-between gap-4"><div><p class="text-xs font-black uppercase tracking-widest text-indigo-600">Ajuda</p><h2 class="mt-2 text-2xl font-black">Como funciona?</h2></div><button type="button" @click="help=false" class="rounded-xl p-2 text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800">✕</button></div>
                <div class="mt-6 space-y-4 text-sm"><p><strong>1. Escolhe o modelo pessoal.</strong><br><span class="text-zinc-500">O Finder usa o Studio como base.</span></p><p><strong>2. Diz-nos o essencial.</strong><br><span class="text-zinc-500">Só precisas do teu nome e de uma breve descrição.</span></p><p><strong>3. A IA cria o website.</strong><br><span class="text-zinc-500">O resultado fica pronto para apresentar, sem Editor Visual.</span></p></div>
            </div>
        </div>
    </div>
</div>
