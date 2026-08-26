<?php

use App\Models\User;
use Laravel\Fortify\Features;

test('login screen can be rendered', function () {
    $this->get(route('login'))->assertOk();
});

test('login screen presents the Finder identity', function () {
    $this->get(route('login'))
        ->assertSee('Finder')
        ->assertSee('Gestão de sites')
        ->assertSee('Entrar na sua conta')
        ->assertSee('Os seus sites, num só lugar.')
        ->assertSee('Voltar aos sites')
        ->assertDontSee('Log in to your account');
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->administrator()->create();

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('entry', absolute: false));

    $this->assertAuthenticated();
});

test('regular users are sent to the public sales page after login', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $this->get(route('entry'))
        ->assertRedirect(route('sales'));
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertSessionHasErrorsIn('email');

    $this->assertGuest();
});

test('users with two factor enabled are redirected to two factor challenge', function () {
    $this->skipUnlessFortifyHas(Features::twoFactorAuthentication());

    Features::twoFactorAuthentication([
        'confirm' => true,
        'confirmPassword' => true,
    ]);

    $user = User::factory()->withTwoFactor()->create();

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('two-factor.login'));
    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('logout'));

    $response->assertRedirect(route('home'));

    $this->assertGuest();
});
