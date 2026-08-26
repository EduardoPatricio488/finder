<?php

namespace App\Livewire;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Component;

class CustomerAssistant extends Component
{
    public bool $open = false;

    public string $question = '';

    public string $answer = '';

    /** @var Collection<int, Product> */
    public Collection $suggestions;

    public function mount(): void
    {
        $this->suggestions = collect();
    }

    public function toggle(): void
    {
        $this->open = ! $this->open;
    }

    public function ask(): void
    {
        $this->validate(['question' => ['required', 'string', 'max:500']]);
        $question = Str::lower(Str::ascii($this->question));
        $budget = $this->extractBudget($question);
        $suggestions = Product::query()
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->with('category')
            ->when($budget !== null, fn ($query) => $query->where('price', '<=', $budget))
            ->when(Str::contains($question, ['prenda', 'presente', 'mae', 'mãe', 'oferecer']), fn ($query) => $query->where(function ($query): void {
                $query->where('tags', 'like', '%presente%')
                    ->orWhere('tags', 'like', '%casa%')
                    ->orWhere('description', 'like', '%presente%')
                    ->orWhere('name', 'like', '%caneca%');
            }))
            ->orderBy('price')
            ->limit(4)
            ->get();

        if ($suggestions->isEmpty() && $budget !== null) {
            $suggestions = Product::query()->where('is_active', true)->where('stock', '>', 0)->with('category')->orderBy('price')->limit(4)->get();
        }

        $this->suggestions = $suggestions;
        $this->answer = $suggestions->isEmpty()
            ? 'Neste momento não encontrei sugestões disponíveis. Experimente outro orçamento ou veja o catálogo completo.'
            : 'Tenho '.$suggestions->count().' sugestões'.($budget !== null ? ' até '.$this->money($budget) : '').':';
        $this->open = true;
    }

    private function extractBudget(string $question): ?float
    {
        preg_match('/(?:ate|até|menos de|maximo de|máximo de)\s*(\d+(?:[\.,]\d{1,2})?)/u', $question, $matches);

        return isset($matches[1]) ? (float) str_replace(',', '.', $matches[1]) : null;
    }

    private function money(float $value): string
    {
        return '€ '.number_format($value, 2, ',', '.');
    }

    public function render(): mixed
    {
        return view('livewire.customer-assistant');
    }
}
