<?php

namespace App\Livewire;

use App\Models\Site;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Websites')]
class PlatformWebsites extends Component
{
    public string $search = '';

    public string $status = 'all';

    public function access(int $siteId): void
    {
        $site = Site::query()->findOrFail($siteId);

        session(['platform_admin_site_id' => $site->id]);

        $site->adminAuditLogs()->create([
            'actor_id' => auth()->id(),
            'action' => 'platform_admin_access',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => ['source' => 'platform_websites'],
        ]);

        session()->put('current_site_id', $site->id);
        $this->redirectRoute('admin.site.dashboard');
    }

    public function render(): mixed
    {
        abort_unless(auth()->user()?->isAdministrator(), 403);

        $sites = Site::query()
            ->with('owner')
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($query): void {
                    $query->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('slug', 'like', '%'.$this->search.'%')
                        ->orWhereHas('owner', function ($owner): void {
                            $owner->where('name', 'like', '%'.$this->search.'%')
                                ->orWhere('email', 'like', '%'.$this->search.'%');
                        });
                });
            })
            ->when($this->status === 'published', fn ($query) => $query->where('is_published', true)->where('status', 'published'))
            ->when($this->status === 'draft', fn ($query) => $query->where(function ($query): void {
                $query->where('is_published', false)->orWhere('status', 'draft');
            }))
            ->latest()
            ->paginate(15);

        return view('livewire.platform-websites', compact('sites'));
    }
}
