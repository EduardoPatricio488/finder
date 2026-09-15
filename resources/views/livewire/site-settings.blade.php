<div class="mx-auto max-w-4xl space-y-6 p-6">
    <div>
        <p class="text-sm font-medium text-indigo-600">{{ $site->name }}</p>
        <h1 class="mt-1 text-2xl font-semibold tracking-tight">Definições do website</h1>
        <p class="mt-1 text-sm text-zinc-500">Identidade, endereço e SEO do teu website.</p>
    </div>

    @if(session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <form wire:submit="save" class="space-y-6">
        <section class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <h2 class="font-semibold">Identidade</h2>
            <div class="mt-5 grid gap-5 md:grid-cols-2">
                <flux:input wire:model="name" label="Nome do website" />
                <flux:input wire:model="slug" label="Slug público" prefix="/site/" />
                <div class="md:col-span-2">
                    <flux:textarea wire:model="tagline" label="Descrição curta" />
                </div>

                <div class="space-y-2">
                    <flux:label>Logótipo</flux:label>
                    <input
                        wire:model="logoUpload"
                        type="file"
                        accept=".jpg,.jpeg,.png,.webp,.gif,.svg,image/jpeg,image/png,image/webp,image/gif,image/svg+xml"
                        class="block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-700 file:mr-3 file:rounded-md file:border-0 file:bg-zinc-100 file:px-3 file:py-1.5 file:text-sm file:font-medium dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:file:bg-zinc-800"
                    />
                    <p class="text-xs text-zinc-500">Escolhe uma imagem diretamente do teu PC. Máximo: 10 MB.</p>
                    @if($logoUpload)
                        <p class="text-xs text-emerald-600">Novo logótipo selecionado.</p>
                    @elseif($logoUrl)
                        <p class="text-xs text-zinc-500">Já existe um logótipo guardado.</p>
                    @endif
                    @error('logoUpload') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-2">
                    <flux:label>Favicon</flux:label>
                    <input
                        wire:model="faviconUpload"
                        type="file"
                        accept=".ico,.png,.jpg,.jpeg,.webp,.svg,image/x-icon,image/png,image/jpeg,image/webp,image/svg+xml"
                        class="block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-700 file:mr-3 file:rounded-md file:border-0 file:bg-zinc-100 file:px-3 file:py-1.5 file:text-sm file:font-medium dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:file:bg-zinc-800"
                    />
                    <p class="text-xs text-zinc-500">Escolhe o favicon diretamente do teu PC. Máximo: 5 MB.</p>
                    @if($faviconUpload)
                        <p class="text-xs text-emerald-600">Novo favicon selecionado.</p>
                    @elseif($faviconUrl)
                        <p class="text-xs text-zinc-500">Já existe um favicon guardado.</p>
                    @endif
                    @error('faviconUpload') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <h2 class="font-semibold">SEO</h2>
            <div class="mt-5 space-y-5">
                <flux:input wire:model="seoTitle" label="Título SEO" maxlength="255" />
                <flux:textarea wire:model="seoDescription" label="Descrição SEO" maxlength="500" />

                <div class="space-y-2">
                    <flux:label>Imagem Open Graph</flux:label>
                    <input
                        wire:model="ogImageUpload"
                        type="file"
                        accept=".jpg,.jpeg,.png,.webp,.gif,.svg,image/jpeg,image/png,image/webp,image/gif,image/svg+xml"
                        class="block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-700 file:mr-3 file:rounded-md file:border-0 file:bg-zinc-100 file:px-3 file:py-1.5 file:text-sm file:font-medium dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:file:bg-zinc-800"
                    />
                    <p class="text-xs text-zinc-500">Escolhe a imagem Open Graph diretamente do teu PC. Máximo: 10 MB.</p>
                    @if($ogImageUpload)
                        <p class="text-xs text-emerald-600">Nova imagem Open Graph selecionada.</p>
                    @elseif($ogImage)
                        <p class="text-xs text-zinc-500">Já existe uma imagem Open Graph guardada.</p>
                    @endif
                    @error('ogImageUpload') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <flux:switch wire:model="robotsIndex" label="Permitir indexação pelos motores de pesquisa" />
            </div>
        </section>

        <div class="flex justify-end">
            <flux:button type="submit" variant="primary" wire:loading.attr="disabled">
                Guardar definições
            </flux:button>
        </div>
    </form>
</div>
