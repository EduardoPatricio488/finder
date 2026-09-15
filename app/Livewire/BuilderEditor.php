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
    public array $history = [];
    public array $future = [];

    public function mount(Site $site): void
    {
        abort_unless($site->isManageableBy(auth()->user()), 403);
        $this->site = $site->load('pages.sections');
        $this->theme = $site->theme ?: $this->defaultTheme();
        $page = $this->site->pages->firstWhere('is_homepage', true) ?? $this->site->pages->first();
        if (! $page) {
            $page = $this->createDefaultPage();
            $this->site->load('pages.sections');
        }
        $this->selectPage($page->id);
    }

    public function getCanUndoProperty(): bool { return count($this->history) > 0; }
    public function getCanRedoProperty(): bool { return count($this->future) > 0; }

    public function updated(string $property): void
    {
        if (str_starts_with($property, 'sections.') || str_starts_with($property, 'theme.') || in_array($property, ['pageName', 'pageSlug', 'pageStatus'], true)) {
            $this->dirty = true;
        }
    }

    public function selectPage(int $pageId): void
    {
        $page = $this->site->pages()->with('sections')->findOrFail($pageId);
        $this->pageId=$page->id; $this->pageName=$page->name; $this->pageSlug=$page->slug; $this->pageStatus=$page->status;
        $this->sections=$page->sections->map(fn (SiteSection $section)=>['id'=>$section->id,'type'=>$section->type,'label'=>$section->label,'content'=>$section->content??[],'settings'=>$section->settings??[],'is_visible'=>$section->is_visible])->values()->all();
        $this->history=[]; $this->future=[]; $this->dirty=false;
    }

    public function createPage(): void
    {
        $this->checkpoint(); $base='nova-pagina'; $slug=$base; $counter=2;
        while($this->site->pages()->withTrashed()->where('slug',$slug)->exists()) $slug=$base.'-'.$counter++;
        $page=$this->site->pages()->create(['name'=>'Nova página','slug'=>$slug,'status'=>'draft','is_homepage'=>false,'sort_order'=>$this->site->pages()->max('sort_order')+1]);
        $page->sections()->create(['type'=>'text','label'=>'Conteúdo','content'=>['title'=>'Nova página','body'=>'Começa a editar o conteúdo desta página.'],'settings'=>['background'=>'transparent','padding'=>'lg'],'sort_order'=>0]);
        $this->site->load('pages.sections'); $this->selectPage($page->id);
    }

    public function duplicatePage(): void
    {
        $this->checkpoint(); $source=$this->site->pages()->with('sections')->findOrFail($this->pageId); $slug=$source->slug.'-copia'; $counter=2;
        while($this->site->pages()->withTrashed()->where('slug',$slug)->exists()) $slug=$source->slug.'-copia-'.$counter++;
        $page=$this->site->pages()->create(['name'=>$source->name.' (cópia)','slug'=>$slug,'status'=>'draft','is_homepage'=>false,'seo'=>$source->seo,'sort_order'=>$source->sort_order+1]);
        foreach($source->sections as $section) $page->sections()->create(['type'=>$section->type,'label'=>$section->label,'content'=>$section->content,'settings'=>$section->settings,'sort_order'=>$section->sort_order,'is_visible'=>$section->is_visible]);
        $this->site->load('pages.sections'); $this->selectPage($page->id);
    }

    public function deletePage(): void
    {
        $this->checkpoint(); $page=$this->site->pages()->findOrFail($this->pageId); abort_if($page->is_homepage,422,'A homepage não pode ser eliminada.'); $page->delete();
        $fallback=$this->site->pages()->firstOrFail(); $this->site->load('pages.sections'); $this->selectPage($fallback->id);
    }

    public function addSection(string $type): void
    {
        $allowed=['hero','text','image','button','feature_grid','card','testimonials','faq','gallery','contact_form','product_grid','product_card','pricing','blog_posts','social_links','video','map','newsletter','cta']; abort_unless(in_array($type,$allowed,true),422);
        $this->checkpoint(); $this->sections[]=['id'=>null,'type'=>$type,'label'=>Str::headline($type),'content'=>$this->defaultContent($type),'settings'=>['background'=>'transparent','padding'=>'lg','align'=>'left'],'is_visible'=>true]; $this->dirty=true;
    }

    public function duplicateSection(int $index): void
    {
        abort_unless(isset($this->sections[$index]),404); $this->checkpoint(); $copy=$this->sections[$index]; $copy['id']=null; $copy['label']=($copy['label']??Str::headline($copy['type'])).' (cópia)'; array_splice($this->sections,$index+1,0,[$copy]); $this->dirty=true;
    }

    public function addItem(int $sectionIndex): void
    {
        abort_unless(isset($this->sections[$sectionIndex]),404); $type=$this->sections[$sectionIndex]['type'];
        $templates=['feature_grid'=>['title'=>'Novo benefício','description'=>'Descreve este benefício.'],'testimonials'=>['name'=>'Cliente','role'=>'Função','quote'=>'Escreve aqui o testemunho.','image'=>''],'faq'=>['question'=>'Nova pergunta?','answer'=>'Escreve a resposta.'],'gallery'=>['url'=>'','alt'=>'','caption'=>''],'pricing'=>['name'=>'Novo plano','price'=>'49 €','description'=>'Descrição do plano.','features'=>['Funcionalidade 1','Funcionalidade 2'],'button_label'=>'Escolher','button_url'=>'#'],'blog_posts'=>['title'=>'Novo artigo','excerpt'=>'Resumo do artigo.','url'=>'#','image'=>''],'social_links'=>['label'=>'Nova rede','url'=>'#']];
        abort_unless(isset($templates[$type]),422); $this->checkpoint(); $this->sections[$sectionIndex]['content']['items'][]=$templates[$type]; $this->dirty=true;
    }

    public function removeItem(int $sectionIndex,int $itemIndex): void
    {
        abort_unless(isset($this->sections[$sectionIndex]['content']['items'][$itemIndex]),404); $this->checkpoint(); array_splice($this->sections[$sectionIndex]['content']['items'],$itemIndex,1); $this->dirty=true;
    }

    public function moveItem(int $sectionIndex,int $itemIndex,string $direction): void
    {
        abort_unless(in_array($direction,['up','down'],true),422); $items=$this->sections[$sectionIndex]['content']['items']??[]; $target=$direction==='up'?$itemIndex-1:$itemIndex+1;
        if(!isset($items[$itemIndex])||$target<0||$target>=count($items)) return; $this->checkpoint(); [$items[$itemIndex],$items[$target]]=[$items[$target],$items[$itemIndex]]; $this->sections[$sectionIndex]['content']['items']=$items; $this->dirty=true;
    }

    public function removeSection(int $index): void
    {
        abort_unless(isset($this->sections[$index]),404); $this->checkpoint(); unset($this->sections[$index]); $this->sections=array_values($this->sections); $this->dirty=true;
    }

    public function moveSection(int $index,string $direction): void
    {
        abort_unless(in_array($direction,['up','down'],true),422); $target=$direction==='up'?$index-1:$index+1; if($target<0||$target>=count($this->sections)) return; $this->checkpoint(); [$this->sections[$index],$this->sections[$target]]=[$this->sections[$target],$this->sections[$index]]; $this->dirty=true;
    }

    public function undo(): void { if(!$this->history)return; $this->future[]=$this->currentSnapshot(); $this->restoreSnapshot(array_pop($this->history)); }
    public function redo(): void { if(!$this->future)return; $this->history[]=$this->currentSnapshot(); $this->restoreSnapshot(array_pop($this->future)); }

    public function save(): void
    {
        $page=$this->site->pages()->findOrFail($this->pageId); $slug=Str::slug($this->pageSlug?:$this->pageName);
        $page->update(['name'=>trim($this->pageName)?:'Sem título','slug'=>$slug?:'pagina','status'=>in_array($this->pageStatus,['draft','published'],true)?$this->pageStatus:'draft']); $keepIds=[];
        foreach($this->sections as $index=>$data){$section=!empty($data['id'])?$page->sections()->findOrFail($data['id']):new SiteSection(['site_page_id'=>$page->id]); $section->fill(['type'=>$data['type'],'label'=>$data['label']??Str::headline($data['type']),'content'=>$data['content']??[],'settings'=>$data['settings']??[],'sort_order'=>$index,'is_visible'=>(bool)($data['is_visible']??true)]); $section->save(); $keepIds[]=$section->id; $this->sections[$index]['id']=$section->id;}
        $query=$page->sections(); if($keepIds)$query->whereNotIn('id',$keepIds); $query->delete(); $this->site->update(['theme'=>$this->theme]); $this->dirty=false; $this->dispatch('builder-saved');
    }

    public function publish(): void { $this->save(); $this->site->update(['is_published'=>true,'status'=>'published','published_at'=>now()]); $this->site->refresh(); $this->dispatch('builder-published'); }
    public function unpublish(): void { $this->save(); $this->site->update(['is_published'=>false,'status'=>'draft','published_at'=>null]); $this->site->refresh(); $this->dispatch('builder-unpublished'); }

    private function checkpoint(): void { $this->history[]=$this->currentSnapshot(); if(count($this->history)>30)array_shift($this->history); $this->future=[]; }
    private function currentSnapshot(): array { return ['sections'=>$this->sections,'pageName'=>$this->pageName,'pageSlug'=>$this->pageSlug,'pageStatus'=>$this->pageStatus,'theme'=>$this->theme]; }
    private function restoreSnapshot(array $snapshot): void { $this->sections=$snapshot['sections'];$this->pageName=$snapshot['pageName'];$this->pageSlug=$snapshot['pageSlug'];$this->pageStatus=$snapshot['pageStatus'];$this->theme=$snapshot['theme'];$this->dirty=true; }

    private function createDefaultPage(): SitePage
    {
        $page=$this->site->pages()->create(['name'=>'Home','slug'=>'home','status'=>'draft','is_homepage'=>true,'sort_order'=>0]);
        foreach(['hero','feature_grid','cta'] as $index=>$type)$page->sections()->create(['type'=>$type,'label'=>Str::headline($type),'content'=>$this->defaultContent($type),'settings'=>['background'=>'transparent','padding'=>'lg','align'=>'left'],'sort_order'=>$index]); return $page;
    }

    private function defaultTheme(): array { return ['primary'=>$this->site->primary_color?:'#635bff','secondary'=>$this->site->secondary_color?:'#111827','accent'=>'#8b5cf6','background'=>'#ffffff','text'=>'#111827','heading_font'=>'Inter','body_font'=>'Inter','radius'=>'lg','shadow'=>'sm','content_width'=>'1200px']; }

    private function defaultContent(string $type): array
    {
        return match($type){
            'hero'=>['title'=>'Cria algo incrível.','subtitle'=>'Personaliza este conteúdo diretamente no Finder.','button_label'=>'Saber mais','button_url'=>'#'],
            'text'=>['title'=>'Uma secção de conteúdo','body'=>'Escreve aqui a mensagem que queres mostrar aos teus visitantes.'],
            'image'=>['url'=>'','alt'=>'','caption'=>''],
            'button'=>['label'=>'Começar agora','url'=>'#','target'=>'_self'],
            'feature_grid'=>['title'=>'Tudo o que precisas','items'=>[['title'=>'Simples','description'=>'Edita sem código.'],['title'=>'Flexível','description'=>'Adapta o teu design.'],['title'=>'Rápido','description'=>'Publica em poucos minutos.']]],
            'card'=>['title'=>'Título do cartão','description'=>'Descrição do cartão.'],
            'testimonials'=>['title'=>'O que dizem','items'=>[['name'=>'Cliente','role'=>'Cliente','quote'=>'Excelente experiência.','image'=>'']]],
            'faq'=>['title'=>'Perguntas frequentes','items'=>[['question'=>'Como funciona?','answer'=>'Personaliza esta resposta.']]],
            'gallery'=>['items'=>[['url'=>'','alt'=>'','caption'=>'']]],
            'contact_form'=>['title'=>'Contacta-nos','description'=>'Envia-nos uma mensagem.','button_label'=>'Enviar mensagem'],
            'product_grid'=>['title'=>'Produtos','description'=>'Escolhe os teus produtos favoritos.','limit'=>6],
            'product_card'=>['title'=>'Produto','description'=>'Descrição do produto.'],
            'pricing'=>['title'=>'Planos','items'=>[['name'=>'Plano','price'=>'49 €','description'=>'Descrição.','features'=>['Funcionalidade 1','Funcionalidade 2'],'button_label'=>'Escolher','button_url'=>'#']]],
            'blog_posts'=>['title'=>'Artigos','items'=>[['title'=>'Novo artigo','excerpt'=>'Resumo do artigo.','url'=>'#','image'=>'']]],
            'social_links'=>['title'=>'Segue-nos','items'=>[['label'=>'Instagram','url'=>'#']]],
            'video'=>['title'=>'Vídeo','url'=>'','description'=>''],
            'map'=>['address'=>'','embed_url'=>''],
            'newsletter'=>['title'=>'Recebe novidades','description'=>'Subscreve a nossa newsletter.','button_label'=>'Subscrever','placeholder'=>'O teu email'],
            'cta'=>['title'=>'Pronto para começar?','description'=>'Publica o teu website quando estiveres satisfeito.','button_label'=>'Começar','button_url'=>'#'],
            default=>['title'=>Str::headline($type),'description'=>'Personaliza esta secção.'],
        };
    }

    public function render(): mixed { return view('livewire.builder-editor'); }
}
