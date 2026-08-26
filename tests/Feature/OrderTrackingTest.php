<?php

use App\Livewire\OrderTracking;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Livewire\Livewire;

test('guests must authenticate to view order tracking', function () {
    $this->get(route('orders.tracking'))->assertRedirect(route('login'));
});

test('customers only see their own orders in tracking', function () {
    $user = User::factory()->create(['email' => 'cliente@example.com']);
    $customer = Customer::create(['name' => $user->name, 'email' => $user->email]);
    $otherCustomer = Customer::create(['name' => 'Outra pessoa', 'email' => 'outra@example.com']);
    Order::create(['order_number' => 'PED-MINHA-1', 'customer_id' => $customer->id, 'sold_at' => now(), 'total' => 25, 'status' => 'enviado', 'payment_method' => 'mbway']);
    Order::create(['order_number' => 'PED-OUTRA-1', 'customer_id' => $otherCustomer->id, 'sold_at' => now(), 'total' => 25, 'status' => 'entregue', 'payment_method' => 'mbway']);

    $this->actingAs($user)->get(route('orders.tracking'))->assertOk()->assertSee('PED-MINHA-1')->assertDontSee('PED-OUTRA-1');
});

test('tracking marks completed steps according to the order status', function () {
    $user = User::factory()->create(['email' => 'tracking@example.com']);
    $customer = Customer::create(['name' => $user->name, 'email' => $user->email]);
    $order = Order::create(['order_number' => 'PED-STATUS-1', 'customer_id' => $customer->id, 'sold_at' => now(), 'total' => 25, 'status' => 'enviado', 'payment_method' => 'mbway']);

    Livewire::actingAs($user)->test(OrderTracking::class)->assertSee('Pagamento confirmado')->assertSee('Enviada')->assertSee('○');
    expect($order->status)->toBe('enviado');
});
