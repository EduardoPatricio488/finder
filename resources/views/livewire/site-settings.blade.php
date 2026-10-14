<div class="mx-auto max-w-5xl space-y-6 p-6 lg:p-8">
    <div class="mb-2"><x-site-workspace-selector :current-site="$site" route-name="settings" /></div>

    <section class="overflow-hidden rounded-3xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="bg-gradient-to-br from-indigo-950 via-indigo-900 to-violet-800 px-6 py-8 text-white sm:px-8">
            <div class="flex items-start gap-4">
                <div class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-white/10">
                    <flux:icon name="adjustments-horizontal" class="size-6" />
                </div>
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-indigo-200">Painel de controlo</p>
                    <h1 class="mt-2 text-3xl font-black tracking-tight">Definições gerais</h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-indigo-100">Configura o comportamento e as preferências do website. A edição de textos, imagens e secções continua disponível no editor de páginas.</p>
                </div>
            </div>
        </div>

        <form wire:submit="savePreferences" class="space-y-7 p-6 sm:p-8">
            @if(session('preferences-saved'))
                <div role="status" class="flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-300">
                    <flux:icon name="check-circle" class="size-5" /> {{ session('preferences-saved') }}
                </div>
            @endif

            <div>
                <div class="mb-4">
                    <h2 class="text-lg font-bold text-zinc-950 dark:text-white">Idioma e descoberta</h2>
                    <p class="mt-1 text-sm text-zinc-500">Define as preferências gerais para este website.</p>
                </div>
                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label for="site-language" class="mb-2 block text-sm font-semibold text-zinc-800 dark:text-zinc-200">Idioma principal</label>
                        <select id="site-language" wire:model="siteLanguage" class="w-full rounded-xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                            <option value="pt-PT">Português (Portugal)</option>
                            <option value="en-GB">Inglês</option>
                            <option value="es-ES">Espanhol</option>
                            <option value="fr-FR">Francês</option>
                        </select>
                        @error('siteLanguage') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="site-description" class="mb-2 block text-sm font-semibold text-zinc-800 dark:text-zinc-200">Descrição para motores de pesquisa</label>
                        <textarea id="site-description" wire:model="siteDescription" rows="3" maxlength="320" placeholder="Resume o propósito deste website..." class="w-full rounded-xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"></textarea>
                        <div class="mt-1 flex justify-between gap-3 text-xs text-zinc-400"><span>Recomendado: uma descrição clara e curta.</span><span>{{ mb_strlen($siteDescription) }}/320</span></div>
                        @error('siteDescription') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
                <label class="mt-5 flex cursor-pointer items-start gap-3 rounded-2xl border border-zinc-200 p-4 transition hover:border-indigo-300 dark:border-zinc-700">
                    <input type="checkbox" wire:model="allowSearchIndexing" class="mt-1 size-4 rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500">
                    <span><span class="block text-sm font-bold text-zinc-900 dark:text-white">Permitir indexação por motores de pesquisa</span><span class="mt-1 block text-sm leading-5 text-zinc-500">Guarda esta preferência para indicar se pretendes que o website possa ser encontrado através de motores de pesquisa.</span></span>
                </label>
            </div>

            <div class="border-t border-zinc-100 pt-6 dark:border-zinc-800">
                <div class="mb-4">
                    <h2 class="text-lg font-bold text-zinc-950 dark:text-white">Integrações</h2>
                    <p class="mt-1 text-sm text-zinc-500">Guarda o identificador de uma ferramenta de análise, caso utilizes uma.</p>
                </div>
                <label for="analytics-id" class="mb-2 block text-sm font-semibold text-zinc-800 dark:text-zinc-200">ID de análise (opcional)</label>
                <input id="analytics-id" type="text" wire:model="analyticsId" placeholder="Ex.: G-XXXXXXXXXX" autocomplete="off" class="w-full rounded-xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                <p class="mt-1 text-xs text-zinc-400">O identificador fica guardado nas definições deste website; a integração com o serviço de análise tem de ser configurada separadamente.</p>
                @error('analyticsId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="border-t border-zinc-100 pt-6 dark:border-zinc-800">
                <h2 class="text-lg font-bold text-zinc-950 dark:text-white">Identidade do construtor</h2>
                <label class="mt-4 flex cursor-pointer items-start gap-3 rounded-2xl border border-zinc-200 p-4 transition hover:border-indigo-300 dark:border-zinc-700">
                    <input type="checkbox" wire:model="showFinderBranding" class="mt-1 size-4 rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500">
                    <span><span class="block text-sm font-bold text-zinc-900 dark:text-white">Mostrar identificação do Finder</span><span class="mt-1 block text-sm leading-5 text-zinc-500">Guarda a tua preferência sobre a identificação do construtor no website.</span></span>
                </label>
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-zinc-100 pt-6 sm:flex-row sm:items-center sm:justify-between dark:border-zinc-800">
                <a href="{{ route('site.public', [$site, 'pageSlug' => null, 'preview' => 1]) }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center gap-2 rounded-xl border border-zinc-200 px-4 py-3 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">Pré-visualizar website <span aria-hidden="true">↗</span></a>
                <button type="submit" wire:loading.attr="disabled" wire:target="savePreferences" class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-indigo-700 disabled:opacity-60">
                    <span wire:loading.remove wire:target="savePreferences"><flux:icon name="check" class="size-4" /></span>
                    <span wire:loading wire:target="savePreferences"><flux:icon name="arrow-path" class="size-4 animate-spin" /></span>
                    Guardar definições
                </button>
            </div>
        </form>
    </section>

    <div class="flex items-start gap-3 rounded-2xl border border-sky-200 bg-sky-50 p-4 text-sm text-sky-900 dark:border-sky-900 dark:bg-sky-950/30 dark:text-sky-200">
        <flux:icon name="information-circle" class="mt-0.5 size-5 shrink-0" />
        <p><strong>Nota:</strong> estas definições são diferentes dos campos de conteúdo do modelo. Para alterar textos, imagens, widgets ou secções, abre o editor da página correspondente.</p>
    </div>
</div>
