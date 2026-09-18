<div class="min-h-screen bg-zinc-50 p-6 dark:bg-zinc-950">
    <div class="mx-auto max-w-6xl">
        <div class="mb-6">
            <p class="text-sm font-medium text-indigo-600">{{ $site->name }}</p>
            <h1 class="mt-1 text-2xl font-semibold tracking-tight">Definições do website</h1>
            <p class="mt-1 text-sm text-zinc-500">Identidade, endereço e SEO deste website.</p>
        </div>

        <div class="mb-6 rounded-2xl border border-indigo-100 bg-indigo-50 p-4 dark:border-indigo-900/50 dark:bg-indigo-950/30">
            <p class="text-sm font-semibold text-indigo-900 dark:text-indigo-200">{{ $site->name }}</p>
            <p class="mt-1 text-xs text-indigo-700 dark:text-indigo-300">Estas definições pertencem exclusivamente a este website.</p>
        </div>


    @if(session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
    @endif

    <form wire:submit="save" class="space-y-6">
        <section class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <h2 class="font-semibold">Identidade</h2>
            <div class="mt-5 grid gap-5 md:grid-cols-2">
                <flux:input wire:model="name" label="Nome do website" />
                <flux:input wire:model="slug" label="Slug público" prefix="/site/" />
                <div class="md:col-span-2"><flux:textarea wire:model="tagline" label="Descrição curta" /></div>
                <div class="space-y-2">
                    <flux:label>Logótipo</flux:label>
                    <input wire:model="logoUpload" type="file" accept=".jpg,.jpeg,.png,.webp,.gif,.svg,image/jpeg,image/png,image/webp,image/gif,image/svg+xml" class="block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm">
                    <p class="text-xs text-zinc-500">Imagem do PC. Máximo: 10 MB.</p>
                    @if($logoUpload)<p class="text-xs text-emerald-600">Novo logótipo selecionado.</p>@elseif($logoUrl)<p class="text-xs text-zinc-500">Já existe um logótipo guardado.</p>@endif
                    @error('logoUpload')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="space-y-2">
                    <flux:label>Favicon</flux:label>
                    <input wire:model="faviconUpload" type="file" accept=".ico,.png,.jpg,.jpeg,.webp,.svg,image/x-icon,image/png,image/jpeg,image/webp,image/svg+xml" class="block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm">
                    <p class="text-xs text-zinc-500">Favicon do PC. Máximo: 5 MB.</p>
                    @if($faviconUpload)<p class="text-xs text-emerald-600">Novo favicon selecionado.</p>@elseif($faviconUrl)<p class="text-xs text-zinc-500">Já existe um favicon guardado.</p>@endif
                    @error('faviconUpload')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
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
                    <input wire:model="ogImageUpload" type="file" accept=".jpg,.jpeg,.png,.webp,.gif,.svg,image/jpeg,image/png,image/webp,image/gif,image/svg+xml" class="block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm">
                    <p class="text-xs text-zinc-500">Imagem Open Graph. Máximo: 10 MB.</p>
                    @if($ogImageUpload)<p class="text-xs text-emerald-600">Nova imagem Open Graph selecionada.</p>@elseif($ogImage)<p class="text-xs text-zinc-500">Já existe uma imagem Open Graph guardada.</p>@endif
                    @error('ogImageUpload')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <flux:switch wire:model="robotsIndex" label="Permitir indexação pelos motores de pesquisa" />
            </div>
        </section>

        <div class="flex justify-end"><flux:button type="submit" variant="primary" wire:loading.attr="disabled">Guardar definições</flux:button></div>
    </form>
</div>
