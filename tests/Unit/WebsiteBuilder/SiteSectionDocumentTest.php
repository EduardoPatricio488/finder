<?php

declare(strict_types=1);

use App\Models\SiteSection;
use App\Support\WebsiteBuilder\BuilderDocument;
use App\Support\WebsiteBuilder\BuilderNodeId;
use App\Support\WebsiteBuilder\SiteSectionDocument;

it('converts a legacy section to valid v2', function (): void {
    $section = new SiteSection([
        'site_page_id' => 7,
        'type' => 'hero',
        'label' => 'Bem-vindo',
        'content' => [
            'title' => 'Olá',
            'subtitle' => 'Descrição',
            'button_label' => 'Saber mais',
            'button_url' => '/contactos',
        ],
        'settings' => ['align' => 'center'],
        'sort_order' => 0,
        'is_visible' => true,
    ]);
    $section->id = 42;
    $document = SiteSectionDocument::fromSection($section);

    expect($document['schema_version'])->toBe(2)
        ->and(BuilderDocument::isValid($document))->toBeTrue()
        ->and($document['nodes'][0]['id'])->toBe(BuilderNodeId::stable('container', 'site-section:42'));
});

it('keeps ids stable for the same section', function (): void {
    $section = new SiteSection([
        'site_page_id' => 7,
        'type' => 'text',
        'content' => ['title' => 'Título', 'body' => 'Conteúdo'],
        'settings' => [],
    ]);
    $section->id = 99;

    expect(SiteSectionDocument::fromSection($section))->toBe(SiteSectionDocument::fromSection($section));
});

it('migrates rich legacy sections into editable v2 elements', function (): void {
    $section = new SiteSection([
        'site_page_id' => 7,
        'type' => 'feature_grid',
        'label' => 'Benefícios',
        'content' => [
            'title' => 'Porque escolher-nos',
            'items' => [
                ['title' => 'Rápido', 'description' => 'Entrega simples.'],
                ['title' => 'Seguro', 'description' => 'Proteção integrada.'],
            ],
        ],
        'settings' => [],
    ]);
    $section->id = 77;

    $document = SiteSectionDocument::fromSection($section);
    $children = $document['nodes'][0]['children'];

    expect($children)->toHaveCount(5)
        ->and(array_column($children, 'type'))->toBe(['heading', 'heading', 'text', 'heading', 'text'])
        ->and(BuilderDocument::isValid($document))->toBeTrue();
});

it('preserves unknown data and stores the normalized v2 document alongside legacy data', function (): void {
    $section = new SiteSection([
        'site_page_id' => 3,
        'type' => 'unknown_section_type',
        'label' => 'Legacy',
        'content' => ['custom_value' => 'keep', 'nested' => ['x' => true]],
        'settings' => ['custom_setting' => 'keep'],
        'sort_order' => 4,
        'is_visible' => false,
    ]);
    $section->id = 123;
    $document = SiteSectionDocument::fromSection($section);
    $data = SiteSectionDocument::toSectionData($document);

    expect($document['nodes'][0]['settings']['section_type_known'])->toBeFalse()
        ->and($data[0]['content'])->toBe($section->content)
        ->and($data[0]['settings'])->toMatchArray(['custom_setting' => 'keep'])
        ->and($data[0]['settings']['builder_document'])->toBe($document)
        ->and($data[0]['type'])->toBe('unknown_section_type')
        ->and($data[0]['id'])->toBe(123);
});

it('handles empty legacy data without inventing content', function (): void {
    $section = new SiteSection(['type' => 'text', 'content' => [], 'settings' => []]);
    $document = SiteSectionDocument::fromSection($section);
    $data = SiteSectionDocument::toSectionData($document);

    expect(BuilderDocument::isValid($document))->toBeTrue()
        ->and($document['nodes'][0]['children'])->toBe([])
        ->and($data[0]['content'])->toBe([])
        ->and($data[0]['settings']['builder_document'])->toBe($document);
});

it('rejects invalid v2 documents through the existing validator', function (): void {
    $document = [
        'schema_version' => 2,
        'nodes' => [[
            'id' => 'cnt_0000000000000001',
            'type' => 'container',
            'settings' => [],
            'children' => [[
                'id' => 'el_0000000000000001',
                'type' => 'not-registered',
                'content' => [],
                'settings' => [],
            ]],
        ]],
    ];

    expect($document)->and(BuilderDocument::isValid($document))->toBeFalse();
    expect(fn (): array => BuilderDocument::normalize($document))->toThrow(\InvalidArgumentException::class);
});
