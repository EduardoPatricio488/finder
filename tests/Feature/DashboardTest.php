<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the platform dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $this->get(route('dashboard'))->assertOk();
});

test('global administrators can access the platform administration dashboard', function () {
    $user = User::factory()->administrator()->create();
    $this->actingAs($user);
    $this->get(route('admin.dashboard'))->assertOk();
});
