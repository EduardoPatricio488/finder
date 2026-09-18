<?php

namespace App\Livewire;

use App\Support\SiteContext;

use App\Models\Site;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
#[Title('Definições do website')]
class SiteDefinitions extends Component
{
    use WithFileUploads;

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
    public $logoUpload = null;
    public $faviconUpload = null;
    public $ogImageUpload = null;
    public string $contactName = '';
    public function mount(): void
    {
        $site = SiteContext::current();
        abort_unless($site instanceof Site && $site->isManageableBy(auth()->user()), 403);

        $this->site = $site;
        $this->availableSites = SiteContext::manageableSitesQuery(auth()->user())->orderBy('sort_order')->orderBy('name')->get(['id', 'name', 'slug', 'status']);
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
            'logoUpload' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif,svg', 'max:10240'],
            'faviconUpload' => ['nullable', 'file', 'mimes:ico,png,jpg,jpeg,webp,svg', 'max:5120'],
            'ogImageUpload' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif,svg', 'max:10240'],
            'seoTitle' => ['nullable', 'string', 'max:255'],
            'seoDescription' => ['nullable', 'string', 'max:500'],
            'robotsIndex' => ['boolean'],
        ]);

        $settings = $this->site->settings ?? [];
        $seo = $this->site->seo ?? [];

        if ($this->logoUpload) {
            $oldLogo = $this->logoUrl;
            $path = $this->logoUpload->store('sites/'.$this->site->id.'/branding', 'public');
            $this->logoUrl = Storage::disk('public')->url($path);
            $settings['logo_url'] = $this->logoUrl;
            $this->deleteStoredPublicFile($oldLogo);
        }

        if ($this->faviconUpload) {
            $oldFavicon = $this->faviconUrl;
            $path = $this->faviconUpload->store('sites/'.$this->site->id.'/branding', 'public');
            $this->faviconUrl = Storage::disk('public')->url($path);
            $settings['favicon_url'] = $this->faviconUrl;
            $this->deleteStoredPublicFile($oldFavicon);
        }

        if ($this->ogImageUpload) {
            $oldOgImage = $this->ogImage;
            $path = $this->ogImageUpload->store('sites/'.$this->site->id.'/branding', 'public');
            $this->ogImage = Storage::disk('public')->url($path);
            $seo['og_image'] = $this->ogImage;
            $this->deleteStoredPublicFile($oldOgImage);
        }

        $this->site->update([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'tagline' => $validated['tagline'],
            'settings' => array_merge($settings, [
                'logo_url' => $this->logoUrl,
                'favicon_url' => $this->faviconUrl,
            ]),
            'seo' => array_merge($seo, [
                'title' => $validated['seoTitle'] ?: $validated['name'],
                'description' => $validated['seoDescription'],
                'og_image' => $this->ogImage,
                'robots_index' => $validated['robotsIndex'],
            ]),
        ]);

        $this->site->refresh();
        $this->reset(['logoUpload', 'faviconUpload', 'ogImageUpload']);
        session()->flash('status', 'Definições do website guardadas.');
    }

    private function deleteStoredPublicFile(string $url): void
    {
        if ($url === '') return;

        $path = parse_url($url, PHP_URL_PATH);
        if (!is_string($path) || !Str::startsWith($path, '/storage/')) return;

        $storagePath = ltrim(Str::after($path, '/storage/'), '/');
        if ($storagePath !== '') Storage::disk('public')->delete($storagePath);
    }

    public function render(): mixed
    {
        return view('livewire.site-definitions');
    }
}
