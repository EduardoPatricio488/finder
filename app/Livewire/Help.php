<?php

namespace App\Livewire;

use App\Support\SiteContext;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Ajuda — Guia completo do Finder')]
class Help extends Component
{
    public function render(): mixed
    {
        return view('livewire.help', [
            'hasDashboard' => Route::has('dashboard'),
            'hasSiteCreate' => Route::has('site.create'),
            'hasBuilder' => Route::has('builder.edit') && SiteContext::current() !== null,
            'currentSite' => SiteContext::current(),
        ]);
    }
}
