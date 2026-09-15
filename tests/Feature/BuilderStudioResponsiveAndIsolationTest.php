<?php

declare(strict_types=1);

use App\Livewire\BuilderStudio;
use App\Models\Site;
use App\Models\User;
use Livewire\Livewire;

it('persists responsive element settings per device without affecting other devices', function () {
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
        'content' => ['title' => 'Olá'],
        'settings' => [],
        'sort_order' => 0,
        'is_visible' => true,
    ]);

    $this->actingAs($owner);
    $component = Livewire::test(BuilderStudio::class, ['site' => $site]);
    $component->call('addElement', 'heading');
    $elementId = $component->get('selectedElementId');

    $component->set('device', 'desktop');
    $component->call('updateSelectedElementSetting', 'font_size', '48px');
    $component->set('device', 'mobile');
    $component->call('updateSelectedElementSetting', 'font_size', '28px');
    $component->call('save');

    $document = $section->fresh()->settings['builder_document'];
    $element = collect($document['nodes'][0]['children'])->firstWhere('id', $elementId);

    expect($element['settings']['responsive']['base']['font_size'])->toBe('48px')
        ->and($element['settings']['responsive']['mobile']['font_size'])->toBe('28px');
});

it('rejects element mutations that reference a section outside the current site', function () {
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
    $otherSection = $otherPage->sections()->create([
        'type' => 'hero',
        'label' => 'Other',
        'content' => ['title' => 'Other'],
        'settings' => [],
        'sort_order' => 0,
        'is_visible' => true,
    ]);

    $this->actingAs($owner);
    $component = Livewire::test(BuilderStudio::class, ['site' => $site]);
    $component->set('sections', [[
        'id' => $otherSection->id,
        'type' => 'hero',
        'label' => 'Injected',
        'content' => [],
        'settings' => [],
        'is_visible' => true,
    ]]);
    $component->set('selectedSection', 0);

    expect(fn () => $component->call('addElement', 'heading'))->toThrow(Throwable::class);
    expect($otherSection->fresh()->content['title'])->toBe('Other');
    expect($page->sections()->count())->toBe(0);
});
