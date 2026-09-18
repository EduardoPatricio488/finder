<div class="min-h-screen bg-zinc-50 px-4 py-8 text-zinc-950 dark:bg-zinc-950 dark:text-white sm:px-6 lg:px-8">
    <div class="mx-auto max-w-6xl" x-data="{ help: false }">
        <div class="mb-8 flex items-start justify-between gap-4">
            <div>
                <a href="{{ route('dashboard') }}" wire:navigate class="text-xs font-bold uppercase tracking-widest text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200">← Finder</a>
                <h1 class="mt-3 text-3xl font-black tracking-tight sm:text-4xl">Vamos criar o teu website</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-zinc-500 dark:text-zinc-400">O Finder cria automaticamente um website pessoal simples, moderno e profissional a partir de poucas informações.</p>
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
                <p class="text-xs font-black uppercase tracking-widest text-indigo-600">2 · Modelo e tipo</p><h2 class="mt-2 text-2xl font-black">Modelo pessoal Studio</h2><p class="mt-2 text-sm text-zinc-500">Esta versão está focada exclusivamente em websites pessoais.</p>
                <h3 class="mt-7 text-sm font-bold">Tipo de website</h3>
                <div class="mt-3 grid gap-2 sm:grid-cols-3 lg:grid-cols-6">
                    @foreach($types as $key=>$item)
                        @if($key === 'personal')
                            <button type="button" wire:click="$set('type','personal')" class="rounded-xl border border-indigo-500 bg-indigo-50 p-3 text-left text-indigo-700 dark:bg-indigo-500/10"><span class="block text-xs font-bold">Pessoal</span><span class="mt-1 block text-[10px] leading-4 text-zinc-400">Um espaço pessoal ou página de apresentação.</span></button>
                        @else
                            <div class="relative cursor-not-allowed rounded-xl border border-zinc-200 bg-zinc-50 p-3 opacity-50 dark:border-zinc-700 dark:bg-zinc-800/60" aria-disabled="true"><span class="absolute right-2 top-2 rounded-full bg-zinc-200 px-1.5 py-0.5 text-[8px] font-black uppercase tracking-wide text-zinc-500 dark:bg-zinc-700 dark:text-zinc-300">Bloqueado</span><span class="block text-xs font-bold">{{ $item['label'] }}</span></div>
                        @endif
                    @endforeach
                </div>
                <h3 class="mt-8 text-sm font-bold">Estilo visual</h3>
                <div class="mt-3 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($templates as $key=>$item)
                        @if($key === 'studio')
                            <button type="button" wire:click="$set('template','studio')" class="overflow-hidden rounded-2xl border border-indigo-500 text-left ring-2 ring-indigo-500/15"><div class="aspect-[16/9] p-4" style="background:linear-gradient(135deg, {{ $item['accent'] ?? '#635bff' }}22, #ffffff 65%)"><div class="h-full rounded-xl border border-white/70 bg-white/70 p-3 shadow-sm"><div class="flex gap-1"><i class="size-1.5 rounded-full bg-zinc-300"></i><i class="size-1.5 rounded-full bg-zinc-300"></i><i class="size-1.5 rounded-full bg-zinc-300"></i></div><div class="mt-4 h-2 w-2/3 rounded bg-zinc-800/70"></div><div class="mt-2 h-1.5 w-4/5 rounded bg-zinc-300"></div><div class="mt-4 h-5 w-16 rounded-md" style="background:{{ $item['accent'] ?? '#635bff' }}"></div></div></div><div class="p-4"><div class="flex items-center justify-between"><span class="font-bold">Studio</span><span class="text-xs font-bold text-indigo-600">✓ Selecionado</span></div><p class="mt-1 text-xs leading-5 text-zinc-500">Minimalista, editorial e focado no conteúdo.</p></div></button>
                        @else
                            <div class="relative overflow-hidden rounded-2xl border border-zinc-200 bg-zinc-50 opacity-50 dark:border-zinc-700 dark:bg-zinc-800/60" aria-disabled="true"><span class="absolute right-3 top-3 z-10 rounded-full bg-zinc-200 px-2.5 py-1 text-[10px] font-black uppercase tracking-wide text-zinc-500 dark:bg-zinc-700 dark:text-zinc-300">Bloqueado</span><div class="aspect-[16/9] p-4"><div class="h-full rounded-xl border border-white/70 bg-white/70 p-3 shadow-sm"></div></div><div class="p-4"><div class="font-bold">{{ $item['label'] }}</div></div></div>
                        @endif
                    @endforeach
                </div>
                <div class="mt-8 flex justify-between"><flux:button icon="arrow-left" wire:click="previous">Voltar</flux:button><flux:button variant="primary" icon:trailing="arrow-right" wire:click="next">Continuar</flux:button></div>
            </section>
        @elseif($step === 3)
            <section class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-8">
                <p class="text-xs font-black uppercase tracking-widest text-indigo-600">3 · Informação</p><h2 class="mt-2 text-2xl font-black">Conta-nos o essencial</h2><p class="mt-2 text-sm text-zinc-500">A IA vai transformar estas poucas informações num website pessoal completo.</p>
                <div class="mt-4 flex items-start gap-3 rounded-2xl border border-indigo-100 bg-indigo-50/70 px-4 py-3 dark:border-indigo-900/50 dark:bg-indigo-500/10"><flux:icon name="information-circle" class="mt-0.5 size-5 shrink-0 text-indigo-600 dark:text-indigo-400" /><p class="text-xs leading-5 text-indigo-800 dark:text-indigo-200"><span class="font-bold">Podes adicionar mais conteúdo depois.</span> Não precisas de preencher tudo agora — depois de criares o website, podes completar e editar os teus dados na configuração do site.</p></div>
                <div class="mt-7 grid gap-5"><flux:input wire:model.live="name" label="O teu nome" placeholder="Ex.: Eduardo Patrício" /><flux:textarea wire:model.live="description" label="Fala-nos um pouco sobre ti" placeholder="Quem és, o que fazes e o que gostarias de apresentar no teu website." rows="4" /></div>
                <div class="mt-8 flex justify-between"><flux:button icon="arrow-left" wire:click="previous">Voltar</flux:button><flux:button variant="primary" icon:trailing="arrow-right" wire:click="next">Escolher páginas</flux:button></div>
            </section>
        @else
            <section class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-8">
                <p class="text-xs font-black uppercase tracking-widest text-indigo-600">4 · Páginas</p><h2 class="mt-2 text-2xl font-black">Que páginas queres começar por ter?</h2><p class="mt-2 text-sm text-zinc-500">A IA prepara apenas o essencial para um website pessoal.</p>
                <div class="mt-4 flex items-start gap-3 rounded-2xl border border-indigo-100 bg-indigo-50/60 px-4 py-3 dark:border-indigo-900/50 dark:bg-indigo-500/10">
                    <flux:icon name="information-circle" class="mt-0.5 size-5 shrink-0 text-indigo-600 dark:text-indigo-400" />
                    <p class="text-xs leading-5 text-indigo-800 dark:text-indigo-200">
                        <span class="font-bold">Podes adicionar mais páginas depois.</span> Começa apenas com o essencial — depois de criares o website, podes adicionar novos separadores quando quiseres.
                    </p>
                </div>
                <div class="mt-7 grid gap-3 sm:grid-cols-3">
                    @foreach(['home' => 'Início', 'about' => 'Sobre mim', 'contact' => 'Contactos'] as $key => $label)
                        <div class="flex items-center gap-3 rounded-2xl border border-indigo-500 bg-indigo-50/60 p-4 dark:bg-indigo-500/10"><span class="flex size-9 items-center justify-center rounded-xl bg-indigo-600 text-white">✓</span><span class="text-sm font-bold">{{ $label }}</span><span class="ml-auto text-[9px] font-black uppercase text-indigo-600">Essencial</span></div>
                    @endforeach
                </div>
                <div class="mt-8 rounded-2xl border border-indigo-100 bg-indigo-50/60 p-5 dark:border-indigo-900/50 dark:bg-indigo-500/10"><p class="text-sm font-bold">A IA trata do resto</p><p class="mt-1 text-xs leading-5 text-zinc-500">Vai adaptar a estrutura, textos, títulos e chamadas para ação ao teu perfil. Não serão inventados dados pessoais que não tenhas fornecido.</p></div>
                <div class="mt-8 flex justify-between"><flux:button icon="arrow-left" wire:click="previous">Voltar</flux:button><flux:button variant="primary" icon:trailing="rocket-launch" wire:click="create" wire:loading.attr="disabled"><span wire:loading.remove>Criar o meu website</span><span wire:loading>A criar o teu website…</span></flux:button></div>
            </section>
        @endif

        <div x-cloak x-show="help" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-zinc-950/50 p-4" @click.self="help=false">
            <div class="w-full max-w-lg rounded-3xl bg-white p-7 shadow-2xl dark:bg-zinc-900">
                <div class="flex items-start justify-between gap-4"><div><p class="text-xs font-black uppercase tracking-widest text-indigo-600">Ajuda</p><h2 class="mt-2 text-2xl font-black">Como funciona?</h2></div><button type="button" @click="help=false" class="rounded-xl p-2 text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800">✕</button></div>
                <div class="mt-6 space-y-4 text-sm"><p><strong>1. Escolhe o modelo pessoal.</strong><br><span class="text-zinc-500">O Finder usa o Studio como base.</span></p><p><strong>2. Diz-nos o essencial.</strong><br><span class="text-zinc-500">Só precisas do teu nome e de uma breve descrição.</span></p><p><strong>3. A IA cria o website.</strong><br><span class="text-zinc-500">O resultado fica pronto para apresentar, sem Editor Visual.</span></p></div>
            </div>
        </div>
    </div>
</div>
