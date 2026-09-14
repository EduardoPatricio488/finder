<?php

namespace App\Livewire;

use App\Models\Site;
use App\Models\SiteAnalyticsEvent;
use App\Models\SitePage;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.public-site')]
#[Title('Website')]
class PublicSite extends Component
{
    public Site $site;

    public SitePage $page;

    public bool $preview = false;

    public function mount(Site $site, ?string $pageSlug = null): void
    {
        $this->preview = request()->boolean('preview');

        if ($this->preview) {
            abort_unless($site->isManageableBy(auth()->user()), 403);
        } else {
            abort_unless($site->is_published && $site->status === 'published', 404);
        }

        $this->site = $site->load(['menus.items.children.page', 'pages.sections', 'products']);
        $this->page = $pageSlug
            ? $this->site->pages()->where('slug', $pageSlug)->when(! $this->preview, fn ($query) => $query->where('status', 'published'))->firstOrFail()
            : ($this->site->pages()->where('is_homepage', true)->when(! $this->preview, fn ($query) => $query->where('status', 'published'))->first()
                ?? $this->site->pages()->when(! $this->preview, fn ($query) => $query->where('status', 'published'))->orderBy('sort_order')->firstOrFail());

        if (! $this->preview) {
            SiteAnalyticsEvent::create([
                'site_id' => $this->site->id,
                'page_id' => $this->page->id,
                'event_type' => 'page_view',
                'path' => request()->path(),
                'referrer' => Str::limit((string) request()->headers->get('referer'), 500, ''),
                'device_type' => $this->deviceType(request()->userAgent()),
                'session_hash' => hash('sha256', (string) request()->session()->getId()),
                'occurred_at' => now(),
            ]);
        }
    }

    private function deviceType(?string $userAgent): string
    {
        if (! $userAgent) {
            return 'unknown';
        }

        if (preg_match('/mobile|android|iphone/i', $userAgent)) {
            return 'mobile';
        }

        if (preg_match('/tablet|ipad/i', $userAgent)) {
            return 'tablet';
        }

        return 'desktop';
    }

    public function render()
    {
        return view('livewire.public-site');
    }
}
