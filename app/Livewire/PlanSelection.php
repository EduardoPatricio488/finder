<?php

namespace App\Livewire;

use App\Models\Plan;
use App\Models\Site;
use Livewire\Component;

class PlanSelection extends Component
{
    public Site $site;

    public function mount(Site $site)
    {
        $this->site = $site;
    }

    public function selectPlan($planId)
    {
        $this->site->update(['plan_id' => $planId]);

        session()->flash('status', 'Plano atualizado com sucesso! Agora tens acesso Pro.');

        return redirect()->route('admin.site.dashboard', $this->site->slug);
    }

    public function render()
    {
        return view('livewire.plan-selection', [
            'plans' => Plan::all()
        ])->layout('layouts.app');
    }
}
