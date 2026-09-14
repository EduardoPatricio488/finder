<?php

declare(strict_types=1);

use App\Livewire\PublicSite;
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

    $page = SitePage::factory()->create([
        'site_id' => $site->id,
        'is_homepage' => true,
        'status' => 'published',
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
    Livewire::test(\App\Livewire\SiteSubmissions::class, ['site' => $secondSite])
        ->assertSee('Ainda não existem submissões.')
        ->assertDontSee('first@example.com');
});
