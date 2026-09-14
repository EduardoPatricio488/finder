<?php

namespace App\Livewire;

use App\Models\Site;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Dashboard')]
class UserDashboard extends Component
{
    public function render()
    {
        $user = auth()->user();

        $sites = Site::query()
            ->where(function ($query) use ($user): void {
                $query->where('owner_id', $user->id)
                    ->orWhereHas('members', fn ($members) => $members->whereKey($user->id));
            })
            ->withCount(['pages', 'products'])
            ->latest('updated_at')
            ->get();

        return view('livewire.user-dashboard', [
            'sites' => $sites,
            'publishedCount' => $sites->where('is_published', true)->count(),
            'draftCount' => $sites->where('is_published', false)->count(),
            'totalProducts' => $sites->sum('products_count'),
            'totalPages' => $sites->sum('pages_count'),
        ]);
    }
}
