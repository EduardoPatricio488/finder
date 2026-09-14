<?php

namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.landing')]
#[Title('Finder — Build your website. Make it yours.')]
class LandingPage extends Component
{
    public function render()
    {
        return view('livewire.landing-page');
    }
}
