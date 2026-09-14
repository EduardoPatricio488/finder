<?php

namespace App\Livewire;

use App\Models\Site;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Admin Global')]
class PlatformAdminDashboard extends Component
{
    public function render()
    {
        abort_unless(auth()->user()?->isAdministrator(), 403);

        return view('livewire.platform-admin-dashboard', [
            'usersCount' => User::query()->count(),
            'sitesCount' => Site::query()->count(),
            'publishedCount' => Site::query()->where('is_published', true)->where('status', 'published')->count(),
            'draftCount' => Site::query()->where('is_published', false)->orWhere('status', 'draft')->count(),
            'newUsers' => User::query()->latest()->limit(8)->get(),
            'recentSites' => Site::query()->with('owner')->latest()->limit(8)->get(),
        ]);
    }
}
