<?php

return [
    'required' => 'O campo :attribute é obrigatório.',
    'string' => 'O campo :attribute tem de ser um texto.',
    'max' => [
        'string' => 'O campo :attribute não pode ter mais de :max caracteres.',
        'numeric' => 'O campo :attribute não pode ser superior a :max.',
        'file' => 'O ficheiro :attribute não pode ter mais de :max kilobytes.',
        'array' => 'O campo :attribute não pode ter mais de :max itens.',
    ],
    'numeric' => 'O campo :attribute tem de ser um número.',
    'integer' => 'O campo :attribute tem de ser um número inteiro.',
    'boolean' => 'O campo :attribute tem de ser verdadeiro ou falso.',
    'email' => 'O campo :attribute tem de ser um endereço de email válido.',
    'image' => 'O campo :attribute tem de ser uma imagem.',
    'min' => [
        'numeric' => 'O campo :attribute tem de ser pelo menos :min.',
        'string' => 'O campo :attribute tem de ter pelo menos :min caracteres.',
    ],
    'in' => 'O valor selecionado para :attribute é inválido.',
    'lt' => [
        'numeric' => 'O campo :attribute tem de ser inferior a :value.',
    ],
    'attributes' => [
        'productName' => 'nome do produto',
        'productDescription' => 'descrição',
        'productPrice' => 'preço',
        'productCategoryId' => 'categoria',
        'productStock' => 'stock',
        'productMinimumStock' => 'stock mínimo',
        'productImage' => 'imagem',
        'productIsActive' => 'produto ativo',
    ],
];
