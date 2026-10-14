<?php

declare(strict_types=1);

use App\Livewire\MenuManager;
use App\Models\Site;
use App\Models\User;
use Livewire\Livewire;

test('sidebar saves only pages selected for the current website', function () {
    $owner = User::factory()->create();
    $site = Site::factory()->create(['owner_id' => $owner->id]);

    $firstPage = $site->pages()->create([
        'name' => 'Início',
        'slug' => 'home',
        'status' => 'published',
        'is_homepage' => true,
        'seo' => [],
        'theme' => [],
        'sort_order' => 0,
    ]);

    $secondPage = $site->pages()->create([
        'name' => 'Sobre',
        'slug' => 'about',
        'status' => 'published',
        'is_homepage' => false,
        'seo' => [],
        'theme' => [],
        'sort_order' => 1,
    ]);

    $this->actingAs($owner)->withSession(['current_site_id' => $site->id]);

    Livewire::test(MenuManager::class)
        ->set('sidebarPageIds', [(string) $secondPage->id])
        ->call('saveSidebar')
        ->assertHasNoErrors();

    $site->refresh();

    expect(data_get($site->settings, 'sidebar.page_ids'))
        ->toBe([(string) $secondPage->id])
        ->not->toContain((string) $firstPage->id);
});
