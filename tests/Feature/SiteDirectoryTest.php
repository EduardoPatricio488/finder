<?php

use App\Livewire\SiteDirectory;
use App\Models\Site;
use App\Models\User;
use Livewire\Livewire;

test('the home page lists published sites including casa and co', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Os seus sites, num só lugar.')
        ->assertSee('Finder Premium')
        ->assertSee('Casa & Co.')
        ->assertSee(route('sales'));
});

test('guests do not see unpublished sites on the home page', function () {
    Site::factory()->unpublished()->create([
        'name' => 'Atelier Norte',
        'slug' => 'atelier-norte',
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee('Atelier Norte');
});

test('administrators see unpublished sites on the home page', function () {
    $admin = User::factory()->administrator()->create();
    Site::factory()->unpublished()->create([
        'name' => 'Atelier Norte',
        'slug' => 'atelier-norte',
    ]);

    $this->actingAs($admin)
        ->get(route('home'))
        ->assertOk()
        ->assertSee('Atelier Norte')
        ->assertSee('Online');
});

test('opening casa and co from the directory sends visitors to the store', function () {
    $site = Site::query()->where('slug', 'casa-co')->firstOrFail();

    $this->get(route('sites.show', $site))
        ->assertRedirect(route('sales'));
});

test('opening an unpublished site is hidden from guests', function () {
    $site = Site::factory()->unpublished()->create([
        'name' => 'Atelier Norte',
        'slug' => 'atelier-norte',
    ]);

    $this->get(route('sites.show', $site))->assertNotFound();
});

test('administrators can open an unpublished site workspace', function () {
    $admin = User::factory()->administrator()->create();
    $site = Site::factory()->unpublished()->create([
        'name' => 'Atelier Norte',
        'slug' => 'atelier-norte',
        'tagline' => 'Peças em madeira.',
    ]);

    $this->actingAs($admin)
        ->get(route('sites.show', $site))
        ->assertOk()
        ->assertSee('Atelier Norte')
        ->assertSee('Peças em madeira.');
});

test('guests cannot create a site', function () {
    Livewire::test(SiteDirectory::class)
        ->set('name', 'Atelier Norte')
        ->call('save')
        ->assertForbidden();

    expect(Site::query()->where('name', 'Atelier Norte')->exists())->toBeFalse();
});

test('free users cannot create a new site', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(SiteDirectory::class)
        ->call('create')
        ->assertForbidden();

    expect(Site::query()->where('slug', 'atelier-norte')->exists())->toBeFalse();
});

test('premium users can create their own site', function () {
    $user = User::factory()->finderPremium()->create();

    Livewire::actingAs($user)
        ->test(SiteDirectory::class)
        ->call('create')
        ->set('name', 'Atelier Norte')
        ->call('save')
        ->assertHasNoErrors();

    $site = Site::query()->where('slug', 'atelier-norte')->first();

    expect($site)
        ->not->toBeNull()
        ->owner_id->toBe($user->id);
});

test('users can start a new site from a template', function () {
    $user = User::factory()->finderPremium()->create();

    Livewire::actingAs($user)
        ->test(SiteDirectory::class)
        ->call('chooseTemplate', 'tech')
        ->set('name', 'Loja do Eduardo')
        ->call('save')
        ->assertHasNoErrors();

    $site = Site::query()->where('slug', 'loja-do-eduardo')->first();

    expect($site)
        ->not->toBeNull()
        ->type->toBe('online_store')
        ->accent->toBe('sky')
        ->tagline->toBe('Tecnologia para o teu dia a dia.')
        ->owner_id->toBe($user->id);
});

test('the casa template preloads the casa and co model', function () {
    $user = User::factory()->finderPremium()->create();

    Livewire::actingAs($user)
        ->test(SiteDirectory::class)
        ->call('create')
        ->assertSet('selectedTemplate', 'casa')
        ->assertSet('tagline', 'Coisas bonitas para viver melhor.')
        ->assertSet('accent', 'amber')
        ->assertSet('primaryColor', '#f59e0b');
});

test('administrators can create a published site', function () {
    $admin = User::factory()->administrator()->create();

    Livewire::actingAs($admin)
        ->test(SiteDirectory::class)
        ->call('create')
        ->set('name', 'Atelier Norte')
        ->set('tagline', 'Peças em madeira.')
        ->set('description', 'Um atelier de móveis feitos à mão.')
        ->set('categoryLabel', 'Atelier')
        ->call('save')
        ->assertHasNoErrors();

    $site = Site::query()->where('slug', 'atelier-norte')->first();

    expect($site)
        ->not->toBeNull()
        ->name->toBe('Atelier Norte')
        ->category_label->toBe('Loja online')
        ->is_published->toBeTrue();
});

test('administrators cannot delete the casa and co site', function () {
    $admin = User::factory()->administrator()->create();
    $site = Site::query()->where('slug', 'casa-co')->firstOrFail();

    Livewire::actingAs($admin)
        ->test(SiteDirectory::class)
        ->call('delete', $site->id)
        ->assertForbidden();

    expect(Site::query()->where('slug', 'casa-co')->exists())->toBeTrue();
});

test('administrators can delete a site they created', function () {
    $admin = User::factory()->administrator()->create();
    $site = Site::factory()->create([
        'name' => 'Atelier Norte',
        'slug' => 'atelier-norte',
    ]);

    Livewire::actingAs($admin)
        ->test(SiteDirectory::class)
        ->call('delete', $site->id)
        ->assertSuccessful();

    expect(Site::query()->where('slug', 'atelier-norte')->exists())->toBeFalse();
});

test('creating a site requires a name', function () {
    $admin = User::factory()->administrator()->create();

    Livewire::actingAs($admin)
        ->test(SiteDirectory::class)
        ->call('create')
        ->set('name', '')
        ->call('save')
        ->assertHasErrors(['name']);
});
