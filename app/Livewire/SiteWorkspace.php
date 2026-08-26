<?php

namespace App\Livewire;

use App\Models\Site;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.hub')]
class SiteWorkspace extends Component
{
    public Site $site;

    public function mount(Site $site): void
    {
        abort_unless($site->isVisibleTo(auth()->user()), 404);

        $this->site = $site;

        if ($site->hasDedicatedHome()) {
            $this->redirect($site->destinationUrl(), navigate: true);
        }
    }

    public function render(): mixed
    {
        return view('livewire.site-workspace');
    }
}
