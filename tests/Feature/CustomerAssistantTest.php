<?php

use App\Livewire\CustomerAssistant;
use App\Models\Product;
use Livewire\Livewire;

test('the customer assistant recommends real products within the requested budget', function () {
    $withinBudget = Product::create([
        'name' => 'Caneca presente',
        'slug' => 'caneca-presente-assistente',
        'tags' => 'presente, cozinha',
        'price' => 19.99,
        'is_active' => true,
        'stock' => 3,
    ]);
    Product::create([
        'name' => 'Vaso premium',
        'slug' => 'vaso-premium-assistente',
        'price' => 45,
        'is_active' => true,
        'stock' => 3,
    ]);

    Livewire::test(CustomerAssistant::class)
        ->set('question', 'Preciso de uma prenda até 30 €')
        ->call('ask')
        ->assertSee('Tenho 1 sugestões até € 30,00:')
        ->assertSee($withinBudget->name)
        ->assertDontSee('Vaso premium');
});

test('the customer assistant does not recommend unavailable products', function () {
    Product::create([
        'name' => 'Produto esgotado',
        'slug' => 'produto-esgotado-assistente',
        'price' => 10,
        'is_active' => true,
        'stock' => 0,
    ]);

    Livewire::test(CustomerAssistant::class)
        ->set('question', 'Quero uma prenda até 30 €')
        ->call('ask')
        ->assertSee('não encontrei sugestões disponíveis');
});
