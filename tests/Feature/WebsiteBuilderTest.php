<?php

declare(strict_types=1);

use App\Livewire\BuilderEditor;
use App\Livewire\CreateSite;
use App\Livewire\MenuManager;
use App\Livewire\SiteSettings;
use App\Models\Site;
use App\Models\User;
use Livewire\Livewire;

test('users can create a website through the creation flow', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $this->get(route('site.create'))->assertOk();

    Livewire::test(CreateSite::class)
        ->set('name', 'Meu Projeto')
        ->set('slug', 'meu-projeto')
        ->set('type', 'business')
        ->set('template', 'studio')
        ->call('create')
        ->assertRedirect();

    expect(Site::where('slug', 'meu-projeto')->where('owner_id', $user->id)->exists())->toBeTrue();
});

test('a user cannot edit another users website builder', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $site = Site::factory()->create(['owner_id' => $owner->id]);
    $this->actingAs($other);
    $this->get(route('builder.edit', $site))->assertForbidden();
});

test('a website owner can edit and publish builder content', function () {
    $owner = User::factory()->create();
    $site = Site::factory()->create(['owner_id' => $owner->id, 'is_published' => false, 'status' => 'draft']);
    $this->actingAs($owner);

    Livewire::test(BuilderEditor::class, ['site' => $site])
        ->call('addSection', 'text')
        ->call('save')
        ->call('publish');

    $site->refresh();
    expect($site->is_published)->toBeTrue()->and($site->status)->toBe('published');
});

test('a website owner can unpublish a website', function () {
    $owner = User::factory()->create();
    $site = Site::factory()->create(['owner_id' => $owner->id, 'is_published' => true, 'status' => 'published']);
    $this->actingAs($owner);

    Livewire::test(BuilderEditor::class, ['site' => $site])->call('unpublish');

    $site->refresh();
    expect($site->is_published)->toBeFalse()->and($site->status)->toBe('draft')->and($site->published_at)->toBeNull();
});

test('unpublished websites are not publicly accessible', function () {
    $owner = User::factory()->create();
    $site = Site::factory()->create(['owner_id' => $owner->id, 'is_published' => false, 'status' => 'draft']);
    $this->get(route('site.public', $site))->assertNotFound();
});

test('website owners can preview unpublished websites without analytics tracking', function () {
    $owner = User::factory()->create();
    $site = Site::factory()->create(['owner_id' => $owner->id, 'is_published' => false, 'status' => 'draft']);
    $this->actingAs($owner);

    $this->get(route('site.public', [$site, 'preview' => 1]))->assertOk();

    expect($site->analyticsEvents()->count())->toBe(0);
});

test('website preview is forbidden to unrelated users', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $site = Site::factory()->create(['owner_id' => $owner->id, 'is_published' => false, 'status' => 'draft']);
    $this->actingAs($other);

    $this->get(route('site.public', [$site, 'preview' => 1]))->assertForbidden();
});

test('builder undo and redo restore section order', function () {
    $owner = User::factory()->create();
    $site = Site::factory()->create(['owner_id' => $owner->id]);
    $this->actingAs($owner);

    $component = Livewire::test(BuilderEditor::class, ['site' => $site]);
    $component->call('addSection', 'text')->call('addSection', 'button');
    $component->call('moveSection', 1, 'up');
    $component->call('undo');

    expect($component->get('sections.0.type'))->toBe('text')
        ->and($component->get('sections.1.type'))->toBe('button');

    $component->call('redo');

    expect($component->get('sections.0.type'))->toBe('button')
        ->and($component->get('sections.1.type'))->toBe('text');
});

test('website owners can update identity and SEO settings', function () {
    $owner = User::factory()->create();
    $site = Site::factory()->create(['owner_id' => $owner->id, 'slug' => 'original']);
    $this->actingAs($owner);

    Livewire::test(SiteSettings::class, ['site' => $site])
        ->set('name', 'Novo Nome')
        ->set('slug', 'novo-nome')
        ->set('tagline', 'Descrição profissional')
        ->set('seoTitle', 'Novo Nome | Website')
        ->set('seoDescription', 'Descrição para pesquisa')
        ->call('save');

    $site->refresh();
    expect($site->name)->toBe('Novo Nome')
        ->and($site->slug)->toBe('novo-nome')
        ->and($site->seo['title'])->toBe('Novo Nome | Website');
});

test('users cannot manage another websites settings or menus', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $site = Site::factory()->create(['owner_id' => $owner->id]);
    $this->actingAs($other);

    $this->get(route('admin.site.settings', $site))->assertForbidden();
    $this->get(route('admin.site.menus', $site))->assertForbidden();
    expect($site->menus()->count())->toBe(0);
});
