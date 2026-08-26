<?php

namespace App\Livewire;

use App\Models\Plan;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.hub')]
class UpgradeSelection extends Component
{
    public function selectPlan($planId)
    {
        $user = auth()->user();
        $plan = Plan::findOrFail($planId);
        $user->update(['plan' => $plan->name]);
        session()->flash('status', 'Upgrade concluído! Já podes criar o teu site.');
        return redirect()->route('home');
    }

    public function render()
    {
        return view('livewire.upgrade-selection', [
            'plans' => Plan::orderBy('price_monthly', 'asc')->get()
        ]);
    }
}
