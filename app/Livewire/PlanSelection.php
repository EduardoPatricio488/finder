<?php

namespace App\Livewire;

use App\Models\Plan;
use App\Models\Site;
use Livewire\Component;

class PlanSelection extends Component
{
    public Site $site;

    public function mount(): void
    {
        $site = \App\Support\SiteContext::current();
        abort_unless($site instanceof Site && $site->isManageableBy(auth()->user()), 403);
        $this->site = $site;
    }

    public function selectPlan($planId)
    {
        $this->site->update(['plan_id' => $planId]);

        session()->flash('status', 'Plano atualizado com sucesso! Agora tens acesso Pro.');

        return redirect()->route('admin.site.dashboard');
    }

    public function render()
    {
        return view('livewire.upgrade-selection', [
            'plans' => Plan::all(),
        ])->layout('layouts.app');
    }
}
