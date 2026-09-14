<div class="mx-auto max-w-6xl space-y-6 p-6">
    <div>
        <p class="text-sm font-medium text-indigo-600">{{ $site->name }}</p>
        <h1 class="mt-1 text-2xl font-semibold">Menus</h1>
        <p class="mt-1 text-sm text-zinc-500">Configura a navegação do website e cria dropdowns sem editar código.</p>
    </div>

    @if (session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('status') }}</div>
    @endif

    <div class="grid gap-6 lg:grid-cols-[1fr_1.4fr]">
        <form wire:submit="saveMenu" class="rounded-2xl border bg-white p-5 shadow-sm space-y-4">
            <h2 class="font-semibold">Definições do menu</h2>
            <div>
                <label class="text-sm font-medium">Nome</label>
                <input wire:model="menuName" class="mt-1 w-full rounded-xl border px-3 py-2" />
                @error('menuName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-sm font-medium">Localização</label>
                <select wire:model="menuLocation" class="mt-1 w-full rounded-xl border px-3 py-2">
                    <option value="header">Cabeçalho</option>
                    <option value="footer">Rodapé</option>
                    <option value="mobile">Mobile</option>
                </select>
            </div>
            <button class="rounded-xl bg-zinc-900 px-4 py-2 text-sm font-medium text-white">Guardar menu</button>
        </form>

        <div class="rounded-2xl border bg-white p-5 shadow-sm space-y-5">
            <h2 class="font-semibold">Adicionar item</h2>
            <div class="grid gap-3 sm:grid-cols-2">
                <input wire:model="label" placeholder="Nome do item" class="rounded-xl border px-3 py-2" />
                <select wire:model="pageId" class="rounded-xl border px-3 py-2">
                    <option value="">Link externo</option>
                    @foreach ($pages as $page)
                        <option value="{{ $page->id }}">{{ $page->name }}</option>
                    @endforeach
                </select>
                <input wire:model="url" placeholder="https://... ou /pagina" class="rounded-xl border px-3 py-2" />
                <select wire:model="parentId" class="rounded-xl border px-3 py-2">
                    <option value="">Item principal</option>
                    @foreach ($items as $item)
                        <option value="{{ $item->id }}">Submenu de: {{ $item->label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-4">
                <select wire:model="target" class="rounded-xl border px-3 py-2 text-sm">
                    <option value="_self">Abrir na mesma janela</option>
                    <option value="_blank">Abrir nova janela</option>
                </select>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model="isVisible" /> Visível</label>
                <button wire:click="addItem" class="ml-auto rounded-xl bg-indigo-600 px-4 py-2 text-sm font-medium text-white">Adicionar</button>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border bg-white p-5 shadow-sm">
        <h2 class="font-semibold">Estrutura</h2>
        <div class="mt-4 space-y-2">
            @forelse ($items as $item)
                <div class="flex items-center gap-3 rounded-xl border px-4 py-3">
                    <span class="flex-1 font-medium">{{ $item->label }}</span>
                    <span class="text-xs text-zinc-500">{{ $item->is_visible ? 'Visível' : 'Oculto' }}</span>
                    <button wire:click="toggleItem({{ $item->id }})" class="text-sm text-indigo-600">{{ $item->is_visible ? 'Ocultar' : 'Mostrar' }}</button>
                    <button wire:click="removeItem({{ $item->id }})" wire:confirm="Eliminar este item?" class="text-sm text-red-600">Eliminar</button>
                </div>
                @foreach ($item->children as $child)
                    <div class="ml-8 flex items-center gap-3 rounded-xl border border-dashed px-4 py-3">
                        <span class="flex-1 text-sm">↳ {{ $child->label }}</span>
                        <button wire:click="removeItem({{ $child->id }})" wire:confirm="Eliminar este item?" class="text-sm text-red-600">Eliminar</button>
                    </div>
                @endforeach
            @empty
                <div class="rounded-xl bg-zinc-50 p-6 text-center text-sm text-zinc-500">Ainda não existem itens neste menu.</div>
            @endforelse
        </div>
    </div>
</div>
