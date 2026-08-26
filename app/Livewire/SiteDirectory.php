<?php

namespace App\Livewire;

use App\Models\Site;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Str;

#[Layout('layouts.hub')]
class SiteDirectory extends Component
{
    /**
     * Tenta redirecionar para a criação de loja.
     * Bloqueia se o utilizador for do plano Free.
     */
    public function create()
    {
        $user = auth()->user();

        // Pegamos no nome do plano e convertemos para minúsculas para comparar com segurança
        $planName = strtolower(is_object($user?->plan) ? $user->plan->name : ($user?->plan ?? 'free'));

        // Se o plano contiver a palavra "free", redireciona para a secção de preços
        if (str_contains($planName, 'free')) {
            session()->flash('error', 'O seu plano atual não permite a criação de novos sites.');
            return redirect()->route('home', ['#planos']);
        }

        return redirect()->route('site.create');
    }

    public function render()
{
    $user = auth()->user();

    // Normalização do plano
    $planName = strtolower($user?->plan ?? 'free');

    // SÓ pode criar se o plano NÃO for free e se estiver logado
    // O limite para Free é rigorosamente ZERO
    $canCreateSites = auth()->check() && !str_contains($planName, 'free');

    return view('livewire.site-directory', [
        'sites' => Site::where('is_published', true)->with('owner')->get(),
        'currentPlanLabel' => 'Finder ' . ($user?->plan ?? 'Free'),
        'canCreateSites' => $canCreateSites,
    ]);
}
}
