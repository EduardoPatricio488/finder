<?php

namespace App\Livewire;

use App\Models\Site;
use App\Models\SitePage;
use App\Models\SiteSection;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.builder')]
#[Title('Editor')]
class BuilderEditor extends Component
{
    public Site $site;
    public ?int $pageId = null;
    public string $pageName = '';
    public string $pageSlug = '';
    public string $pageStatus = 'draft';
    public array $sections = [];
    public array $theme = [];
    public bool $dirty = false;

    public function mount(Site $site): void
    {
        abort_unless($site->isManageableBy(auth()->user()), 403);
        $this->site = $site->load('pages.sections');
        $this->theme = $site->theme ?: $this->defaultTheme();
        $page = $this->site->pages->firstWhere('is_homepage', true) ?? $this->site->pages->first();
        if (! $page) { $page = $this->createDefaultPage(); $this->site->load('pages.sections'); }
        $this->selectPage($page->id);
    }

    public function selectPage(int $pageId): void
    {
        $page = $this->site->pages()->with('sections')->findOrFail($pageId);
        $this->pageId = $page->id;
        $this->pageName = $page->name;
        $this->pageSlug = $page->slug;
        $this->pageStatus = $page->status;
        $this->sections = $page->sections->map(fn (SiteSection $section) => ['id'=>$section->id,'type'=>$section->type,'label'=>$section->label,'content'=>$section->content ?? [],'settings'=>$section->settings ?? [],'is_visible'=>$section->is_visible])->values()->all();
        $this->dirty = false;
    }

    public function createPage(): void
    {
        $base = 'nova-pagina';
        $slug = $base;
        $counter = 2;
        while ($this->site->pages()->withTrashed()->where('slug', $slug)->exists()) $slug = $base.'-'.$counter++;
        $page = $this->site->pages()->create(['name'=>'Nova página','slug'=>$slug,'status'=>'draft','is_homepage'=>false,'sort_order'=>$this->site->pages()->max('sort_order') + 1]);
        $page->sections()->create(['type'=>'text','label'=>'Conteúdo','content'=>['title'=>'Nova página','body'=>'Começa a editar o conteúdo desta página.'],'settings'=>['background'=>'transparent','padding'=>'lg'],'sort_order'=>0]);
        $this->site->load('pages.sections');
        $this->selectPage($page->id);
    }

    public function duplicatePage(): void
    {
        $source = $this->site->pages()->with('sections')->findOrFail($this->pageId);
        $slug = $source->slug.'-copia'; $counter = 2;
        while ($this->site->pages()->withTrashed()->where('slug', $slug)->exists()) $slug = $source->slug.'-copia-'.$counter++;
        $page = $this->site->pages()->create(['name'=>$source->name.' (cópia)','slug'=>$slug,'status'=>'draft','is_homepage'=>false,'seo'=>$source->seo,'sort_order'=>$source->sort_order + 1]);
        foreach ($source->sections as $section) $page->sections()->create(['type'=>$section->type,'label'=>$section->label,'content'=>$section->content,'settings'=>$section->settings,'sort_order'=>$section->sort_order,'is_visible'=>$section->is_visible]);
        $this->site->load('pages.sections');
        $this->selectPage($page->id);
    }

    public function deletePage(): void
    {
        $page = $this->site->pages()->findOrFail($this->pageId);
        abort_if($page->is_homepage, 422, 'A homepage não pode ser eliminada.');
        $page->delete();
        $fallback = $this->site->pages()->firstOrFail();
        $this->site->load('pages.sections');
        $this->selectPage($fallback->id);
    }

    public function addSection(string $type): void
    {
        $allowed = ['hero','text','image','button','feature_grid','card','testimonials','faq','gallery','contact_form','product_grid','video','newsletter','cta'];
        abort_unless(in_array($type, $allowed, true), 422);
        $this->sections[] = ['id'=>null,'type'=>$type,'label'=>Str::headline($type),'content'=>$this->defaultContent($type),'settings'=>['background'=>'transparent','padding'=>'lg'],'is_visible'=>true];
        $this->dirty = true;
    }

    public function removeSection(int $index): void { unset($this->sections[$index]); $this->sections = array_values($this->sections); $this->dirty = true; }

    public function moveSection(int $index, string $direction): void
    {
        $target = $direction === 'up' ? $index - 1 : $index + 1;
        if ($target < 0 || $target >= count($this->sections)) return;
        [$this->sections[$index],$this->sections[$target]] = [$this->sections[$target],$this->sections[$index]];
        $this->dirty = true;
    }

    public function save(): void
    {
        $page = $this->site->pages()->findOrFail($this->pageId);
        $page->update(['name'=>$this->pageName,'slug'=>Str::slug($this->pageSlug ?: $this->pageName),'status'=>$this->pageStatus]);
        $keepIds = [];
        foreach ($this->sections as $index => $data) {
            $section = ! empty($data['id']) ? $page->sections()->findOrFail($data['id']) : new SiteSection(['site_page_id'=>$page->id]);
            $section->fill(['type'=>$data['type'],'label'=>$data['label'] ?? Str::headline($data['type']),'content'=>$data['content'] ?? [],'settings'=>$data['settings'] ?? [],'sort_order'=>$index,'is_visible'=>(bool)($data['is_visible'] ?? true)]);
            $section->save(); $keepIds[] = $section->id; $this->sections[$index]['id'] = $section->id;
        }
        $query = $page->sections(); if ($keepIds) $query->whereNotIn('id',$keepIds); $query->delete();
        $this->site->update(['theme'=>$this->theme]);
        $this->dirty = false; $this->dispatch('builder-saved');
    }

    public function publish(): void
    {
        $this->save();
        $this->site->update(['is_published'=>true,'status'=>'published','published_at'=>now()]);
        $this->site->refresh(); $this->dispatch('builder-published');
    }

    private function createDefaultPage(): SitePage
    {
        $page = $this->site->pages()->create(['name'=>'Home','slug'=>'home','status'=>'draft','is_homepage'=>true,'sort_order'=>0]);
        foreach (['hero','feature_grid','cta'] as $index => $type) $page->sections()->create(['type'=>$type,'label'=>Str::headline($type),'content'=>$this->defaultContent($type),'settings'=>['background'=>'transparent','padding'=>'lg'],'sort_order'=>$index]);
        return $page;
    }

    private function defaultTheme(): array { return ['primary'=>$this->site->primary_color ?: '#635bff','secondary'=>$this->site->secondary_color ?: '#111827','accent'=>'#8b5cf6','background'=>'#ffffff','text'=>'#111827','heading_font'=>'Inter','body_font'=>'Inter','radius'=>'lg','shadow'=>'sm','content_width'=>'1200px']; }

    private function defaultContent(string $type): array
    {
        return match ($type) {
            'hero'=>['title'=>'Cria algo incrível.','subtitle'=>'Personaliza este conteúdo diretamente no Finder.','button_label'=>'Saber mais','button_url'=>'#'],
            'text'=>['title'=>'Uma secção de conteúdo','body'=>'Escreve aqui a mensagem que queres mostrar aos teus visitantes.'],
            'button'=>['label'=>'Começar agora','url'=>'#'],
            'feature_grid'=>['title'=>'Tudo o que precisas','items'=>[['title'=>'Simples','description'=>'Edita sem código.'],['title'=>'Flexível','description'=>'Adapta o teu design.'],['title'=>'Rápido','description'=>'Publica em poucos minutos.']]],
            'cta'=>['title'=>'Pronto para começar?','description'=>'Publica o teu website quando estiveres satisfeito.','button_label'=>'Começar'],
            default=>['title'=>Str::headline($type),'description'=>'Personaliza esta secção.'],
        };
    }

    public function render() { return view('livewire.builder-editor'); }
}
