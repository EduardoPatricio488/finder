<?php

namespace App\Livewire;

use App\Models\Site;
use App\Models\SitePage;
use App\Models\SiteSection;
use App\Support\SiteContext;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Editor da página')]
class SitePageEditor extends Component
{
    public Site $site;
    public SitePage $page;
    public string $name = '';
    public string $slug = '';
    public string $status = 'draft';
    public array $sections = [];

    public function mount(SitePage $page): void
    {
        $site = SiteContext::current();
        abort_unless($site instanceof Site && $site->isManageableBy(auth()->user()), 403);
        abort_unless((int) $page->site_id === (int) $site->id, 404);

        $this->site = $site;
        $this->page = $page;
        $this->name = $page->name;
        $this->slug = $page->slug;
        $this->status = $page->status;
        $this->loadSections();
    }

    private function loadSections(): void
    {
        $this->sections = $this->page->sections()->get()->map(fn (SiteSection $section) => [
            'id' => $section->id,
            'type' => $section->type ?: 'text',
            'label' => $section->label ?? '',
            'title' => (string) data_get($section->content, 'title', ''),
            'body' => (string) data_get($section->content, 'body', data_get($section->content, 'description', '')),
            'button_label' => (string) data_get($section->content, 'button_label', ''),
            'button_url' => (string) data_get($section->content, 'button_url', ''),
            'image_url' => (string) data_get($section->content, 'image_url', ''),
            'items_text' => collect(data_get($section->content, 'items', []))->map(fn ($item) => is_array($item) ? (string) ($item['title'] ?? $item['question'] ?? '') : (string) $item)->filter()->implode("\n"),
            'is_visible' => (bool) $section->is_visible,
        ])->all();
    }

    public function addSection(string $type = 'text'): void
    {
        $allowed = ['hero', 'text', 'features', 'image', 'gallery', 'testimonials', 'faq', 'cta', 'widget'];
        abort_unless(in_array($type, $allowed, true), 422);

        $this->sections[] = [
            'id' => null,
            'type' => $type,
            'label' => match ($type) {
                'hero' => 'Destaque principal',
                'features' => 'Funcionalidades',
                'gallery' => 'Galeria',
                'testimonials' => 'Testemunhos',
                'faq' => 'Perguntas frequentes',
                'cta' => 'Chamada à ação',
                'image' => 'Imagem',
                'widget' => 'Widget',
                default => 'Nova secção',
            },
            'title' => '',
            'body' => '',
            'button_label' => '',
            'button_url' => '',
            'image_url' => '',
            'items_text' => '',
            'is_visible' => true,
        ];
    }

    public function removeSection(int $index): void
    {
        abort_unless(isset($this->sections[$index]), 404);
        $section = $this->sections[$index];
        if (! empty($section['id'])) {
            $this->page->sections()->whereKey($section['id'])->delete();
        }
        array_splice($this->sections, $index, 1);
        session()->flash('status', 'Secção removida.');
    }

    public function save(): void
    {
        $this->name = trim($this->name);
        $this->slug = Str::slug($this->slug);

        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:120', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'unique:site_pages,slug,'.$this->page->id.',id,site_id,'.$this->site->id],
            'status' => ['required', 'in:draft,published'],
            'sections' => ['array', 'max:30'],
            'sections.*.type' => ['required', 'in:hero,text,features,image,gallery,testimonials,faq,cta,widget'],
            'sections.*.label' => ['nullable', 'string', 'max:120'],
            'sections.*.title' => ['nullable', 'string', 'max:255'],
            'sections.*.body' => ['nullable', 'string', 'max:10000'],
            'sections.*.button_label' => ['nullable', 'string', 'max:100'],
            'sections.*.button_url' => ['nullable', 'string', 'max:2048'],
            'sections.*.image_url' => ['nullable', 'url', 'max:2048'],
            'sections.*.items_text' => ['nullable', 'string', 'max:10000'],
            'sections.*.is_visible' => ['boolean'],
        ]);

        $this->page->update([
            'name' => $this->name,
            'slug' => $this->slug,
            'status' => $this->status,
        ]);

        foreach ($this->sections as $index => $sectionData) {
            $items = collect(preg_split('/\r?\n/', (string) ($sectionData['items_text'] ?? '')))
                ->map(fn ($item) => trim($item))
                ->filter()
                ->map(fn ($item) => ['title' => $item])
                ->values()
                ->all();

            $content = [
                'title' => trim((string) ($sectionData['title'] ?? '')),
                'body' => trim((string) ($sectionData['body'] ?? '')),
                'description' => trim((string) ($sectionData['body'] ?? '')),
                'button_label' => trim((string) ($sectionData['button_label'] ?? '')),
                'button_url' => trim((string) ($sectionData['button_url'] ?? '')),
                'image_url' => trim((string) ($sectionData['image_url'] ?? '')),
                'items' => $items,
            ];

            $attributes = [
                'type' => $sectionData['type'],
                'label' => trim((string) ($sectionData['label'] ?? '')) ?: null,
                'content' => $content,
                'settings' => [],
                'sort_order' => $index,
                'is_visible' => (bool) ($sectionData['is_visible'] ?? true),
            ];

            if (! empty($sectionData['id'])) {
                $this->page->sections()->whereKey($sectionData['id'])->update($attributes);
            } else {
                $this->page->sections()->create($attributes);
            }
        }

        $this->page->refresh();
        $this->loadSections();
        session()->flash('status', 'Página e conteúdo guardados com sucesso.');
    }

    public function render(): mixed
    {
        return view('livewire.site-page-editor', [
            'pageTypes' => [
                'hero' => 'Destaque principal',
                'text' => 'Texto',
                'features' => 'Funcionalidades / lista',
                'image' => 'Imagem',
                'gallery' => 'Galeria',
                'testimonials' => 'Testemunhos',
                'faq' => 'Perguntas frequentes',
                'cta' => 'Botão / chamada à ação',
                'widget' => 'Widget / bloco personalizado',
            ],
        ]);
    }
}
