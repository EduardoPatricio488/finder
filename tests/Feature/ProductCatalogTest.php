<?php

use App\Livewire\ProductCatalog;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

test('the public sales introduction page can be visited', function () {
    $this->get(route('sales'))
        ->assertOk()
        ->assertSee('Coisas bonitas para viver melhor.')
        ->assertSee(route('products'));
});

test('the introduction page greets authenticated users by name', function () {
    $user = User::factory()->create(['name' => 'Ana Martins']);

    $this->actingAs($user)
        ->get(route('sales'))
        ->assertSee('Olá, Ana Martins');
});

test('the public products page can be visited', function () {
    $this->get(route('products'))->assertOk();
});

test('authenticated customers see the full customer sidebar on the products page', function () {
    $user = User::factory()->create([
        'name' => 'João Costa',
        'email' => 'joao@gmail.com',
    ]);

    $this->actingAs($user)
        ->get(route('products'))
        ->assertOk()
        ->assertSeeInOrder([
            'Área do cliente',
            'Todos os sites',
            'Loja',
            'Explorar produtos',
            'As minhas encomendas',
            'Favoritos',
            'Carrinho',
            'A minha conta',
            'Perfil',
            'Moradas',
            'Segurança',
            'Pagamentos',
            'Notificações',
            'Definições',
            'João Costa',
            'joao@gmail.com',
            'Terminar sessão',
        ]);
});

test('administrators see the admin options in the customer sidebar on the products page', function () {
    $admin = User::factory()->administrator()->create();

    $this->actingAs($admin)
        ->get(route('products'))
        ->assertOk()
        ->assertSeeInOrder([
            'Administração',
            'Painel',
            'Produtos',
            'Promoções',
            'Categorias',
            'Vendas',
            'Encomendas',
            'Pagamentos',
            'Clientes',
            'Utilizadores',
            'Relatórios',
            'Movimentos de stock',
            'Configuração',
        ]);
});

test('the sidebar cart link opens the cart panel on the products page', function () {
    $this->get(route('products', ['cart' => 1]))
        ->assertOk()
        ->assertSee('O seu pedido')
        ->assertSee('Carrinho');
});

test('active promotions show the original and discounted product prices', function () {
    $product = Product::create([
        'name' => 'Garrafa em promoção',
        'slug' => 'garrafa-promocao',
        'price' => 29.99,
        'is_active' => true,
        'stock' => 4,
    ]);
    $promotion = Promotion::create([
        'name' => 'Oferta especial',
        'discount_type' => 'percentagem',
        'discount_value' => 33.34,
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDay(),
    ]);
    $promotion->products()->attach($product);

    $this->get(route('products'))
        ->assertSee('29,99')
        ->assertSee('19,99');
});

test('products can be searched by sku and tags and filtered by price, stock and rating', function () {
    $featured = Product::create([
        'name' => 'Caneca Azul',
        'slug' => 'caneca-azul-filtros',
        'sku' => 'CAN-001',
        'tags' => 'cozinha, presente',
        'price' => 15,
        'is_active' => true,
        'stock' => 4,
    ]);
    $hidden = Product::create([
        'name' => 'Jarro Verde',
        'slug' => 'jarro-verde-filtros',
        'sku' => 'JAR-002',
        'tags' => 'decoracao',
        'price' => 45,
        'is_active' => true,
        'stock' => 0,
    ]);
    $reviewer = User::factory()->create();
    $customer = Customer::create(['name' => $reviewer->name, 'email' => $reviewer->email]);
    $order = Order::create(['order_number' => 'PED-FILTRO-1', 'customer_id' => $customer->id, 'sold_at' => now(), 'total' => 15, 'status' => 'concluida', 'payment_method' => 'mbway']);
    OrderItem::create(['order_id' => $order->id, 'product_id' => $featured->id, 'product_name' => $featured->name, 'quantity' => 1, 'unit_price' => 15, 'total' => 15]);
    ProductReview::create(['product_id' => $featured->id, 'user_id' => $reviewer->id, 'order_id' => $order->id, 'rating' => 5, 'comment' => 'Excelente.']);

    Livewire::test(ProductCatalog::class)
        ->set('search', 'CAN-001')
        ->assertSee('Caneca Azul')
        ->assertDontSee('Jarro Verde')
        ->set('search', '15')
        ->assertSee('Caneca Azul')
        ->assertDontSee('Jarro Verde')
        ->set('search', 'decoracao')
        ->assertSee('Jarro Verde')
        ->set('search', '')
        ->set('availability', 'esgotado')
        ->assertSee('Jarro Verde')
        ->assertDontSee('Caneca Azul')
        ->set('availability', '')
        ->set('maxPrice', '20')
        ->set('minRating', '4')
        ->assertSee('Caneca Azul')
        ->assertDontSee('Jarro Verde');
});

test('a valid coupon is applied to the cart subtotal', function () {
    $product = Product::create([
        'name' => 'Caderno',
        'slug' => 'caderno-cupao',
        'price' => 20,
        'is_active' => true,
        'stock' => 2,
    ]);
    DB::table('coupons')->insert([
        'code' => 'BEMVINDO',
        'type' => 'percentagem',
        'value' => 10,
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDay(),
        'minimum_order_value' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Livewire::test(ProductCatalog::class)
        ->set('cart', [$product->id => 1])
        ->set('couponCode', 'bemvindo')
        ->call('applyCoupon')
        ->assertSet('appliedCouponCode', 'BEMVINDO')
        ->assertSet('couponDiscount', 2);
});

test('the checkout is prefilled with the authenticated user data', function () {
    $user = User::factory()->create(['name' => 'Ana Martins', 'email' => 'ana@example.com']);

    Livewire::actingAs($user)
        ->test(ProductCatalog::class)
        ->assertSet('customerName', 'Ana Martins')
        ->assertSet('customerEmail', 'ana@example.com');
});

test('a visitor can finalize the cart and stock is reduced', function () {
    $product = Product::create([
        'name' => 'Garrafa térmica',
        'slug' => 'garrafa-termica-checkout',
        'price' => 25,
        'is_active' => true,
        'stock' => 3,
    ]);

    Livewire::test(ProductCatalog::class)
        ->set('cart', [$product->id => 2])
        ->set('customerName', 'Maria Silva')
        ->set('customerEmail', 'maria@example.com')
        ->set('customerPhone', '912345678')
        ->set('deliveryAddress', 'Rua Central, 10, 1000-100 Lisboa')
        ->set('paymentMethod', 'mbway')
        ->call('checkout')
        ->assertSet('completedOrderNumber', fn (?string $number): bool => str_starts_with($number ?? '', 'PED-'));

    expect($product->refresh()->stock)->toBe(1);
    $this->assertDatabaseHas('customers', ['email' => 'maria@example.com']);
    $this->assertDatabaseHas('orders', ['total' => 61.5, 'tax' => 11.5, 'shipping' => 0, 'status' => 'pendente']);
    $this->assertDatabaseHas('payments', ['amount' => 61.5, 'method' => 'mbway']);
});

test('the public sales page only shows active products in stock', function () {
    Product::create(['name' => 'Produto disponível', 'slug' => 'produto-disponivel', 'price' => 10, 'is_active' => true, 'stock' => 2]);
    Product::create(['name' => 'Produto inativo', 'slug' => 'produto-inativo', 'price' => 10, 'is_active' => false, 'stock' => 2]);
    Product::create(['name' => 'Produto esgotado', 'slug' => 'produto-esgotado', 'price' => 10, 'is_active' => true, 'stock' => 0]);

    $this->get(route('products'))
        ->assertSee('Produto')
        ->assertDontSee('Produto inativo')
        ->assertDontSee('Produto esgotado');
});
