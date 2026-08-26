<div class="flex items-center justify-center min-h-screen bg-zinc-50 dark:bg-zinc-950 px-4">
    <div class="w-full max-w-lg">
        <flux:card class="p-8 shadow-2xl border-zinc-200 dark:border-zinc-800">
            <div class="space-y-8">
                <div class="text-center">
                    <div class="inline-flex p-3 rounded-2xl bg-amber-500/10 text-amber-600 mb-4">
                        <flux:icon name="building-storefront" variant="outline" class="size-8" />
                    </div>
                    <flux:heading size="xl" class="font-black uppercase italic tracking-tighter">Lançar Novo Projeto</flux:heading>
                    <flux:subheading class="mt-2">Escolha um nome e um link único para a sua loja.</flux:subheading>
                </div>

                <form wire:submit="create" class="space-y-6">
                    <flux:input 
                        wire:model.live="name" 
                        label="Nome da Loja" 
                        placeholder="Ex: Minha Boutique" 
                    />
                    
                    <flux:input 
                        wire:model="slug" 
                        label="Endereço (Slug)" 
                        prefix="finder.test/sites/" 
                    />

                    <div class="pt-4">
                        <flux:button type="submit" variant="primary" class="w-full h-12 font-black uppercase tracking-widest text-xs">
                            Criar Loja Agora 🚀
                        </flux:button>
                    </div>
                </form>
            </div>
        </flux:card>
    </div>
</div>
