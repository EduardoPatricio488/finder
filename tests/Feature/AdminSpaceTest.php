<?php

use App\Livewire\AdminAssistant;
use App\Models\User;
use Livewire\Livewire;

test('authenticated users can access the administration space', function () {
    $user = User::factory()->administrator()->create();

    $this->actingAs($user);

    $this->get(route('dashboard'))->assertOk();
    $this->get(route('admin.products'))->assertOk();
    $this->get(route('admin.users'))->assertOk();
    $this->get(route('admin.config'))->assertOk();
    $this->get(route('admin.sales'))->assertOk();
    $this->get(route('admin.customers'))->assertOk();
    $this->get(route('admin.categories'))->assertOk();
    $this->get(route('admin.reports'))->assertOk();
    $this->get(route('admin.orders'))->assertOk();
    $this->get(route('admin.payments'))->assertOk();
    $this->get(route('admin.stock'))->assertOk();
    $this->get(route('admin.assistant'))->assertOk();
});

test('regular users are denied access to the administration space', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $this->get(route('dashboard'))->assertForbidden();
    $this->get(route('admin.products'))->assertForbidden();
});

test('the administration assistant answers sales questions from current data', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(AdminAssistant::class)
        ->set('question', 'Como estão as vendas este mês?')
        ->call('ask')
        ->assertSee('Este mês, a loja registou');
});
