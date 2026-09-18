<?php

namespace App\Livewire;

use App\Models\Site;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.hub')]
class SiteDirectory extends Component
{
    public function create()
    {
        return redirect()->route('site.create');
    }

    public function render()
    {
        $user = auth()->user();

        $sites = Site::query()
            ->where(function ($query) use ($user): void {
                $query->where('owner_id', $user->id)
                    ->orWhereHas('members', fn ($members) => $members->whereKey($user->id));
            })
            ->with('owner')
            ->latest('updated_at')
            ->get();

        return view('livewire.site-directory', [
            'sites' => $sites,
            'currentPlanLabel' => 'Finder '.($user?->plan ?? 'Free'),
            'canCreateSites' => true,
        ]);
    }
}
