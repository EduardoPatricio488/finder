<?php

declare(strict_types=1);

use App\Livewire\BuilderStudio;
use App\Models\Site;
use App\Models\SitePage;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Livewire;

test('builder studio supports page creation duplication homepage and deletion', function () {
    $owner = User::factory()->create();
    $site = Site::factory()->create(['owner_id' => $owner->id]);
    $home = SitePage::query()->create([
        'site_id' => $site->id,
        'name' => 'Home',
        'slug' => 'home',
        'status' => 'draft',
        'is_homepage' => true,
        'sort_order' => 0,
        'seo' => [],
    ]);
    $site->pages()->create([
        'name' => 'Serviços',
        'slug' => 'servicos',
        'status' => 'draft',
        'is_homepage' => false,
        'sort_order' => 1,
        'seo' => [],
    ]);

    $this->actingAs($owner);
    $component = Livewire::test(BuilderStudio::class, ['site' => $site])
        ->call('createPage')
        ->assertSet('statusMessage', 'Nova página criada');

    expect($site->pages()->count())->toBe(3);

    $component->call('duplicatePage');
    expect($site->pages()->count())->toBe(4);

    $newPageId = $component->get('pageId');
    $component->call('setHomepage', $newPageId);
    $site->refresh();
    expect($site->pages()->where('is_homepage', true)->value('id'))->toBe($newPageId);

    $component->call('deletePage');
    expect($site->pages()->count())->toBe(3);
    expect($home->fresh()->exists)->toBeTrue();
});

test('builder studio keeps page and section mutations scoped to the current site', function () {
    $owner = User::factory()->create();
    $otherOwner = User::factory()->create();
    $site = Site::factory()->create(['owner_id' => $owner->id]);
    $otherSite = Site::factory()->create(['owner_id' => $otherOwner->id]);
    $page = $site->pages()->create([
        'name' => 'Home',
        'slug' => 'home',
        'status' => 'draft',
        'is_homepage' => true,
        'sort_order' => 0,
        'seo' => [],
    ]);
    $otherPage = $otherSite->pages()->create([
        'name' => 'Home',
        'slug' => 'home',
        'status' => 'draft',
        'is_homepage' => true,
        'sort_order' => 0,
        'seo' => [],
    ]);

    $this->actingAs($owner);
    $component = Livewire::test(BuilderStudio::class, ['site' => $site]);

    expect(fn () => $component->call('loadPage', $otherPage->id))->toThrow(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);
    expect($page->fresh()->site_id)->toBe($site->id);
});

test('builder studio publish checks block invalid sites and publish valid sites', function () {
    $owner = User::factory()->create();
    $site = Site::factory()->create(['owner_id' => $owner->id, 'is_published' => false, 'status' => 'draft']);
    $site->pages()->create([
        'name' => 'Home',
        'slug' => 'home',
        'status' => 'draft',
        'is_homepage' => true,
        'sort_order' => 0,
        'seo' => [],
    ]);

    $this->actingAs($owner);
    $component = Livewire::test(BuilderStudio::class, ['site' => $site])->call('runPublishChecks');
    expect(collect($component->get('publishChecks'))->contains(fn (array $check): bool => $check['level'] === 'error'))->toBeTrue();

    $site->pages()->first()->sections()->create([
        'type' => 'hero',
        'label' => 'Hero',
        'content' => ['title' => 'Olá'],
        'settings' => [],
        'sort_order' => 0,
        'is_visible' => true,
    ]);

    $component->call('publish');
    expect($site->fresh()->is_published)->toBeTrue();
});

test('builder studio can edit a v2 element through the inspector API', function () {
    $owner = User::factory()->create();
    $site = Site::factory()->create(['owner_id' => $owner->id]);
    $page = $site->pages()->create([
        'name' => 'Home',
        'slug' => 'home',
        'status' => 'draft',
        'is_homepage' => true,
        'sort_order' => 0,
        'seo' => [],
    ]);
    $section = $page->sections()->create([
        'type' => 'hero',
        'label' => 'Hero',
        'content' => ['title' => 'Original'],
        'settings' => [],
        'sort_order' => 0,
        'is_visible' => true,
    ]);

    $this->actingAs($owner);
    $component = Livewire::test(BuilderStudio::class, ['site' => $site]);
    $component->call('addElement', 'heading');
    $elementId = $component->get('selectedElementId');

    expect($elementId)->toBeString()->and(Str::startsWith($elementId, 'el_'))->toBeTrue();

    $component->call('updateSelectedElement', 'text', 'Título actualizado');
    $component->call('save');

    $section->refresh();
    $document = $section->settings['builder_document'];
    expect($document['nodes'][0]['children'][0]['content']['text'])->toBe('Título atualizado');
});
