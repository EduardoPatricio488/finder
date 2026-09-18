<?php

namespace App\Livewire;

use App\Models\Site;
use App\Models\SiteSubmission;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class SiteSubmissions extends Component
{
    use WithPagination;

    public Site $site;

    public string $status = 'all';

    public function mount(Site $site): void
    {
        abort_unless($site->isManageableBy(auth()->user()), 403);
        $this->site = $site;
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
