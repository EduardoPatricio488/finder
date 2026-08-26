<?php

use App\Livewire\ProductDetail;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;

test('a product detail page can be visited publicly', function () {
    $product = Product::create([
        'name' => 'Caneca',
        'slug' => 'caneca-avaliacoes',
        'price' => 12,
        'is_active' => true,
        'stock' => 2,
    ]);

    $this->get(route('products.show', $product))->assertOk();
});

test('a user without a purchase cannot submit a product review', function () {
    $user = User::factory()->create();
    $product = Product::create([
        'name' => 'Caderno',
        'slug' => 'caderno-sem-compra',
        'price' => 10,
        'is_active' => true,
        'stock' => 2,
    ]);

    Livewire::actingAs($user)
        ->test(ProductDetail::class, ['product' => $product])
        ->assertSet('canReview', false)
        ->set('rating', 4)
        ->set('comment', 'Comentário não autorizado')
        ->call('submitReview')
        ->assertForbidden();

    $this->assertDatabaseCount('product_reviews', 0);
});

test('a user who bought a product can submit a review', function () {
    $user = User::factory()->create(['email' => 'cliente@example.com']);
    $product = Product::create([
        'name' => 'Garrafa',
        'slug' => 'garrafa-com-avaliacao',
        'price' => 20,
        'is_active' => true,
        'stock' => 2,
    ]);
    $customer = Customer::create(['name' => $user->name, 'email' => $user->email]);
    $order = Order::create([
        'order_number' => 'PED-AVALIACAO-1',
        'customer_id' => $customer->id,
        'sold_at' => now(),
        'total' => 20,
        'status' => 'concluida',
        'payment_method' => 'mbway',
    ]);
    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_name' => $product->name,
        'quantity' => 1,
        'unit_price' => 20,
        'total' => 20,
    ]);

    Livewire::actingAs($user)
        ->test(ProductDetail::class, ['product' => $product])
        ->assertSet('canReview', true)
        ->set('rating', 5)
        ->set('comment', 'Excelente produto, recomendo.')
        ->call('submitReview');

    $this->assertDatabaseHas('product_reviews', [
        'product_id' => $product->id,
        'user_id' => $user->id,
        'order_id' => $order->id,
        'rating' => 5,
        'comment' => 'Excelente produto, recomendo.',
    ]);
});
