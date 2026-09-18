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

    public string $placement = 'gallery';

    public string $search = '';
    public bool $mediaApplied = false;

    public function mount(Site $site): void
    {
        abort_unless($site->isManageableBy(auth()->user()), 403);
        $this->site = $site;
        $this->mediaApplied = filled(data_get($site->settings, 'media_applied_at'));
    }

    public function uploadMedia(): void
    {
        $this->placement = in_array($this->placement, ['logo','hero','about','experience','education','skills','projects','services','testimonials','gallery','contact','background','footer'], true)
            ? $this->placement
            : 'gallery';

        $this->validate(
            [
                'upload' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,gif,svg', 'max:10240'],
                'altText' => ['nullable', 'string', 'max:255'],
                'placement' => ['required', 'string', 'in:logo,hero,about,experience,education,skills,projects,services,testimonials,gallery,contact,background,footer'],
            ],
            [
                'upload.required' => 'Seleciona primeiro uma imagem do teu computador.',
                'upload.file' => 'O ficheiro selecionado não é válido.',
                'upload.mimes' => 'Formato não suportado. Usa JPG, PNG, WEBP, GIF ou SVG.',
                'upload.max' => 'A imagem não pode ultrapassar 10 MB.',
            ],
        );

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
            'placement' => $this->placement,
            'width' => $image[0] ?? null,
            'height' => $image[1] ?? null,
        ]);

        $this->reset(['upload', 'altText']);
        $this->mediaApplied = false;
        session()->flash('status', 'Ficheiro guardado na biblioteca de media. Agora podes aplicar as alterações no site.');
    }

    public function mediaUrl(SiteMedia $media): string
    {
        return route('admin.site.media.file', ['site' => $this->site, 'media' => $media->id]);
    }

    public function placementOptions(): array
    {
        return [
            'logo' => 'Logótipo', 'hero' => 'Capa / Hero principal', 'about' => 'Sobre mim / Sobre nós',
            'experience' => 'Experiência', 'education' => 'Formação', 'skills' => 'Competências',
            'projects' => 'Projetos', 'services' => 'Serviços', 'testimonials' => 'Testemunhos',
            'gallery' => 'Galeria', 'contact' => 'Contacto', 'background' => 'Fundo de uma secção', 'footer' => 'Rodapé',
        ];
    }

    public function placementLabel(?string $placement): string
    {
        return [
            'logo' => 'Logótipo', 'hero' => 'Capa / Hero', 'about' => 'Sobre mim / Sobre nós',
            'experience' => 'Experiência', 'education' => 'Formação', 'skills' => 'Competências',
            'projects' => 'Projetos', 'services' => 'Serviços', 'testimonials' => 'Testemunhos',
            'gallery' => 'Galeria', 'contact' => 'Contacto', 'background' => 'Fundo de secção', 'footer' => 'Rodapé',
        ][$placement ?: 'gallery'] ?? 'Galeria';
    }

    public function applyMedia(): void
    {
        $settings = $this->site->settings ?? [];
        data_set($settings, 'media_applied_at', now()->toIso8601String());
        $this->site->update(['settings' => $settings]);
        $this->site->refresh();
        $this->mediaApplied = true;
        session()->flash('media-applied', 'As imagens da biblioteca foram aplicadas no site.');
    }

    public function deleteMedia(int $mediaId): void
    {
        $media = $this->site->media()->findOrFail($mediaId);
        Storage::disk($media->disk ?: 'public')->delete($media->path);
        $media->delete();
        $this->mediaApplied = false;
    }

    public function render(): mixed
    {
        $media = $this->site->media()
            ->when($this->search !== '', fn ($query) => $query->where('original_name', 'like', '%'.$this->search.'%'))
            ->latest()
            ->get();

        return view('livewire.media-library', [
            'media' => $media,
            'placementOptions' => $this->placementOptions(),
        ]);
    }
}
