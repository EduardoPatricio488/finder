<?php

namespace App\Livewire;

use App\Models\Site;
use App\Models\SitePage;
use App\Models\SiteSection;
use App\Services\WebsiteAiGenerator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.builder')]
#[Title('Website Studio')]
class BuilderStudio extends Component
{
    public Site $site;
    public ?int $pageId = null;
    public array $pages = [];
    public array $sections = [];
    public array $theme = [];
    public array $siteSettings = [];
    public array $pageSeo = [];
    public ?int $selectedSection = null;
    public string $device = 'desktop';
    public string $panel = 'pages';
    public string $templateCategory = '';
    public string $aiBrief = '';
    public string $statusMessage = 'Guardado';
    public bool $dirty = false;
    public bool $showAi = false;
    public bool $showTemplates = false;
    public bool $showPublish = false;
    public bool $showSettings = false;

    public function mount(Site $site): void
    {
        abort_unless($site->isManageableBy(auth()->user()), 403);
        $this->site = $site;
        $this->theme = $site->theme ?: $this->defaultTheme();
        $this->siteSettings = $site->settings ?: [];
        $page = $site->pages()->orderByDesc('is_homepage')->orderBy('sort_order')->first();
        if (! $page) {
            $page = $this->createPageRecord('Home', 'home', true);
        }
        $this->loadPage($page->id);
    }

    public function loadPage(int $id): void
    {
        $page = $this->site->pages()->with('sections')->findOrFail($id);
        $this->pageId = $page->id;
        $this->sections = $page->sections->map(fn (SiteSection $s) => [
            'id' => $s->id,
            'type' => $s->type,
            'label' => $s->label,
            'content' => $s->content ?: [],
            'settings' => $s->settings ?: [],
            'is_visible' => (bool) $s->is_visible,
        ])->values()->all();
        $this->pageSeo = $page->seo ?: [];
        $this->selectedSection = count($this->sections) ? 0 : null;
        $this->refreshPages();
        $this->dirty = false;
        $this->statusMessage = 'Guardado';
    }

    public function refreshPages(): void
    {
        $this->pages = $this->site->pages()->orderBy('sort_order')->get()->map(fn (SitePage $p) => [
            'id' => $p->id,
            'name' => $p->name,
            'slug' => $p->slug,
            'is_homepage' => (bool) $p->is_homepage,
            'status' => $p->status,
        ])->all();
    }

    public function updated(string $property): void
    {
        if (str_starts_with($property, 'sections.') || str_starts_with($property, 'theme.') || str_starts_with($property, 'pageSeo.') || str_starts_with($property, 'siteSettings.')) {
            $this->dirty = true;
            $this->statusMessage = 'Alterações por guardar';
        }
    }

    public function save(): void
    {
        $page = $this->site->pages()->findOrFail($this->pageId);
        DB::transaction(function () use ($page): void {
            $this->site->update(['theme' => $this->theme, 'settings' => $this->siteSettings]);
            $page->update(['seo' => $this->pageSeo]);
            $existingIds = [];
            foreach ($this->sections as $index => $data) {
                $payload = [
                    'type' => $data['type'],
                    'label' => Str::limit((string) ($data['label'] ?? Str::headline($data['type'])), 120, ''),
                    'content' => is_array($data['content'] ?? null) ? $data['content'] : [],
                    'settings' => is_array($data['settings'] ?? null) ? $data['settings'] : [],
                    'sort_order' => $index,
                    'is_visible' => (bool) ($data['is_visible'] ?? true),
                ];
                if (! empty($data['id'])) {
                    $section = $page->sections()->findOrFail((int) $data['id']);
                    $section->update($payload);
                    $existingIds[] = $section->id;
                } else {
                    $section = $page->sections()->create($payload);
                    $existingIds[] = $section->id;
                    $this->sections[$index]['id'] = $section->id;
                }
            }
            $page->sections()->whereNotIn('id', $existingIds ?: [0])->delete();
        });
        $this->dirty = false;
        $this->statusMessage = 'Guardado agora';
        $this->refreshPages();
    }

    public function addSection(string $type): void
    {
        $allowed = ['hero','text','image','button','feature_grid','card','testimonials','faq','gallery','contact_form','product_grid','product_card','pricing','blog_posts','social_links','video','map','newsletter','cta'];
        abort_unless(in_array($type, $allowed, true), 422);
        $this->sections[] = [
            'id' => null,
            'type' => $type,
            'label' => Str::headline($type),
            'content' => $this->defaultContent($type),
            'settings' => ['background' => 'transparent', 'padding' => 'lg', 'align' => 'left'],
            'is_visible' => true,
        ];
        $this->selectedSection = count($this->sections) - 1;
        $this->dirty = true;
        $this->panel = 'elements';
    }

    public function duplicateSection(int $index): void
    {
        abort_unless(isset($this->sections[$index]), 404);
        $copy = $this->sections[$index];
        $copy['id'] = null;
        $copy['label'] = ($copy['label'] ?: 'Secção').' (cópia)';
        array_splice($this->sections, $index + 1, 0, [$copy]);
        $this->selectedSection = $index + 1;
        $this->dirty = true;
    }

    public function deleteSection(int $index): void
    {
        abort_unless(isset($this->sections[$index]), 404);
        array_splice($this->sections, $index, 1);
        $this->selectedSection = $this->sections ? min($index, count($this->sections) - 1) : null;
        $this->dirty = true;
    }

    public function moveSection(int $index, string $direction): void
    {
        abort_unless(in_array($direction, ['up', 'down'], true), 422);
        $target = $direction === 'up' ? $index - 1 : $index + 1;
        if (! isset($this->sections[$index], $this->sections[$target])) return;
        [$this->sections[$index], $this->sections[$target]] = [$this->sections[$target], $this->sections[$index]];
        $this->selectedSection = $target;
        $this->dirty = true;
    }

    public function toggleSection(int $index): void
    {
        abort_unless(isset($this->sections[$index]), 404);
        $this->sections[$index]['is_visible'] = ! (bool) $this->sections[$index]['is_visible'];
        $this->dirty = true;
    }

    public function createPage(string $name = 'Nova página'): void
    {
        $slug = Str::slug($name) ?: 'nova-pagina';
        $base = $slug;
        $n = 2;
        while ($this->site->pages()->where('slug', $slug)->exists()) $slug = $base.'-'.($n++);
        $page = $this->site->pages()->create(['name' => $name, 'slug' => $slug, 'status' => 'draft', 'is_homepage' => false, 'sort_order' => ((int) $this->site->pages()->max('sort_order')) + 1, 'seo' => []]);
        $this->loadPage($page->id);
    }

    public function duplicatePage(): void
    {
        $source = $this->site->pages()->with('sections')->findOrFail($this->pageId);
        $name = $source->name.' (cópia)';
        $slug = $source->slug.'-copia';
        $base = $slug; $n = 2;
        while ($this->site->pages()->where('slug', $slug)->exists()) $slug = $base.'-'.($n++);
        $page = DB::transaction(function () use ($source, $name, $slug) {
            $p = $this->site->pages()->create(['name'=>$name,'slug'=>$slug,'status'=>'draft','is_homepage'=>false,'sort_order'=>((int)$this->site->pages()->max('sort_order'))+1,'seo'=>$source->seo]);
            foreach ($source->sections as $s) $p->sections()->create(['type'=>$s->type,'label'=>$s->label,'content'=>$s->content,'settings'=>$s->settings,'sort_order'=>$s->sort_order,'is_visible'=>$s->is_visible]);
            return $p;
        });
        $this->loadPage($page->id);
    }

    public function deletePage(): void
    {
        $page = $this->site->pages()->findOrFail($this->pageId);
        abort_if($page->is_homepage, 422, 'A homepage não pode ser eliminada.');
        $page->delete();
        $next = $this->site->pages()->orderBy('sort_order')->firstOrFail();
        $this->loadPage($next->id);
    }

    public function setHomepage(int $id): void
    {
        $page = $this->site->pages()->findOrFail($id);
        DB::transaction(function () use ($page): void {
            $this->site->pages()->update(['is_homepage' => false]);
            $page->update(['is_homepage' => true]);
        });
        $this->refreshPages();
    }

    public function updatePageName(string $name): void
    {
        $page = $this->site->pages()->findOrFail($this->pageId);
        $name = trim(strip_tags($name));
        abort_if($name === '', 422);
        $page->update(['name' => Str::limit($name, 120, '')]);
        $this->refreshPages();
    }

    public function applyTemplate(string $category): void
    {
        $templates = $this->templates();
        abort_unless(isset($templates[$category]), 422);
        DB::transaction(function () use ($templates, $category): void {
            $this->site->pages()->each(fn (SitePage $p) => $p->delete());
            foreach ($templates[$category]['pages'] as $i => $definition) {
                $slug = Str::slug($definition['slug']);
                $page = $this->site->pages()->create(['name'=>$definition['name'],'slug'=>$slug,'status'=>'draft','is_homepage'=>$i===0,'sort_order'=>$i,'seo'=>['title'=>$definition['name'].' — '.$this->site->name]]);
                foreach ($definition['sections'] as $j => $type) $page->sections()->create(['type'=>$type,'label'=>Str::headline($type),'content'=>$this->defaultContent($type),'settings'=>['background'=>'transparent','padding'=>'lg','align'=>'left'],'sort_order'=>$j,'is_visible'=>true]);
            }
        });
        $page = $this->site->pages()->where('is_homepage', true)->firstOrFail();
        $this->loadPage($page->id);
        $this->showTemplates = false;
    }

    public function generateWithAi(WebsiteAiGenerator $generator): void
    {
        $brief = trim($this->aiBrief);
        abort_if($brief === '', 422, 'Escreve primeiro o que queres criar.');
        $result = $generator->generate(['description'=>$brief,'business_name'=>$this->site->name,'pages'=>['home','about','services','contact']]);
        $pages = $result['pages'] ?? [];
        DB::transaction(function () use ($pages): void {
            $this->site->pages()->each(fn (SitePage $p) => $p->delete());
            $order = 0;
            foreach ($pages as $slug => $sections) {
                $name = match ($slug) { 'home'=>'Home','about'=>'Sobre nós','services'=>'Serviços','contact'=>'Contactos', default=>Str::headline((string)$slug) };
                $page = $this->site->pages()->create(['name'=>$name,'slug'=>Str::slug((string)$slug),'status'=>'draft','is_homepage'=>$order===0,'sort_order'=>$order,'seo'=>['title'=>$name.' — '.$this->site->name]]);
                foreach ($sections as $i => $section) {
                    if (! is_array($section) || empty($section['type'])) continue;
                    $page->sections()->create(['type'=>$section['type'],'label'=>$section['label'] ?? Str::headline($section['type']),'content'=>$section['content'] ?? [],'settings'=>['background'=>'transparent','padding'=>'lg','align'=>'left'],'sort_order'=>$i,'is_visible'=>true]);
                }
                $order++;
            }
        });
        $page = $this->site->pages()->where('is_homepage', true)->firstOrFail();
        $this->loadPage($page->id);
        $this->showAi = false;
        $this->aiBrief = '';
    }

    public function publish(): void
    {
        $this->save();
        $checks = $this->publishChecks();
        abort_unless($checks['ok'], 422, 'Corrige os pontos obrigatórios antes de publicar.');
        $this->site->update(['is_published'=>true,'status'=>'published','published_at'=>now()]);
        $this->showPublish = false;
        $this->statusMessage = 'Website online';
    }

    public function publishChecks(): array
    {
        $pages = $this->site->pages()->with('sections')->get();
        $errors = [];
        if (trim((string) $this->site->name) === '') $errors[] = 'O website precisa de um nome.';
        if ($pages->isEmpty()) $errors[] = 'Cria pelo menos uma página.';
        foreach ($pages as $page) {
            if (trim((string) $page->name) === '' || trim((string) $page->slug) === '') $errors[] = 'Existe uma página sem nome ou slug.';
            if (count($page->sections) === 0) $errors[] = 'A página "'.$page->name.'" está vazia.';
        }
        return ['ok'=>count($errors)===0,'errors'=>$errors,'pages'=>$pages->count()];
    }

    public function unpublish(): void
    {
        $this->site->update(['is_published'=>false,'status'=>'draft']);
        $this->statusMessage = 'Website em rascunho';
    }

    public function openPreview(): void
    {
        $this->save();
        $this->dispatch('open-builder-preview', url: route('site.public', ['site'=>$this->site->slug,'pageSlug'=>$this->currentPage()['slug'] ?? null, 'preview'=>1]));
    }

    public function reorderPages(array $ids): void
    {
        $ids = array_values(array_map('intval', $ids));
        $pages = $this->site->pages()->whereIn('id',$ids)->get()->keyBy('id');
        abort_unless($pages->count() === count($ids), 422);
        foreach ($ids as $i => $id) $pages[$id]->update(['sort_order'=>$i]);
        $this->refreshPages();
    }

    public function updateTheme(string $key, string $value): void
    {
        abort_unless(in_array($key,['primary','secondary','background','text','font','button_radius','section_radius','shadow'],true),422);
        $this->theme[$key] = trim(strip_tags($value));
        $this->dirty = true;
    }

    public function currentPage(): array
    {
        return collect($this->pages)->firstWhere('id',$this->pageId) ?? [];
    }

    private function createPageRecord(string $name, string $slug, bool $home = false): SitePage
    {
        return DB::transaction(function () use ($name,$slug,$home) {
            $page = $this->site->pages()->create(['name'=>$name,'slug'=>$slug,'status'=>'draft','is_homepage'=>$home,'sort_order'=>0,'seo'=>['title'=>$name.' — '.$this->site->name]]);
            foreach (['hero','feature_grid','cta'] as $i=>$type) $page->sections()->create(['type'=>$type,'label'=>Str::headline($type),'content'=>$this->defaultContent($type),'settings'=>['background'=>'transparent','padding'=>'lg','align'=>'left'],'sort_order'=>$i,'is_visible'=>true]);
            return $page;
        });
    }

    private function defaultTheme(): array
    {
        return ['primary'=>'#2563eb','secondary'=>'#0f172a','background'=>'#ffffff','text'=>'#0f172a','font'=>'Inter','button_radius'=>'md','section_radius'=>'lg','shadow'=>'soft'];
    }

    private function defaultContent(string $type): array
    {
        return match ($type) {
            'hero'=>['title'=>'O teu negócio, apresentado de forma profissional.','subtitle'=>'Explica claramente o que ofereces e porque devem escolher-te.','button_label'=>'Saber mais','button_url'=>'#contacto'],
            'text'=>['title'=>'Uma mensagem importante','body'=>'Escreve aqui a informação principal desta secção.'],
            'image'=>['url'=>'','alt'=>'','caption'=>''],
            'button'=>['label'=>'Saber mais','url'=>'#'],
            'feature_grid'=>['title'=>'Porque escolher-nos','items'=>[['title'=>'Qualidade','description'=>'Apresenta um benefício.'],['title'=>'Confiança','description'=>'Mostra porque és uma boa escolha.'],['title'=>'Experiência','description'=>'Explica o teu diferencial.']]],
            'card'=>['title'=>'Serviço em destaque','description'=>'Descrição curta e clara.','button_label'=>'Saber mais','button_url'=>'#'],
            'testimonials'=>['title'=>'O que dizem os clientes','items'=>[['name'=>'Cliente','role'=>'Cliente','quote'=>'Adiciona aqui um testemunho real.']]],
            'faq'=>['title'=>'Perguntas frequentes','items'=>[['question'=>'Como funciona?','answer'=>'Explica aqui.']]],
            'gallery'=>['title'=>'Galeria','items'=>[]],
            'contact_form'=>['title'=>'Entra em contacto','description'=>'Envia-nos uma mensagem.','button_label'=>'Enviar mensagem'],
            'product_grid'=>['title'=>'Produtos','description'=>'Os teus produtos aparecem aqui.','limit'=>6],
            'product_card'=>['title'=>'Produto','description'=>'Descrição do produto.','price'=>''],
            'pricing'=>['title'=>'Planos','items'=>[['name'=>'Essencial','price'=>'49 €','description'=>'Plano inicial.','features'=>['Funcionalidade 1','Funcionalidade 2'],'button_label'=>'Escolher','button_url'=>'#contacto']]],
            'blog_posts'=>['title'=>'Artigos','items'=>[]],
            'social_links'=>['title'=>'Segue-nos','items'=>[]],
            'video'=>['title'=>'Vídeo','url'=>''],
            'map'=>['title'=>'Onde estamos','address'=>''],
            'newsletter'=>['title'=>'Recebe novidades','description'=>'Subscreve a nossa newsletter.','button_label'=>'Subscrever'],
            'cta'=>['title'=>'Vamos falar?','description'=>'Entra em contacto para saber mais.','button_label'=>'Contactar','button_url'=>'#contacto'],
            default=>[],
        };
    }

    private function templates(): array
    {
        $base = fn (array $pages) => ['pages'=>array_map(fn($p)=>['name'=>$p[0],'slug'=>$p[1],'sections'=>$p[2]],$pages)];
        return [
            'business'=>$base([['Home','home',['hero','feature_grid','testimonials','cta']],['Sobre nós','sobre-nos',['hero','text','feature_grid']],['Serviços','servicos',['hero','feature_grid','faq','cta']],['Contactos','contactos',['hero','contact_form','map']]]),
            'restaurant'=>$base([['Home','home',['hero','image','feature_grid','cta']],['Menu','menu',['hero','product_grid','cta']],['Sobre','sobre',['hero','text','gallery']],['Contactos','contactos',['hero','contact_form','map']]]),
            'portfolio'=>$base([['Home','home',['hero','gallery','cta']],['Projetos','projetos',['hero','gallery','feature_grid']],['Sobre mim','sobre',['hero','text','testimonials']],['Contactos','contactos',['hero','contact_form']]]),
            'freelancer'=>$base([['Home','home',['hero','feature_grid','testimonials','cta']],['Serviços','servicos',['hero','feature_grid','pricing']],['Sobre mim','sobre',['hero','text']],['Contactos','contactos',['hero','contact_form']]]),
            'agency'=>$base([['Home','home',['hero','feature_grid','testimonials','cta']],['Serviços','servicos',['hero','feature_grid','pricing']],['Casos','casos',['hero','gallery','testimonials']],['Contactos','contactos',['hero','contact_form']]]),
            'real-estate'=>$base([['Home','home',['hero','image','feature_grid','cta']],['Imóveis','imoveis',['hero','product_grid','cta']],['Sobre','sobre',['hero','text']],['Contactos','contactos',['hero','contact_form','map']]]),
            'landing'=>$base([['Home','home',['hero','feature_grid','testimonials','faq','cta']]]),
            'saas'=>$base([['Home','home',['hero','feature_grid','pricing','testimonials','cta']],['Funcionalidades','funcionalidades',['hero','feature_grid','faq']],['Preços','precos',['hero','pricing','faq']],['Contactos','contactos',['hero','contact_form']]]),
            'shop'=>$base([['Home','home',['hero','feature_grid','product_grid','cta']],['Loja','loja',['hero','product_grid','product_grid']],['Sobre','sobre',['hero','text']],['Contactos','contactos',['hero','contact_form']]]),
            'professional'=>$base([['Home','home',['hero','feature_grid','testimonials','cta']],['Serviços','servicos',['hero','feature_grid','faq']],['Sobre nós','sobre-nos',['hero','text']],['Contactos','contactos',['hero','contact_form','map']]]),
        ];
    }

    public function render()
    {
        return view('livewire.builder-studio');
    }
}
