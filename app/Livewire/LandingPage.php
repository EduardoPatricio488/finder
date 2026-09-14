<?php

namespace App\Livewire;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.landing')]
#[Title('Finder — Cria. Personaliza. Publica.')]
class LandingPage extends Component
{
    public function render()
    {
        /** @var Collection<int, Plan> $plans */
        $plans = Plan::query()
            ->orderBy('price_monthly')
            ->get();

        return view('livewire.landing-page', compact('plans'));
    }
}
