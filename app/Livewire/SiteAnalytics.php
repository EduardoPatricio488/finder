<?php

namespace App\Livewire;

use App\Models\Site;
use App\Models\SiteAnalyticsEvent;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class SiteAnalytics extends Component
{
    public Site $site;
    public int $days = 30;

    public function mount(Site $site): void
    {
        abort_unless($site->isManageableBy(auth()->user()), 403);
        $this->site = $site;
    }

    public function render(): mixed
    {
        $from = now()->subDays($this->days)->startOfDay();
        $events = SiteAnalyticsEvent::query()->where('site_id', $this->site->id)->where('occurred_at', '>=', $from);
        $views = (clone $events)->where('event_type', 'page_view')->count();
        $uniqueSessions = (clone $events)->where('event_type', 'page_view')->whereNotNull('session_hash')->distinct('session_hash')->count('session_hash');
        $byPage = (clone $events)->where('event_type', 'page_view')->select('page_id', DB::raw('count(*) as total'))->groupBy('page_id')->orderByDesc('total')->with('page')->limit(10)->get();
        $byDevice = (clone $events)->where('event_type', 'page_view')->select('device_type', DB::raw('count(*) as total'))->groupBy('device_type')->orderByDesc('total')->get();
        $daily = (clone $events)->where('event_type', 'page_view')->selectRaw('DATE(occurred_at) as day, count(*) as total')->groupBy('day')->orderBy('day')->get();

        return view('livewire.site-analytics', compact('views', 'uniqueSessions', 'byPage', 'byDevice', 'daily'));
    }
}
