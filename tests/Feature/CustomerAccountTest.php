<?php

use App\Livewire\CustomerAccount;
use App\Models\User;
use Livewire\Livewire;

test('guests must authenticate to access the customer account', function () {
    $this->get(route('account'))->assertRedirect(route('login'));
});

test('authenticated customers can access all personal account sections', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $this->get(route('account'))->assertOk()->assertSee('A minha conta');
    $this->get(route('account.favorites'))->assertOk()->assertSee('Favoritos');
    $this->get(route('account.addresses'))->assertOk()->assertSee('Moradas');
    $this->get(route('account.payments'))->assertOk()->assertSee('Métodos de pagamento');
    $this->get(route('account.notifications'))->assertOk()->assertSee('Notificações');
});

test('the account component renders the authenticated customer name', function () {
    $user = User::factory()->create(['name' => 'João Costa']);

    Livewire::actingAs($user)
        ->test(CustomerAccount::class)
        ->assertSee('João Costa');
});
