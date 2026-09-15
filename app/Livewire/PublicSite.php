<?php

namespace App\Livewire;

use App\Models\Site;
use App\Models\SiteAnalyticsEvent;
use App\Models\SitePage;
use App\Models\SiteSubmission;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Website')]
class PublicSite extends Component
{
    public Site $site;

    public SitePage $page;

    public bool $preview = false;

    public string $contactName = '';

    public string $contactEmail = '';

    public string $contactMessage = '';

    public string $newsletterEmail = '';

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

    public function submitContact(): void
    {
        $this->ensurePublicSubmissionAllowed('contact');

        $validated = $this->validate([
            'contactName' => ['required', 'string', 'max:120'],
            'contactEmail' => ['required', 'email:rfc', 'max:255'],
            'contactMessage' => ['required', 'string', 'max:5000'],
        ]);

        SiteSubmission::create([
            'site_id' => $this->site->id,
            'form_type' => 'contact',
            'name' => $validated['contactName'],
            'email' => $validated['contactEmail'],
            'message' => $validated['contactMessage'],
            'status' => 'new',
        ]);

        $this->reset(['contactName', 'contactEmail', 'contactMessage']);
        session()->flash('site_form_success', 'A tua mensagem foi enviada com sucesso.');
    }

    public function subscribeNewsletter(): void
    {
        $this->ensurePublicSubmissionAllowed('newsletter');

        $validated = $this->validate([
            'newsletterEmail' => ['required', 'email:rfc', 'max:255'],
        ]);

        SiteSubmission::create([
            'site_id' => $this->site->id,
            'form_type' => 'newsletter',
            'email' => $validated['newsletterEmail'],
            'payload' => ['source' => 'newsletter'],
            'status' => 'new',
        ]);

        $this->reset('newsletterEmail');
        session()->flash('site_newsletter_success', 'Subscrição concluída com sucesso.');
    }

    private function ensurePublicSubmissionAllowed(string $type): void
    {
        abort_if($this->preview, 404);

        $key = sprintf('site-submission:%s:%s:%s', $this->site->id, $type, request()->ip());
        abort_if(RateLimiter::tooManyAttempts($key, 5), 429);
        RateLimiter::hit($key, 60);
    }

    private function deviceType(?string $userAgent): string
    {
        if (! $userAgent) {
            return 'unknown';
        }

        if (preg_match('/tablet|ipad/i', $userAgent)) {
            return 'tablet';
        }

        if (preg_match('/mobile|android|iphone/i', $userAgent)) {
            return 'mobile';
        }

        return 'desktop';
    }

    public function render(): mixed
    {
        return view('livewire.public-site-renderer')
            ->layout('layouts.public-site', [
                'site' => $this->site,
                'page' => $this->page,
                'preview' => $this->preview,
            ]);
    }
}
