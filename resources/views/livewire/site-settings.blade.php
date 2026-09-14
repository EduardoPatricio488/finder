<div class="mx-auto max-w-4xl space-y-6 p-6">
    <div><p class="text-sm font-medium text-indigo-600">{{ $site->name }}</p><h1 class="mt-1 text-2xl font-semibold tracking-tight">Definições do website</h1><p class="mt-1 text-sm text-zinc-500">Identidade, endereço e SEO do teu website.</p></div>

    @if(session('status'))<div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>@endif

    <form wire:submit="save" class="space-y-6">
        <section class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <h2 class="font-semibold">Identidade</h2>
            <div class="mt-5 grid gap-5 md:grid-cols-2">
                <flux:input wire:model="name" label="Nome do website" />
                <flux:input wire:model="slug" label="Slug público" prefix="/site/" />
                <div class="md:col-span-2"><flux:textarea wire:model="tagline" label="Descrição curta" /></div>
                <flux:input wire:model="logoUrl" label="URL do logótipo" placeholder="https://..." />
                <flux:input wire:model="faviconUrl" label="URL do favicon" placeholder="https://..." />
            </div>
        </section>

        <section class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <h2 class="font-semibold">SEO</h2>
            <div class="mt-5 space-y-5">
                <flux:input wire:model="seoTitle" label="Título SEO" maxlength="255" />
                <flux:textarea wire:model="seoDescription" label="Descrição SEO" maxlength="500" />
                <flux:input wire:model="ogImage" label="Imagem Open Graph" placeholder="https://..." />
                <flux:switch wire:model="robotsIndex" label="Permitir indexação pelos motores de pesquisa" />
            </div>
        </section>

        <div class="flex justify-end"><flux:button type="submit" variant="primary" wire:loading.attr="disabled">Guardar definições</flux:button></div>
    </form>
</div>
