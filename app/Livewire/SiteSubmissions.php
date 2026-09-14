<?php

namespace App\Livewire;

use App\Livewire\Concerns\InteractsWithSiteContext;
use App\Models\SiteSubmission;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class SiteSubmissions extends Component
{
    use InteractsWithSiteContext;
    use WithPagination;

    public string $status = 'all';

    public function mount(): void
    {
        $this->resolveSiteContext();
    }

    public function markRead(int $submissionId): void
    {
        $submission = SiteSubmission::query()
            ->where('site_id', $this->site->id)
            ->findOrFail($submissionId);

        $submission->update(['status' => 'read']);
    }

    public function render()
    {
        $query = SiteSubmission::query()
            ->where('site_id', $this->site->id)
            ->latest();

        if ($this->status !== 'all') {
            $query->where('status', $this->status);
        }

        return view('livewire.site-submissions', [
            'submissions' => $query->paginate(20),
        ]);
    }
}
