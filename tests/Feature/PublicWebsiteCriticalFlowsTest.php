<?php

declare(strict_types=1);

use App\Livewire\PublicSite;
use App\Models\Site;
use App\Models\SiteAnalyticsEvent;
use App\Models\SitePage;
use App\Models\SiteSubmission;
use App\Models\User;
use Livewire\Livewire;

function publicSiteWithPage(array $siteAttributes = []): array
{
    $owner = User::factory()->create();
    $site = Site::factory()->create(array_merge([
        'owner_id' => $owner->id,
        'is_published' => true,
        'status' => 'published',
    ], $siteAttributes));

    $page = SitePage::create([
        'site_id' => $site->id,
        'name' => 'Início',
        'slug' => 'inicio',
        'status' => 'published',
        'is_homepage' => true,
        'seo' => [],
        'sort_order' => 0,
    ]);

    return [$owner, $site, $page];
}

test('public websites create analytics page views without exposing the session id', function () {
    [, $site] = publicSiteWithPage();

    $response = $this->withHeader('User-Agent', 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) Mobile')
        ->get(route('site.public', $site));

    $response->assertOk();

    $event = SiteAnalyticsEvent::query()->where('site_id', $site->id)->latest()->first();

    expect($event)->not->toBeNull()
        ->and($event->event_type)->toBe('page_view')
        ->and($event->device_type)->toBe('mobile')
        ->and($event->session_hash)->not->toBe(session()->getId());
});

test('public contact forms create site-scoped submissions', function () {
    [, $site] = publicSiteWithPage();

    Livewire::test(PublicSite::class, ['site' => $site])
        ->set('contactName', 'João Silva')
        ->set('contactEmail', 'joao@example.com')
        ->set('contactMessage', 'Gostaria de saber mais informações.')
        ->call('submitContact')
        ->assertHasNoErrors();

    $submission = SiteSubmission::query()->where('site_id', $site->id)->first();

    expect($submission)->not->toBeNull()
        ->and($submission->form_type)->toBe('contact')
        ->and($submission->email)->toBe('joao@example.com')
        ->and($submission->status)->toBe('new');
});

test('public newsletter forms create site-scoped subscriptions', function () {
    [, $site] = publicSiteWithPage();

    Livewire::test(PublicSite::class, ['site' => $site])
        ->set('newsletterEmail', 'newsletter@example.com')
        ->call('subscribeNewsletter')
        ->assertHasNoErrors();

    expect(SiteSubmission::query()->where('site_id', $site->id)->where('form_type', 'newsletter')->where('email', 'newsletter@example.com')->exists())->toBeTrue();
});

test('public submissions reject invalid data', function () {
    [, $site] = publicSiteWithPage();

    Livewire::test(PublicSite::class, ['site' => $site])
        ->set('contactName', '')
        ->set('contactEmail', 'not-an-email')
        ->set('contactMessage', '')
        ->call('submitContact')
        ->assertHasErrors(['contactName', 'contactEmail', 'contactMessage']);

    expect(SiteSubmission::where('site_id', $site->id)->count())->toBe(0);
});

test('unrelated users cannot preview another website', function () {
    [, $site] = publicSiteWithPage(['is_published' => false, 'status' => 'draft']);
    $other = User::factory()->create();

    $this->actingAs($other)
        ->get(route('site.public', $site).'?preview=1')
        ->assertForbidden();
});

test('published public pages cannot expose another website page by slug', function () {
    [, $siteA] = publicSiteWithPage();
    [, , $pageB] = publicSiteWithPage();

    $this->get(route('site.public', [$siteA, 'pageSlug' => $pageB->slug]))->assertNotFound();
});
