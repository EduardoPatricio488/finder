<?php

declare(strict_types=1);

use App\Support\WebsiteBuilder\BuilderDocument;
use App\Support\WebsiteBuilder\BuilderNodeId;
use App\Support\WebsiteBuilder\ElementRegistry;
use App\Support\WebsiteBuilder\SectionRegistry;

it('registers the initial website elements', function () {
    expect(ElementRegistry::types())->toBe(['heading', 'text', 'image', 'button', 'divider', 'spacer'])
        ->and(ElementRegistry::has('heading'))->toBeTrue()
        ->and(ElementRegistry::has('button'))->toBeTrue()
        ->and(ElementRegistry::has('unknown'))->toBeFalse();

    expect(ElementRegistry::get('heading'))
        ->toHaveKeys(['type', 'label', 'category', 'icon', 'description', 'default_content', 'default_settings', 'allowed_parents', 'schema']);
});

it('registers every current website section type', function () {
    $expected = [
        'hero', 'text', 'image', 'button', 'feature_grid', 'card', 'testimonials', 'faq',
        'gallery', 'contact_form', 'product_grid', 'product_card', 'pricing', 'blog_posts',
        'social_links', 'video', 'map', 'newsletter', 'cta',
    ];

    expect(SectionRegistry::types())->toBe($expected)
        ->and(SectionRegistry::has('hero'))->toBeTrue()
        ->and(SectionRegistry::has('cta'))->toBeTrue()
        ->and(SectionRegistry::has('unknown'))->toBeFalse();
});

it('generates stable typed node ids', function () {
    $container = BuilderNodeId::generate('container');
    $heading = BuilderNodeId::generate('heading');

    expect($container)->toStartWith('cnt_')
        ->and($heading)->toStartWith('el_')
        ->and(BuilderNodeId::isValid($container, 'container'))->toBeTrue()
        ->and(BuilderNodeId::isValid($heading, 'heading'))->toBeTrue()
        ->and($container)->not->toBe($heading);
});

it('normalizes a valid document and applies element defaults', function () {
    $document = BuilderDocument::normalize([
        'schema_version' => 2,
        'nodes' => [
            [
                'type' => 'container',
                'settings' => [],
                'children' => [
                    [
                        'type' => 'heading',
                        'content' => ['text' => 'Título personalizado'],
                    ],
                ],
            ],
        ],
    ]);

    expect($document['schema_version'])->toBe(2)
        ->and($document['nodes'])->toHaveCount(1)
        ->and($document['nodes'][0]['id'])->toStartWith('cnt_')
        ->and($document['nodes'][0]['children'][0]['id'])->toStartWith('el_')
        ->and($document['nodes'][0]['children'][0]['content']['text'])->toBe('Título personalizado')
        ->and($document['nodes'][0]['children'][0]['settings']['align'])->toBe('left')
        ->and(BuilderDocument::isValid($document))->toBeTrue();
});

it('preserves existing valid ids and user data during normalization', function () {
    $document = BuilderDocument::normalize([
        'schema_version' => 2,
        'nodes' => [[
            'id' => 'cnt_1234567890abcdef',
            'type' => 'container',
            'settings' => ['max_width' => '1200px'],
            'children' => [[
                'id' => 'el_abcdef1234567890',
                'type' => 'text',
                'content' => ['text' => 'Conteúdo'],
                'settings' => ['align' => 'center', 'custom' => true],
            ]],
        ]],
    ]);

    expect($document['nodes'][0]['id'])->toBe('cnt_1234567890abcdef')
        ->and($document['nodes'][0]['settings']['max_width'])->toBe('1200px')
        ->and($document['nodes'][0]['children'][0]['id'])->toBe('el_abcdef1234567890')
        ->and($document['nodes'][0]['children'][0]['settings']['custom'])->toBeTrue();
});

it('rejects missing or unsupported schema versions', function () {
    expect(fn () => BuilderDocument::validate(['nodes' => []]))->toThrow(InvalidArgumentException::class)
        ->and(fn () => BuilderDocument::validate(['schema_version' => 1, 'nodes' => []]))->toThrow(InvalidArgumentException::class);
});

it('rejects duplicate ids and unknown element types', function () {
    $duplicate = [
        'schema_version' => 2,
        'nodes' => [
            ['id' => 'cnt_1234567890abcdef', 'type' => 'container', 'settings' => [], 'children' => []],
            ['id' => 'cnt_1234567890abcdef', 'type' => 'container', 'settings' => [], 'children' => []],
        ],
    ];

    $unknown = [
        'schema_version' => 2,
        'nodes' => [[
            'id' => 'cnt_1234567890abcdef',
            'type' => 'container',
            'settings' => [],
            'children' => [[
                'id' => 'el_abcdef1234567890',
                'type' => 'unknown',
                'content' => [],
                'settings' => [],
            ]],
        ]],
    ];

    expect(fn () => BuilderDocument::validate($duplicate))->toThrow(InvalidArgumentException::class)
        ->and(fn () => BuilderDocument::validate($unknown))->toThrow(InvalidArgumentException::class)
        ->and(BuilderDocument::isValid($unknown))->toBeFalse();
});

it('rejects impossible nesting and invalid schema values', function () {
    $nestedContainer = [
        'schema_version' => 2,
        'nodes' => [[
            'id' => 'cnt_1234567890abcdef',
            'type' => 'container',
            'settings' => [],
            'children' => [[
                'id' => 'cnt_abcdef1234567890',
                'type' => 'container',
                'settings' => [],
                'children' => [],
            ]],
        ]],
    ];

    $invalidHeading = [
        'schema_version' => 2,
        'nodes' => [[
            'id' => 'cnt_1234567890abcdef',
            'type' => 'container',
            'settings' => [],
            'children' => [[
                'id' => 'el_abcdef1234567890',
                'type' => 'heading',
                'content' => ['text' => 'Título', 'level' => 'h7'],
                'settings' => ['align' => 'left'],
            ]],
        ]],
    ];

    expect(fn () => BuilderDocument::validate($nestedContainer))->toThrow(InvalidArgumentException::class)
        ->and(fn () => BuilderDocument::validate($invalidHeading))->toThrow(InvalidArgumentException::class);
});
