<div class="fixed bottom-5 right-5 z-50 flex flex-col items-end gap-3 sm:bottom-6 sm:right-6">
    @if ($open)
        <section class="w-[min(22rem,calc(100vw-2rem))] overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-2xl dark:border-zinc-700 dark:bg-zinc-900" aria-label="Assistente IA">
            <div class="flex items-center justify-between bg-stone-950 px-4 py-3 text-white">
                <div class="flex items-center gap-2"><span class="flex size-7 items-center justify-center rounded-full bg-amber-400 text-sm text-stone-950">✦</span><div><p class="text-sm font-semibold">Assistente IA</p><p class="text-[11px] text-stone-300">Dados da sua loja</p></div></div>
                <button type="button" wire:click="close" aria-label="Fechar assistente" class="flex size-8 items-center justify-center rounded-full text-lg text-stone-300 hover:bg-stone-800 hover:text-white">&times;</button>
            </div>
            <div class="max-h-[min(26rem,60vh)] space-y-3 overflow-y-auto p-4">
                @if ($answer)<div class="rounded-xl rounded-tl-none bg-amber-50 p-3 text-sm leading-6 text-stone-700">{{ $answer }}</div>@else<div class="rounded-xl rounded-tl-none bg-stone-100 p-3 text-sm leading-6 text-stone-600">Olá. Posso analisar vendas, clientes e stock. O que pretende saber?</div>@endif
                @error('question')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <form wire:submit.prevent="ask" class="flex gap-2 border-t border-stone-200 p-3 dark:border-zinc-700"><input wire:model.live="question" type="text" placeholder="Escreva uma pergunta..." aria-label="Pergunta para o assistente" class="min-w-0 flex-1 rounded-lg border-stone-300 px-3 py-2 text-sm"><button type="submit" aria-label="Enviar pergunta" class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-amber-400 text-lg text-stone-950 hover:bg-amber-300" wire:loading.attr="disabled" wire:target="ask"><span wire:loading.remove wire:target="ask">&uarr;</span><span wire:loading wire:target="ask">...</span></button></form>
        </section>
    @endif
    <button type="button" wire:click="toggle" aria-label="{{ $open ? 'Fechar assistente IA' : 'Abrir assistente IA' }}" class="flex size-14 items-center justify-center rounded-full bg-stone-950 text-2xl text-amber-300 shadow-xl ring-4 ring-white transition hover:scale-105 hover:bg-stone-800 dark:ring-zinc-900">{{ $open ? '&times;' : '✦' }}</button>
</div>
