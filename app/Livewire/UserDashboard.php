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
    public string $filter = 'all';

    public function setFilter(string $filter): void
    {
        abort_unless(in_array($filter, ['all', 'published', 'drafts', 'products'], true), 422);

        $this->filter = $filter;
        $this->dispatch('dashboard-filter-changed');
    }

    public function clearFilter(): void
    {
        $this->filter = 'all';
    }

    public function render()
    {
        $user = auth()->user();

        $allSites = Site::query()
            ->where(function ($query) use ($user): void {
                $query->where('owner_id', $user->id)
                    ->orWhereHas('members', fn ($members) => $members->whereKey($user->id));
            })
            ->withCount(['pages', 'products'])
            ->latest('updated_at')
            ->get();

        $sites = match ($this->filter) {
            'published' => $allSites->where('is_published', true)->values(),
            'drafts' => $allSites->where('is_published', false)->values(),
            'products' => $allSites->where('products_count', '>', 0)->values(),
            default => $allSites,
        };

        return view('livewire.user-dashboard', [
            'sites' => $sites,
            'allSitesCount' => $allSites->count(),
            'publishedCount' => $allSites->where('is_published', true)->count(),
            'draftCount' => $allSites->where('is_published', false)->count(),
            'totalProducts' => $allSites->sum('products_count'),
            'totalPages' => $allSites->sum('pages_count'),
        ]);
    }
}
