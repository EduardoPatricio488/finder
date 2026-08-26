<div class="py-12 bg-zinc-50 dark:bg-zinc-950 min-h-screen">
    <div class="max-w-5xl mx-auto px-6">
        <div class="text-center mb-16">
            <flux:heading size="xl" class="font-black uppercase italic tracking-tighter">Escolha o seu nível</flux:heading>
            <flux:subheading class="mt-2 text-base">Desbloqueie o poder total do Finder para <b>{{ $site->name }}</b></flux:subheading>
        </div>

        <div class="grid md:grid-cols-2 gap-8">
            @foreach($plans as $plan)
                <div class="relative bg-white dark:bg-zinc-900 border-2 {{ $site->plan_id == $plan->id ? 'border-amber-500 shadow-xl shadow-amber-500/10' : 'border-zinc-200 dark:border-zinc-800' }} rounded-[2.5rem] p-10 flex flex-col">
                    @if($site->plan_id == $plan->id)
                        <span class="absolute -top-4 left-1/2 -translate-x-1/2 bg-amber-500 text-white text-[10px] font-black uppercase px-4 py-1.5 rounded-full tracking-widest">Plano Atual</span>
                    @endif

                    <div class="mb-8">
                        <h2 class="text-2xl font-black uppercase italic tracking-tight">{{ $plan->name }}</h2>
                        <div class="mt-4 flex items-baseline">
                            <span class="text-5xl font-black">€{{ number_format($plan->price_monthly / 100, 0) }}</span>
                            <span class="text-zinc-400 ml-2 font-bold uppercase text-xs">/ mês</span>
                        </div>
                    </div>

                    <ul class="space-y-4 mb-10 flex-1">
                        <li class="flex items-center gap-3 text-sm font-medium">
                            <flux:icon name="check-circle" variant="solid" class="text-emerald-500 size-5" />
                            Até {{ $plan->product_limit }} produtos ativos
                        </li>
                        <li class="flex items-center gap-3 text-sm font-medium {{ $plan->has_reports ? 'text-zinc-900 dark:text-white' : 'text-zinc-300 dark:text-zinc-700' }}">
                            <flux:icon name="{{ $plan->has_reports ? 'check-circle' : 'no-symbol' }}" variant="solid" class="size-5" />
                            Relatórios e Estatísticas Avançadas
                        </li>
                        <li class="flex items-center gap-3 text-sm font-medium {{ $plan->has_ai ? 'text-zinc-900 dark:text-white' : 'text-zinc-300 dark:text-zinc-700' }}">
                            <flux:icon name="{{ $plan->has_ai ? 'sparkles' : 'no-symbol' }}" variant="solid" class="size-5" />
                            Assistente de Gestão com IA
                        </li>
                    </ul>

                    @if($site->plan_id != $plan->id)
                        <flux:button wire:click="selectPlan({{ $plan->id }})" variant="primary" class="w-full !rounded-2xl !py-6 !font-black !uppercase !tracking-widest">
                            Ativar Plano {{ $plan->name }} 🚀
                        </flux:button>
                    @else
                        <flux:button disabled class="w-full !rounded-2xl !py-6 !bg-zinc-100 !text-zinc-400">Plano em vigor</flux:button>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>