<?php

namespace App\Livewire;

use App\Livewire\Concerns\InteractsWithSiteContext;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class AdminAssistant extends Component
{
    use InteractsWithSiteContext;

    public bool $open = false;

    public string $question = '';

    public string $answer = '';

    public function toggle(): void
    {
        $this->open = ! $this->open;
    }

    public function close(): void
    {
        $this->open = false;
    }

    public function ask(): void
    {
        $this->validate(['question' => ['required', 'string', 'max:500']]);
        $this->open = true;
        $question = Str::lower($this->question);

        $this->answer = match (true) {
            Str::contains($question, ['vendas', 'receita']) && Str::contains($question, ['mês', 'mes']) => $this->monthlySalesAnswer(),
            Str::contains($question, ['repor', 'stock baixo', 'stock']) => $this->restockAnswer(),
            Str::contains($question, ['melhores clientes', 'melhor cliente', 'clientes']) => $this->bestCustomersAnswer(),
            Str::contains($question, ['melhor dia', 'dia de vendas']) => $this->bestSalesDayAnswer(),
            Str::contains($question, ['prejuízo', 'prejuizo', 'prejuízos', 'prejuizos']) => 'Ainda não consigo calcular prejuízo com rigor: os produtos não têm custo de compra registado. Adicione esse dado para eu comparar custo e receita.',
            default => 'Posso responder sobre vendas deste mês, produtos a repor, melhores clientes, melhor dia de vendas e stock baixo.',
        };
    }

    private function monthlySalesAnswer(): string
    {
        $current = $this->salesBetween(now()->startOfMonth(), now()->endOfMonth());
        $previous = $this->salesBetween(now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth());
        $change = $previous > 0 ? (($current - $previous) / $previous) * 100 : null;
        $comparison = $change === null ? 'não há dados do mês anterior para comparação' : sprintf('%s%.1f%% relativamente ao mês anterior', $change >= 0 ? '+' : '', $change);

        return sprintf('Este mês, a loja registou %s em vendas. Isso representa %s. ', $this->money($current), $comparison).sprintf('O período anterior totalizou %s.', $this->money($previous));
    }

    private function restockAnswer(): string
    {
        $products = $this->currentSite()->products()->whereColumn('stock', '<=', 'minimum_stock')->orderBy('stock')->limit(5)->pluck('name')->all();

        return $products === [] ? 'Não há produtos abaixo do stock mínimo neste momento.' : 'Deve considerar repor: '.implode(', ', $products).'.';
    }

    private function bestCustomersAnswer(): string
    {
        $customers = $this->currentSite()->customers()->withSum('orders', 'total')->withCount('orders')->orderByDesc('orders_sum_total')->limit(3)->get();

        if ($customers->isEmpty()) {
            return 'Ainda não existem compras associadas a clientes.';
        }

        return 'Os melhores clientes são: '.$customers->map(fn (Customer $customer): string => $customer->name.' ('.$this->money((float) $customer->orders_sum_total).', '.$customer->orders_count.' compras)')->join('; ').'.';
    }

    private function bestSalesDayAnswer(): string
    {
        $day = $this->currentSite()->orders()->where('status', '!=', 'cancelada')->whereBetween('sold_at', [now()->startOfMonth(), now()->endOfMonth()])->select(DB::raw('date(sold_at) as day'), DB::raw('sum(total) as revenue'))->groupBy('day')->orderByDesc('revenue')->first();

        return $day === null ? 'Ainda não existem vendas este mês para encontrar o melhor dia.' : 'O melhor dia deste mês foi '.date('d/m/Y', strtotime($day->day)).', com '.$this->money((float) $day->revenue).' em vendas.';
    }

    private function salesBetween($from, $to): float
    {
        return (float) $this->currentSite()->orders()->where('status', '!=', 'cancelada')->whereBetween('sold_at', [$from, $to])->sum('total');
    }

    private function money(float $value): string
    {
        return '€ '.number_format($value, 2, ',', '.');
    }

    public function render(): mixed
    {
        return view('livewire.admin-assistant-widget');
    }
}
