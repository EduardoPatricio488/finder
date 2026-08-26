<div class="min-h-screen bg-[#fcfaf7] py-16 px-6">
    <div class="max-w-4xl mx-auto">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-stone-400 hover:text-stone-950 mb-12 uppercase text-[9px] font-black tracking-[0.2em]">
            <flux:icon name="arrow-left" variant="micro" class="size-3" /> Voltar
        </a>

        <header class="mb-16 text-left">
            <h1 class="text-4xl font-black italic uppercase tracking-tighter text-stone-950">Finder Premium</h1>
            <p class="mt-4 text-sm text-stone-500 max-w-md">Escolhe o nível de acesso para começares a construir.</p>
        </header>

        <div class="grid md:grid-cols-2 gap-8">
            @foreach($plans as $plan)
                @php
                    $isFree = str_contains(strtolower($plan->name), 'free');
                    $isCurrent = str_contains(strtolower(auth()->user()->plan), strtolower($plan->name));
                @endphp

                <div @class([
                    'relative p-10 rounded-[2.5rem] border transition-all flex flex-col',
                    'bg-white border-stone-200' => $isFree,
                    'bg-stone-950 border-stone-950 text-white shadow-2xl scale-[1.05]' => !$isFree
                ])>
                    <h2 class="text-xl font-black uppercase italic">{{ $plan->name }}</h2>
                    <div class="mt-4 flex items-baseline gap-1">
                        <span class="text-5xl font-black tracking-tighter">€{{ number_format($plan->price_monthly / 100, 0) }}</span>
                        <span class="text-[10px] font-bold uppercase opacity-40">/ mês</span>
                    </div>

                    <ul class="mt-8 space-y-4 flex-1">
                        <li class="flex items-center gap-3 text-[11px] font-bold uppercase tracking-tight">
                            <flux:icon name="{{ $isFree ? 'x-mark' : 'check' }}" @class(['size-4', 'text-red-500' => $isFree, 'text-emerald-500' => !$isFree]) />
                            {{ $isFree ? '0 Sites' : 'Sites Ilimitados' }}
                        </li>
                    </ul>

                    @if($isCurrent)
                        <div class="mt-8 w-full text-center py-4 text-[9px] font-black uppercase tracking-widest opacity-50 border border-current rounded-xl">Plano Ativo</div>
                    @else
                        <flux:button wire:click="selectPlan({{ $plan->id }})" @class(['mt-8 w-full !rounded-xl !py-6 !text-[10px] !font-black !uppercase !tracking-widest', '!bg-amber-400 !text-stone-950' => !$isFree])>
                            Ativar {{ $plan->name }}
                        </flux:button>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
