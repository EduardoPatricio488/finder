<div class="mx-auto max-w-7xl space-y-6 p-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-semibold text-indigo-600">{{ $site->name }}</p>
            <h1 class="text-3xl font-bold tracking-tight">Biblioteca de media</h1>
            <p class="mt-1 text-sm text-zinc-500">Imagens e ficheiros deste website, isolados por tenant.</p>
        </div>
        <a href="{{ route('builder.edit', $site) }}" wire:navigate class="rounded-xl border border-zinc-200 px-4 py-2 text-sm font-semibold dark:border-zinc-700">Voltar ao editor</a>
    </div>

    @if(session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('status') }}</div>
    @endif

    <div class="grid gap-6 lg:grid-cols-[360px_1fr]">
        <form wire:submit="uploadMedia" class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <h2 class="font-semibold">Adicionar ficheiro</h2>
            <label class="mt-4 block cursor-pointer rounded-xl border-2 border-dashed border-zinc-300 p-4 text-center hover:border-indigo-400 dark:border-zinc-700">
                <input type="file" wire:model="upload" accept="image/jpeg,image/png,image/webp,image/gif,image/svg+xml" class="sr-only">
                <span class="text-sm font-medium">Escolher imagem do computador</span>
                <span class="mt-1 block text-xs text-zinc-500">JPG, PNG, WEBP, GIF ou SVG · máximo 10 MB</span>
            </label>
            @error('upload') <p class="mt-2 text-xs text-red-600">{{ $message }}</p> @enderror

            @if ($upload)
                <div class="mt-4 overflow-hidden rounded-xl border border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-950">
                    <img src="{{ $upload->temporaryUrl() }}" alt="Pré-visualização" class="max-h-56 w-full object-contain">
                    <div class="border-t border-zinc-200 px-3 py-2 text-xs dark:border-zinc-700">
                        <p class="truncate font-semibold">{{ $upload->getClientOriginalName() }}</p>
                        <p class="mt-1 text-zinc-500">{{ number_format($upload->getSize() / 1024, 1) }} KB</p>
                    </div>
                </div>
            @endif

            <div wire:loading wire:target="upload" class="mt-3 text-xs text-indigo-600">A carregar a imagem...</div>
            <input wire:model="altText" placeholder="Texto alternativo (ex.: Foto profissional do Eduardo)" class="mt-4 w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-950">
            <div class="mt-4">
                <label class="mb-2 block text-sm font-semibold">Onde vai ser aplicada?</label>
                <select wire:model="placement" class="w-full rounded-xl border border-zinc-200 bg-white px-3 py-2.5 text-sm dark:border-zinc-700 dark:bg-zinc-950">
                    <option value="logo">Logótipo</option>
                    <option value="hero">Capa / Hero principal</option>
                    <option value="about">Sobre mim / Sobre nós</option>
                    <option value="experience">Experiência</option>
                    <option value="education">Formação</option>
                    <option value="skills">Competências</option>
                    <option value="projects">Projetos</option>
                    <option value="services">Serviços</option>
                    <option value="testimonials">Testemunhos</option>
                    <option value="gallery">Galeria</option>
                    <option value="contact">Contacto</option>
                    <option value="background">Fundo de uma secção</option>
                    <option value="footer">Rodapé</option>
                </select>
                <p class="mt-2 text-xs leading-5 text-zinc-500">
                    Exemplo: escolhe <strong>Projetos</strong> para indicar que esta imagem será usada numa imagem de destaque de um projeto no website.
                </p>
                @error('placement') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <button type="submit" class="mt-4 w-full rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white disabled:cursor-not-allowed disabled:opacity-60" wire:loading.attr="disabled" wire:target="uploadMedia,upload">
                <span wire:loading.remove wire:target="uploadMedia">Carregar</span>
                <span wire:loading wire:target="uploadMedia">A guardar...</span>
            </button>
        </form>

        <div class="space-y-4">
            <input wire:model.live.debounce.300ms="search" placeholder="Pesquisar ficheiros..." class="w-full rounded-xl border border-zinc-200 bg-white px-4 py-3 text-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @forelse($media as $item)
                    @php($mediaUrl = Storage::disk($item->disk ?: 'public')->url($item->path))
                    <article class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="relative aspect-[4/3] overflow-hidden bg-zinc-100 dark:bg-zinc-950">
                            <img src="{{ $mediaUrl }}" alt="{{ $item->alt_text }}" class="h-full w-full object-cover" loading="lazy" decoding="async" onerror="this.style.display='none';this.nextElementSibling.classList.remove('hidden');">
                            <div class="absolute inset-0 hidden items-center justify-center p-4 text-center text-xs text-zinc-500">Não foi possível carregar esta imagem.<br>Verifica o armazenamento público do Finder.</div>
                        </div>
                        <div class="p-4">
                            <p class="truncate text-sm font-semibold">{{ $item->original_name }}</p>
                            <p class="mt-1 text-xs text-zinc-500">{{ number_format($item->size / 1024, 1) }} KB @if($item->width) · {{ $item->width }}×{{ $item->height }}@endif</p>
                            @php
                                $placementLabels = [
                                    'logo' => 'Logótipo',
                                    'hero' => 'Capa / Hero',
                                    'about' => 'Sobre mim / Sobre nós',
                                    'experience' => 'Experiência',
                                    'education' => 'Formação',
                                    'skills' => 'Competências',
                                    'projects' => 'Projetos',
                                    'services' => 'Serviços',
                                    'testimonials' => 'Testemunhos',
                                    'gallery' => 'Galeria',
                                    'contact' => 'Contacto',
                                    'background' => 'Fundo de secção',
                                    'footer' => 'Rodapé',
                                ];
                            @endphp
                            <div class="mt-3 rounded-xl bg-indigo-50 px-3 py-2 text-xs text-indigo-800 dark:bg-indigo-950/40 dark:text-indigo-200">
                                <span class="font-bold">Aplicação:</span> {{ $placementLabels[$item->placement ?? 'gallery'] ?? 'Galeria' }}
                            </div>
                            <button wire:click="deleteMedia({{ $item->id }})" wire:confirm="Eliminar este ficheiro?" class="mt-3 text-xs font-semibold text-red-600">Eliminar</button>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-zinc-300 p-10 text-center text-sm text-zinc-500 sm:col-span-2 xl:col-span-3">Ainda não existem ficheiros.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
