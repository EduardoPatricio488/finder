<?php

namespace App\Livewire;

use App\Models\Site;
use App\Models\SiteMedia;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class MediaLibrary extends Component
{
    use WithFileUploads;

    public Site $site;

    public $upload = null;

    public string $altText = '';

    public string $search = '';

    public function mount(Site $site): void
    {
        abort_unless($site->isManageableBy(auth()->user()), 403);
        $this->site = $site;
    }

    public function uploadMedia(): void
    {
        $this->validate([
            'upload' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,gif,svg', 'max:10240'],
            'altText' => ['nullable', 'string', 'max:255'],
        ]);

        $path = $this->upload->store('sites/'.$this->site->id.'/media', 'public');
        $image = @getimagesize($this->upload->getRealPath());

        SiteMedia::create([
            'site_id' => $this->site->id,
            'uploaded_by' => auth()->id(),
            'disk' => 'public',
            'path' => $path,
            'original_name' => $this->upload->getClientOriginalName(),
            'mime_type' => $this->upload->getMimeType(),
            'size' => $this->upload->getSize(),
            'alt_text' => $this->altText,
            'width' => $image[0] ?? null,
            'height' => $image[1] ?? null,
        ]);

        $this->reset(['upload', 'altText']);
        session()->flash('status', 'Ficheiro adicionado à biblioteca de media.');
    }

    public function deleteMedia(int $mediaId): void
    {
        $media = $this->site->media()->findOrFail($mediaId);
        Storage::disk($media->disk ?: 'public')->delete($media->path);
        $media->delete();
    }

    public function render(): mixed
    {
        $media = $this->site->media()
            ->when($this->search !== '', fn ($query) => $query->where('original_name', 'like', '%'.$this->search.'%'))
            ->latest()
            ->get();

        return view('livewire.media-library', compact('media'));
    }
}
