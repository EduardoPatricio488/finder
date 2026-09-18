<div class="mx-auto max-w-5xl space-y-6 p-6 lg:p-8">
    <div class="mb-2"><x-site-workspace-selector :current-site="$site" route-name="settings" /></div>

    <div class="flex flex-col justify-between gap-5 md:flex-row md:items-end">
        <div>
            <div class="text-[10px] font-black uppercase tracking-[0.25em] text-indigo-600 dark:text-indigo-400">
                <span>Dados do site</span>
            </div>
            <h1 class="mt-2 text-3xl font-black tracking-tight text-stone-950 dark:text-white">
                Configuração do site
            </h1>
            <p class="mt-1 max-w-3xl text-sm leading-6 text-stone-500">
                {{ $modelProfile['description'] ?? 'Preenche os conteúdos específicos deste website.' }}
            </p>
        </div>

        <div class="flex flex-wrap items-center justify-end gap-3 md:min-w-[420px]">
            <div class="rounded-2xl border-2 border-indigo-200 bg-indigo-50 px-5 py-3 text-right shadow-sm dark:border-indigo-800 dark:bg-indigo-950/40">
                <div class="text-[9px] font-black uppercase tracking-[0.2em] text-indigo-500 dark:text-indigo-400">Modelo escolhido</div>
                <div class="mt-1 text-base font-black text-indigo-950 dark:text-white">{{ $modelProfile['label'] ?? 'Pessoal' }}</div>
            </div>
            <div class="rounded-xl bg-zinc-100 px-3 py-2 text-[10px] font-black uppercase tracking-[0.12em] text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">{{ $modelFilled }}/{{ $modelTotal }} preenchidos</div>
            @if(session('model-content-saved'))
                <span class="rounded-xl bg-emerald-50 px-4 py-2 text-xs font-bold text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300">
                    {{ session('model-content-saved') }}
                </span>
            @endif
            @if(session('model-content-applied'))
                <span class="rounded-xl bg-emerald-50 px-4 py-2 text-xs font-bold text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300">
                    {{ session('model-content-applied') }}
                </span>
            @endif
        </div>
    </div>

    <section class="rounded-[2rem] border border-indigo-200 bg-white p-7 shadow-sm dark:border-indigo-900/60 dark:bg-zinc-900">
        <div>
            <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.25em] text-indigo-600 dark:text-indigo-400">
                <span>Conteúdo do modelo</span>
                <span class="rounded-full bg-indigo-100 px-2 py-1 text-[9px] dark:bg-indigo-950/60">{{ $modelFilled }}/{{ $modelTotal }} preenchidos</span>
            </div>
            <h2 class="mt-2 text-2xl font-black tracking-tight text-stone-950 dark:text-white">
                Conteúdo do website
            </h2>
            <p class="mt-1 max-w-3xl text-sm leading-6 text-stone-500">
                Estes campos são específicos deste tipo de website e ficam guardados apenas neste site.
            </p>
        </div>

        @if($modelTotal > 0)
            <form wire:submit="saveModelContent" class="mt-7">
                <div class="grid gap-5 md:grid-cols-2">
                    @foreach($modelFields as $field)
                        @php
                            $fieldKey = $field['key'];
                            $fieldType = $field['type'] ?? 'text';
                            $fieldId = 'model-'.$fieldKey;
                        @endphp

                        <div id="{{ $fieldId }}" class="{{ in_array($fieldType, ['textarea'], true) ? 'md:col-span-2' : '' }} scroll-mt-8">
                            <label for="{{ $fieldId }}-input" class="mb-2 block text-sm font-bold text-stone-900 dark:text-white">
                                {{ $field['label'] }}
                                @if($field['required'] ?? false)
                                    <span class="text-red-500">*</span>
                                @endif
                            </label>

                            @if($fieldType === 'textarea')
                                <textarea id="{{ $fieldId }}-input" wire:model.live="modelContent.{{ $fieldKey }}" rows="5" placeholder="{{ $field['placeholder'] ?? '' }}" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:focus:border-indigo-500 dark:focus:ring-indigo-950"></textarea>
                            @else
                                <input id="{{ $fieldId }}-input" type="{{ $fieldType === 'email' ? 'email' : ($fieldType === 'url' ? 'url' : 'text') }}" wire:model="modelContent.{{ $fieldKey }}" placeholder="{{ $field['placeholder'] ?? '' }}" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:focus:border-indigo-500 dark:focus:ring-indigo-950" />
                            @endif

                            @error("modelContent.{$fieldKey}")
                                <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endforeach
                </div>

                <div class="mt-6 flex flex-wrap items-center justify-between gap-3 border-t border-stone-100 pt-5 dark:border-zinc-800">
                    <div class="flex flex-col gap-1">
                        <p class="text-xs text-stone-400">Os dados ficam isolados deste website.</p>
                        @if($modelContentApplied)
                            <p class="text-xs font-semibold text-emerald-600 dark:text-emerald-400">✓ Dados aplicados no site</p>
                        @else
                            <p class="text-xs font-semibold text-amber-600 dark:text-amber-400">Ainda não aplicado ao site</p>
                        @endif
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <flux:button type="submit" variant="primary" icon="check" class="!rounded-xl !bg-indigo-600">Guardar configuração</flux:button>
                        @if($modelContentSaved)
                            <flux:button type="button" wire:click="applyModelContent" wire:loading.attr="disabled" icon="arrow-up-tray" class="!rounded-xl">Aplicar no site</flux:button>
                        @else
                            <flux:button type="button" disabled icon="lock-closed" class="!rounded-xl opacity-50 cursor-not-allowed" title="Guarda primeiro a configuração para poderes aplicar no site">Aplicar no site</flux:button>
                        @endif
                        <a href="{{ route('site.public', [$site, 'pageSlug' => null, 'preview' => 1]) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-xl border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-stone-700 transition hover:bg-stone-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800">
                            Ver site <span aria-hidden="true">↗</span>
                        </a>
                    </div>
                </div>
            </form>
        @else
            <div class="mt-6 rounded-2xl bg-stone-50 p-6 text-sm text-stone-500 dark:bg-zinc-800/60">
                Este modelo ainda não tem campos configurados.
            </div>
        @endif
    </section>
</div>
