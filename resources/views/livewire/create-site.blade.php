<div class="min-h-screen bg-zinc-50 px-4 py-10 dark:bg-zinc-950">
    <style>
        @keyframes site-step-in {
            from { opacity: 0; transform: translateY(6px) scale(.995); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        .site-step-in { animation: site-step-in .35s ease-out both; }
        @media (prefers-reduced-motion: reduce) {
            .site-step-in { animation: none; }
        }
    </style>

    <div class="mx-auto max-w-4xl">

        {{-- ============================= HEADER ============================= --}}
        <div class="mb-8">
            <div class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <a href="{{ route('dashboard') }}" wire:navigate class="inline-flex items-center gap-2 text-sm font-semibold text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                        FINDER
                    </a>
                    <h1 class="mt-4 text-3xl font-bold tracking-tight text-zinc-950 sm:text-4xl dark:text-white">
                        Criar website
                    </h1>
                    <p class="mt-2 max-w-md text-sm leading-6 text-zinc-500 dark:text-zinc-400">
                        Vamos preparar a estrutura inicial em poucos passos.
                    </p>
                </div>

                <div class="flex items-center gap-2 self-start rounded-full border border-zinc-200 bg-white px-3.5 py-1.5 text-xs font-semibold text-zinc-500 shadow-sm sm:self-auto dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-400">
                    <span class="size-1.5 rounded-full bg-indigo-500"></span>
                    Passo {{ $step }} de 4
                </div>
            </div>

            {{-- ===================== STEPPER ===================== --}}
            <div class="mt-8 grid grid-cols-4 gap-2 sm:gap-4">
                @foreach (['Tipo', 'Template', 'Identidade', 'Páginas'] as $index => $label)
                    @php $number = $index + 1; @endphp
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center gap-1.5">
                            <span
                                class="flex size-6 shrink-0 items-center justify-center rounded-full text-[11px] font-bold
                                    {{ $step === $number
                                        ? 'bg-indigo-600 text-white'
                                        : ($step > $number
                                            ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-400'
                                            : 'bg-zinc-200 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-500') }}"
                            >
                                @if ($step > $number)
                                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                @else
                                    {{ str_pad($number, 2, '0', STR_PAD_LEFT) }}
                                @endif
                            </span>
                            <span class="hidden text-xs font-semibold sm:inline {{ $step >= $number ? 'text-zinc-900 dark:text-white' : 'text-zinc-400 dark:text-zinc-600' }}">
                                {{ $label }}
                            </span>
                        </div>
                        <div class="h-1 rounded-full {{ $step >= $number ? 'bg-indigo-600' : 'bg-zinc-200 dark:bg-zinc-800' }}" aria-hidden="true"></div>
                    </div>
                @endforeach
            </div>

            {{-- ===================== PROGRESS BAR ===================== --}}
            <div class="relative mt-4 h-1.5 overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-800" role="progressbar" aria-valuenow="{{ $step * 25 }}" aria-valuemin="0" aria-valuemax="100">
                <div
                    class="relative h-full overflow-hidden rounded-full bg-gradient-to-r from-indigo-500 to-fuchsia-500 transition-all duration-500 ease-out"
                    style="width: {{ $step * 25 }}%"
                >
                    <span class="absolute inset-0 bg-white/20 motion-safe:animate-pulse" aria-hidden="true"></span>
                </div>
            </div>
        </div>

        {{-- ============================= PASSO 1 — TIPO ============================= --}}
        @if ($step === 1)
            <section wire:key="step-1" class="site-step-in rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm sm:p-8 dark:border-zinc-800 dark:bg-zinc-900">
                <h2 class="text-xl font-semibold text-zinc-950 dark:text-white">O que queres criar?</h2>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Escolhe a estrutura que melhor corresponde ao teu objetivo.</p>

                <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    @php $typeIcons = ['sparkles', 'squares-2x2', 'swatch', 'rectangle-stack', 'globe-alt', 'cube', 'bolt', 'star']; @endphp
                    @foreach ($types as $key => $item)
                        @php $isSelected = $type === $key; @endphp
                        <button
                            type="button"
                            wire:click="$set('type','{{ $key }}')"
                            class="group relative flex flex-col items-start gap-3 rounded-2xl border p-4 text-left transition-all duration-150 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-zinc-900
                                {{ $isSelected
                                    ? 'border-indigo-500 bg-indigo-50/70 shadow-sm shadow-indigo-500/10 dark:border-indigo-500 dark:bg-indigo-500/10'
                                    : 'border-zinc-200 hover:-translate-y-0.5 hover:border-zinc-300 hover:shadow-sm dark:border-zinc-700 dark:hover:border-zinc-600' }}"
                        >
                            @if ($isSelected)
                                <span class="absolute right-3 top-3 flex size-5 items-center justify-center rounded-full bg-indigo-600 text-white">
                                    <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </span>
                            @endif

                            <span class="flex size-10 items-center justify-center rounded-xl {{ $isSelected ? 'bg-indigo-600 text-white' : 'bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400' }}">
                                <flux:icon :name="$typeIcons[$loop->index % count($typeIcons)]" class="size-5" />
                            </span>

                            <span>
                                <span class="block font-semibold text-zinc-950 dark:text-white">{{ $item['label'] }}</span>
                                <span class="mt-1 block text-xs leading-5 text-zinc-500 dark:text-zinc-400">{{ $item['description'] }}</span>
                            </span>
                        </button>
                    @endforeach
                </div>

                <div class="mt-8 flex justify-end">
                    <flux:button variant="primary" icon:trailing="arrow-right" wire:click="next">Continuar</flux:button>
                </div>
            </section>

        {{-- ============================= PASSO 2 — TEMPLATE ============================= --}}
        @elseif ($step === 2)
            <section wire:key="step-2" class="site-step-in rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm sm:p-8 dark:border-zinc-800 dark:bg-zinc-900">
                <h2 class="text-xl font-semibold text-zinc-950 dark:text-white">Escolhe um template</h2>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">A estrutura visual inicial do teu website. Podes ajustar tudo depois.</p>

                <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($templates as $key => $item)
                        @php $isSelected = $template === $key; @endphp
                        <button
                            type="button"
                            wire:click="$set('template','{{ $key }}')"
                            class="group relative overflow-hidden rounded-2xl border text-left transition-all duration-150 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-zinc-900
                                {{ $isSelected
                                    ? 'border-indigo-500 ring-2 ring-indigo-500/25'
                                    : 'border-zinc-200 hover:-translate-y-0.5 hover:border-zinc-300 hover:shadow-sm dark:border-zinc-700 dark:hover:border-zinc-600' }}"
                        >
                            @if ($isSelected)
                                <span class="absolute right-2.5 top-2.5 z-10 flex size-6 items-center justify-center rounded-full bg-indigo-600 text-white shadow-sm">
                                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </span>
                            @endif

                            {{-- preview HTML/CSS de website, sem imagens externas --}}
                            <div class="relative aspect-[4/3] overflow-hidden bg-gradient-to-br from-indigo-50 via-violet-50 to-fuchsia-50 p-3 dark:from-indigo-950/60 dark:via-violet-950/50 dark:to-fuchsia-950/40">
                                <div class="flex items-center gap-1 rounded-md bg-white/80 px-2 py-1 shadow-sm dark:bg-zinc-900/70">
                                    <span class="size-1.5 rounded-full bg-zinc-300 dark:bg-zinc-600"></span>
                                    <span class="size-1.5 rounded-full bg-zinc-300 dark:bg-zinc-600"></span>
                                    <span class="ml-auto h-1.5 w-10 rounded-full bg-zinc-200 dark:bg-zinc-700"></span>
                                </div>

                                @if ($loop->index % 2 === 0)
                                    <div class="mt-2.5 space-y-1.5">
                                        <div class="h-2.5 w-3/5 rounded bg-zinc-800/70 dark:bg-white/70"></div>
                                        <div class="h-1.5 w-4/5 rounded bg-zinc-400/50 dark:bg-zinc-400/40"></div>
                                        <div class="mt-2 h-4 w-14 rounded-md bg-indigo-500/80"></div>
                                    </div>
                                    <div class="mt-3 grid grid-cols-3 gap-1">
                                        <div class="h-6 rounded bg-white/70 shadow-sm dark:bg-white/10"></div>
                                        <div class="h-6 rounded bg-white/70 shadow-sm dark:bg-white/10"></div>
                                        <div class="h-6 rounded bg-white/70 shadow-sm dark:bg-white/10"></div>
                                    </div>
                                @else
                                    <div class="mt-2.5 flex gap-2">
                                        <div class="h-14 w-1/2 rounded-md bg-white/70 shadow-sm dark:bg-white/10"></div>
                                        <div class="flex-1 space-y-1.5 pt-1">
                                            <div class="h-2 w-full rounded bg-zinc-800/60 dark:bg-white/60"></div>
                                            <div class="h-1.5 w-4/5 rounded bg-zinc-400/50 dark:bg-zinc-400/40"></div>
                                            <div class="h-1.5 w-3/5 rounded bg-zinc-400/50 dark:bg-zinc-400/40"></div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="p-4">
                                <span class="block font-semibold text-zinc-950 dark:text-white">{{ $item['label'] }}</span>
                                <span class="mt-1 block text-xs leading-5 text-zinc-500 dark:text-zinc-400">{{ $item['description'] }}</span>
                            </div>
                        </button>
                    @endforeach
                </div>

                <div class="mt-8 flex justify-between">
                    <flux:button icon="arrow-left" wire:click="previous">Voltar</flux:button>
                    <flux:button variant="primary" icon:trailing="arrow-right" wire:click="next">Continuar</flux:button>
                </div>
            </section>

        {{-- ============================= PASSO 3 — IDENTIDADE ============================= --}}
        @elseif ($step === 3)
            <section wire:key="step-3" class="site-step-in rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm sm:p-8 dark:border-zinc-800 dark:bg-zinc-900">
                <h2 class="text-xl font-semibold text-zinc-950 dark:text-white">Personaliza a identidade</h2>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Estes valores definem o nome, o endereço e o estilo visual do teu website.</p>

                <div class="mt-6 grid gap-8 lg:grid-cols-[1.1fr_0.9fr]">
                    {{-- CAMPOS --}}
                    <div class="space-y-5">
                        <flux:input wire:model.live="name" label="Nome do website" placeholder="A minha empresa" />
                        <flux:input wire:model.live="slug" label="Slug" placeholder="a-minha-empresa" description="O endereço do teu website." />

                        <div class="grid gap-5 sm:grid-cols-2">
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                Cor principal
                                <div class="mt-2 flex items-center gap-2 rounded-xl border border-zinc-200 p-1.5 dark:border-zinc-700">
                                    <input type="color" wire:model.live="primaryColor" class="size-8 shrink-0 cursor-pointer rounded-lg border-0 bg-transparent p-0">
                                    <span class="truncate text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ $primaryColor }}</span>
                                </div>
                            </label>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                Cor secundária
                                <div class="mt-2 flex items-center gap-2 rounded-xl border border-zinc-200 p-1.5 dark:border-zinc-700">
                                    <input type="color" wire:model.live="secondaryColor" class="size-8 shrink-0 cursor-pointer rounded-lg border-0 bg-transparent p-0">
                                    <span class="truncate text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ $secondaryColor }}</span>
                                </div>
                            </label>
                        </div>

                        <flux:select wire:model="font" label="Tipografia">
                            <option value="Inter">Inter</option>
                            <option value="Manrope">Manrope</option>
                            <option value="DM Sans">DM Sans</option>
                            <option value="Plus Jakarta Sans">Plus Jakarta Sans</option>
                        </flux:select>
                    </div>

                    {{-- PRÉ-VISUALIZAÇÃO — reflete tipo, template, cores, tipografia e nome já escolhidos --}}
                    <div class="lg:sticky lg:top-6 lg:self-start">
                        <p class="text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Pré-visualização do website</p>

                        @if (!empty($types[$type]['label']) || !empty($templates[$template]['label']))
                            <div class="mt-2 flex flex-wrap gap-1.5">
                                @if (!empty($types[$type]['label']))
                                    <span class="inline-flex items-center rounded-full bg-zinc-100 px-2.5 py-1 text-[11px] font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                        {{ $types[$type]['label'] }}
                                    </span>
                                @endif
                                @if (!empty($templates[$template]['label']))
                                    <span class="inline-flex items-center rounded-full bg-zinc-100 px-2.5 py-1 text-[11px] font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                        {{ $templates[$template]['label'] }}
                                    </span>
                                @endif
                            </div>
                        @endif

                        <div class="mt-3 overflow-hidden rounded-2xl border border-zinc-200 shadow-sm dark:border-zinc-700" style="font-family: '{{ $font }}', sans-serif;">
                            {{-- barra do browser --}}
                            <div class="flex items-center gap-1.5 border-b border-zinc-200 bg-zinc-50 px-3 py-2 dark:border-zinc-800 dark:bg-zinc-900">
                                <span class="size-1.5 rounded-full bg-zinc-300 dark:bg-zinc-700"></span>
                                <span class="size-1.5 rounded-full bg-zinc-300 dark:bg-zinc-700"></span>
                                <span class="size-1.5 rounded-full bg-zinc-300 dark:bg-zinc-700"></span>
                                <span class="ml-2 truncate rounded bg-white px-2 py-0.5 text-[10px] text-zinc-400 dark:bg-zinc-800 dark:text-zinc-500">
                                    {{ $slug ? $slug . '.finder.site' : 'oteusite.finder.site' }}
                                </span>
                            </div>

                            <div class="bg-white dark:bg-zinc-950">
                                {{-- navbar --}}
                                <div class="flex items-center justify-between px-4 py-3">
                                    <span class="flex items-center gap-1.5 text-xs font-bold text-zinc-900 dark:text-white">
                                        <span class="flex size-5 items-center justify-center rounded-md text-[10px] font-bold text-white" style="background-color: {{ $primaryColor }}">
                                            {{ strtoupper(substr($name ?: 'W', 0, 1)) }}
                                        </span>
                                        <span class="max-w-[120px] truncate">{{ $name ?: 'O teu website' }}</span>
                                    </span>
                                    <div class="hidden items-center gap-3 text-[10px] font-medium text-zinc-400 sm:flex">
                                        <span>Início</span><span>Sobre</span><span>Contacto</span>
                                    </div>
                                </div>

                                {{-- hero --}}
                                <div class="px-4 pb-4 pt-1">
                                    <p class="text-[10px] font-semibold uppercase tracking-wide" style="color: {{ $secondaryColor }}">
                                        {{ $types[$type]['label'] ?? 'O teu negócio' }}
                                    </p>
                                    <p class="mt-1 max-w-[230px] text-base font-bold leading-snug text-zinc-900 dark:text-white">
                                        Uma presença online profissional para {{ $name ?: 'o teu projeto' }}.
                                    </p>
                                    <div class="mt-3 flex gap-2">
                                        <span class="rounded-lg px-3 py-1.5 text-[10px] font-semibold text-white" style="background-color: {{ $primaryColor }}">
                                            Começar agora
                                        </span>
                                        <span class="rounded-lg border px-3 py-1.5 text-[10px] font-semibold" style="border-color: {{ $secondaryColor }}; color: {{ $secondaryColor }}">
                                            Saber mais
                                        </span>
                                    </div>
                                </div>

                                {{-- conteúdo: layout varia consoante o template escolhido, tal como no passo 2 --}}
                                @php
                                    $templateKeys = array_keys($templates);
                                    $templateIndex = array_search($template, $templateKeys, true);
                                    $isSplitLayout = $templateIndex !== false && $templateIndex % 2 === 1;
                                @endphp

                                <div class="border-t border-zinc-100 bg-zinc-50/70 px-4 py-4 dark:border-zinc-900 dark:bg-zinc-900/40">
                                    @if ($isSplitLayout)
                                        <div class="flex gap-2">
                                            <div class="h-14 w-1/2 rounded-md bg-white shadow-sm dark:bg-zinc-800"></div>
                                            <div class="flex-1 space-y-1.5 pt-1">
                                                <div class="h-2 w-full rounded bg-zinc-300 dark:bg-zinc-700"></div>
                                                <div class="h-1.5 w-4/5 rounded bg-zinc-200 dark:bg-zinc-800"></div>
                                                <div class="h-1.5 w-3/5 rounded bg-zinc-200 dark:bg-zinc-800"></div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="grid grid-cols-3 gap-1.5">
                                            <div class="h-10 rounded-md bg-white shadow-sm dark:bg-zinc-800"></div>
                                            <div class="h-10 rounded-md bg-white shadow-sm dark:bg-zinc-800"></div>
                                            <div class="h-10 rounded-md bg-white shadow-sm dark:bg-zinc-800"></div>
                                        </div>
                                    @endif
                                </div>

                                {{-- rodapé --}}
                                <div class="flex items-center justify-between px-4 py-2.5 text-[9px] text-zinc-400 dark:text-zinc-600">
                                    <span>© {{ $name ?: 'O teu website' }}</span>
                                    <span>{{ $font }}</span>
                                </div>
                            </div>
                        </div>

                        <p class="mt-3 text-[11px] leading-5 text-zinc-400 dark:text-zinc-500">
                            Representação aproximada com base no tipo, template, cores e tipografia que escolheste.
                        </p>
                    </div>
                </div>

                <div class="mt-8 flex justify-between">
                    <flux:button icon="arrow-left" wire:click="previous">Voltar</flux:button>
                    <flux:button variant="primary" icon:trailing="arrow-right" wire:click="next">Continuar</flux:button>
                </div>
            </section>

        {{-- ============================= PASSO 4 — PÁGINAS ============================= --}}
        @else
            @php
                $pageOptions = [
                    'home' => ['label' => 'Home', 'icon' => 'home'],
                    'about' => ['label' => 'Sobre', 'icon' => 'user'],
                    'services' => ['label' => 'Serviços', 'icon' => 'briefcase'],
                    'products' => ['label' => 'Produtos', 'icon' => 'shopping-bag'],
                    'blog' => ['label' => 'Blog', 'icon' => 'newspaper'],
                    'contact' => ['label' => 'Contacto', 'icon' => 'envelope'],
                    'faq' => ['label' => 'FAQ', 'icon' => 'question-mark-circle'],
                    'privacy' => ['label' => 'Privacidade', 'icon' => 'shield-check'],
                ];
            @endphp
            <section wire:key="step-4" class="site-step-in rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm sm:p-8 dark:border-zinc-800 dark:bg-zinc-900">
                <h2 class="text-xl font-semibold text-zinc-950 dark:text-white">Escolhe as páginas iniciais</h2>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Podes editar, duplicar ou remover páginas depois.</p>

                <div class="mt-6 grid gap-3 sm:grid-cols-2">
                    @foreach ($pageOptions as $key => $option)
                        @php $isSelected = in_array($key, $pages, true); @endphp
                        <button
                            type="button"
                            wire:click="togglePage('{{ $key }}')"
                            aria-pressed="{{ $isSelected ? 'true' : 'false' }}"
                            class="flex items-center gap-3 rounded-xl border p-4 text-left transition-all duration-150 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-zinc-900
                                {{ $isSelected
                                    ? 'border-indigo-500 bg-indigo-50/70 dark:border-indigo-500 dark:bg-indigo-500/10'
                                    : 'border-zinc-200 hover:border-zinc-300 dark:border-zinc-700 dark:hover:border-zinc-600' }}"
                        >
                            <span class="flex size-9 shrink-0 items-center justify-center rounded-lg {{ $isSelected ? 'bg-indigo-600 text-white' : 'bg-zinc-100 text-zinc-400 dark:bg-zinc-800 dark:text-zinc-500' }}">
                                <flux:icon :name="$option['icon']" class="size-4.5" />
                            </span>

                            <span class="flex-1 text-sm font-medium text-zinc-900 dark:text-white">{{ $option['label'] }}</span>

                            <span class="flex size-5 shrink-0 items-center justify-center rounded-full border {{ $isSelected ? 'border-indigo-600 bg-indigo-600 text-white' : 'border-zinc-300 dark:border-zinc-600' }}">
                                @if ($isSelected)
                                    <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                @endif
                            </span>
                        </button>
                    @endforeach
                </div>

                {{-- ===================== RESUMO ===================== --}}
                <div class="mt-8 rounded-2xl border border-zinc-200 bg-zinc-50 p-5 dark:border-zinc-800 dark:bg-zinc-950/50">
                    <p class="text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Resumo</p>
                    <dl class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <dt class="text-xs text-zinc-500 dark:text-zinc-400">Tipo</dt>
                            <dd class="mt-0.5 text-sm font-semibold text-zinc-900 dark:text-white">{{ $types[$type]['label'] ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-zinc-500 dark:text-zinc-400">Template</dt>
                            <dd class="mt-0.5 text-sm font-semibold text-zinc-900 dark:text-white">{{ $templates[$template]['label'] ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-zinc-500 dark:text-zinc-400">Nome</dt>
                            <dd class="mt-0.5 truncate text-sm font-semibold text-zinc-900 dark:text-white">{{ $name ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-zinc-500 dark:text-zinc-400">Slug</dt>
                            <dd class="mt-0.5 truncate text-sm font-semibold text-zinc-900 dark:text-white">{{ $slug ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-zinc-500 dark:text-zinc-400">Tipografia</dt>
                            <dd class="mt-0.5 text-sm font-semibold text-zinc-900 dark:text-white">{{ $font }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-zinc-500 dark:text-zinc-400">Páginas selecionadas</dt>
                            <dd class="mt-0.5 text-sm font-semibold text-zinc-900 dark:text-white">{{ count($pages) }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="mt-8 flex justify-between">
                    <flux:button icon="arrow-left" wire:click="previous">Voltar</flux:button>
                    <flux:button variant="primary" icon:trailing="check" wire:click="create">Criar website</flux:button>
                </div>
            </section>
        @endif
    </div>
</div>
