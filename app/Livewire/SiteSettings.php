<?php

namespace App\Livewire;

use App\Models\Site;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Definições do website')]
class SiteSettings extends Component
{
    public Site $site;

    public string $name = '';

    public string $slug = '';

    public string $tagline = '';

    public string $logoUrl = '';

    public string $faviconUrl = '';

    public string $seoTitle = '';

    public string $seoDescription = '';

    public string $ogImage = '';

    public bool $robotsIndex = true;

    /**
     * Kept as a backwards-compatible Livewire property so stale browser
     * snapshots from an older SiteSettings version cannot trigger a 500.
     * Contact form data belongs to PublicSite and is intentionally not saved here.
     */
    public string $contactName = '';

    public function mount(Site $site): void
    {
        abort_unless($site->isManageableBy(auth()->user()), 403);

        $this->site = $site;
        $this->name = $site->name;
        $this->slug = $site->slug;
        $this->tagline = $site->tagline ?? '';
        $settings = $site->settings ?? [];
        $seo = $site->seo ?? [];
        $this->logoUrl = (string) ($settings['logo_url'] ?? '');
        $this->faviconUrl = (string) ($settings['favicon_url'] ?? '');
        $this->seoTitle = (string) ($seo['title'] ?? $site->name);
        $this->seoDescription = (string) ($seo['description'] ?? $this->tagline);
        $this->ogImage = (string) ($seo['og_image'] ?? '');
        $this->robotsIndex = (bool) ($seo['robots_index'] ?? true);
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:120', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'unique:sites,slug,'.$this->site->id],
            'tagline' => ['nullable', 'string', 'max:500'],
            'logoUrl' => ['nullable', 'url', 'max:2048'],
            'faviconUrl' => ['nullable', 'url', 'max:2048'],
            'seoTitle' => ['nullable', 'string', 'max:255'],
            'seoDescription' => ['nullable', 'string', 'max:500'],
            'ogImage' => ['nullable', 'url', 'max:2048'],
            'robotsIndex' => ['boolean'],
        ]);

        $this->site->update([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'tagline' => $validated['tagline'],
            'settings' => array_merge($this->site->settings ?? [], [
                'logo_url' => $validated['logoUrl'],
                'favicon_url' => $validated['faviconUrl'],
            ]),
            'seo' => array_merge($this->site->seo ?? [], [
                'title' => $validated['seoTitle'] ?: $validated['name'],
                'description' => $validated['seoDescription'],
                'og_image' => $validated['ogImage'],
                'robots_index' => $validated['robotsIndex'],
            ]),
        ]);

        $this->site->refresh();
        session()->flash('status', 'Definições do website guardadas.');
    }

    public function render(): mixed
    {
        return view('livewire.site-settings');
    }
}
