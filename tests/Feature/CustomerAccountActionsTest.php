<?php

use App\Livewire\CustomerAccount;
use App\Livewire\ProductCatalog;
use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;

test('customers can save addresses, payment methods and notification preferences', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)->test(CustomerAccount::class)
        ->set('addressLabel', 'Casa')
        ->set('addressText', 'Rua Principal, 10')
        ->call('addAddress')
        ->set('paymentLabel', 'Cartão pessoal')
        ->set('paymentLastFour', '1234')
        ->call('addPaymentMethod')
        ->set('promotions', false)
        ->call('saveNotifications');

    $this->assertDatabaseHas('customer_addresses', ['user_id' => $user->id, 'label' => 'Casa']);
    $this->assertDatabaseHas('customer_payment_methods', ['user_id' => $user->id, 'last_four' => '1234']);
    $this->assertDatabaseHas('customer_notification_preferences', ['user_id' => $user->id, 'promotions' => false]);
});

test('authenticated customers can add a product to favorites', function () {
    $user = User::factory()->create();
    $product = Product::create(['name' => 'Produto favorito', 'slug' => 'produto-favorito', 'price' => 10, 'is_active' => true, 'stock' => 2]);

    Livewire::actingAs($user)->test(ProductCatalog::class)->call('toggleFavorite', $product->id);

    $this->assertDatabaseHas('favorite_products', ['user_id' => $user->id, 'product_id' => $product->id]);
});
