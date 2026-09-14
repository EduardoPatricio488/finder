<?php

declare(strict_types=1);

use App\Livewire\PublicSite;
use App\Livewire\SiteSubmissions;
use App\Models\Site;
use App\Models\SitePage;
use App\Models\SiteSubmission;
use App\Models\User;
use Livewire\Livewire;

it('records public contact submissions only for published websites', function () {
    $owner = User::factory()->create();
    $site = Site::factory()->create([
        'owner_id' => $owner->id,
        'is_published' => true,
        'status' => 'published',
    ]);

    SitePage::create([
        'site_id' => $site->id,
        'name' => 'Home',
        'slug' => 'home',
        'status' => 'published',
        'is_homepage' => true,
        'sort_order' => 0,
    ]);

    Livewire::test(PublicSite::class, ['site' => $site])
        ->set('contactName', 'Maria Silva')
        ->set('contactEmail', 'maria@example.com')
        ->set('contactMessage', 'Gostava de saber mais.')
        ->call('submitContact');

    expect(SiteSubmission::where('site_id', $site->id)->where('form_type', 'contact')->count())->toBe(1)
        ->and(SiteSubmission::where('site_id', $site->id)->first()->email)->toBe('maria@example.com');
});

it('isolates website submissions between websites', function () {
    $owner = User::factory()->create();
    $firstSite = Site::factory()->create(['owner_id' => $owner->id, 'is_published' => true, 'status' => 'published']);
    $secondSite = Site::factory()->create(['owner_id' => $owner->id, 'is_published' => true, 'status' => 'published']);

    SiteSubmission::create([
        'site_id' => $firstSite->id,
        'form_type' => 'contact',
        'name' => 'Primeiro',
        'email' => 'first@example.com',
        'message' => 'Mensagem',
    ]);

    $this->actingAs($owner);
    Livewire::test(SiteSubmissions::class, ['site' => $secondSite])
        ->assertSee('Ainda não existem submissões.')
        ->assertDontSee('first@example.com');
});
