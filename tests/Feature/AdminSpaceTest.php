<?php

use App\Models\Site;
use App\Models\User;

test('global administrators can access platform administration', function () {
    $user = User::factory()->administrator()->create();
    $this->actingAs($user);

    $this->get(route('admin.dashboard'))->assertOk();
    $this->get(route('admin.users'))->assertOk();
    $this->get(route('admin.config'))->assertOk();
});

test('regular users are denied global administration', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('admin.dashboard'))->assertForbidden();
    $this->get(route('admin.users'))->assertForbidden();
});

test('website administration is isolated by ownership', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $site = Site::factory()->create(['owner_id' => $owner->id]);

    $this->actingAs($owner);
    $this->get(route('admin.site.dashboard', $site))->assertOk();

    $this->actingAs($other);
    $this->get(route('admin.site.dashboard', $site))->assertForbidden();
});
