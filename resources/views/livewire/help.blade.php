<div class="space-y-6 p-6 lg:p-8">
    <div>
        <p class="text-sm font-medium text-indigo-600">Finder</p>
        <h1 class="mt-1 text-3xl font-black tracking-tight text-zinc-950 dark:text-white">Ajuda</h1>
        <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">Encontra aqui as principais orientações para gerir os teus websites.</p>
    </div>

    <div class="grid gap-4 md:grid-cols-3">
        @if($hasDashboard)
            <a href="{{ route('dashboard') }}" wire:navigate class="rounded-2xl border border-zinc-200 bg-white p-5 transition hover:border-indigo-300 hover:shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <h2 class="font-semibold text-zinc-950 dark:text-white">Meus Websites</h2>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">Gere os teus websites, acede ao painel e abre o site público.</p>
            </a>
        @endif

        @if($hasSiteCreate)
            <a href="{{ route('site.create') }}" wire:navigate class="rounded-2xl border border-zinc-200 bg-white p-5 transition hover:border-indigo-300 hover:shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <h2 class="font-semibold text-zinc-950 dark:text-white">Criar website</h2>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">Cria um novo website e configura a sua estrutura inicial.</p>
            </a>
        @endif

        @if($hasBuilder)
            <a href="{{ route('builder.edit') }}" wire:navigate class="rounded-2xl border border-zinc-200 bg-white p-5 transition hover:border-indigo-300 hover:shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <h2 class="font-semibold text-zinc-950 dark:text-white">Website Builder</h2>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">Edita as páginas e personaliza o conteúdo do teu website.</p>
            </a>
        @endif
    </div>

    <div class="rounded-2xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
        <h2 class="text-lg font-bold text-zinc-950 dark:text-white">Como funciona o Finder?</h2>
        <div class="mt-5 space-y-4 text-sm text-zinc-600 dark:text-zinc-300">
            <p><strong>1. Seleciona o website:</strong> usa o seletor na sidebar para escolher o website que queres gerir.</p>
            <p><strong>2. Configura o website:</strong> ajusta as definições, media, menus e restantes opções disponíveis.</p>
            <p><strong>3. Gere a loja:</strong> quando aplicável, podes gerir produtos, vendas e encomendas a partir da área Loja.</p>
            <p><strong>4. Publica e verifica:</strong> usa a opção de ver o site para confirmar como as alterações aparecem publicamente.</p>
        </div>
    </div>
</div>