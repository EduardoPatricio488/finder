<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->administrator()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('regular users cannot visit the administration dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $this->get(route('dashboard'))->assertForbidden();
});
