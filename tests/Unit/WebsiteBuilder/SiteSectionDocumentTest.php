<?php

declare(strict_types=1);

use App\Models\SiteSection;
use App\Support\WebsiteBuilder\BuilderDocument;
use App\Support\WebsiteBuilder\BuilderNodeId;
use App\Support\WebsiteBuilder\SiteSectionDocument;

it('converts a legacy section to valid v2', function (): void {
    $section = new SiteSection(['site_page_id' => 7, 'type' => 'hero', 'label' => 'Bem-vindo', 'content' => ['title' => 'Olá', 'subtitle' => 'Descrição', 'button_label' => 'Saber mais', 'button_url' => '/contactos'], 'settings' => ['align' => 'center'], 'sort_order' => 0, 'is_visible' => true]);
    $section->id = 42;
    $document = SiteSectionDocument::fromSection($section);
    expect($document['schema_version'])->toBe(2)->and(BuilderDocument::isValid($document))->toBeTrue();

    expect($document['nodes'][0]['children'])->toHaveCount(3);
});

it('keeps ids stable for the same section', function (): void {
    $section = new SiteSection(['site_page_id' => 7, 'type' => 'text', 'label' => 'Texto', 'content' => ['title' => 'Título', 'body' => 'Corpo'], 'settings' => [], 'sort_order' => 0, 'is_visible' => true]);
    $section->id = 42;

    $first = SiteSectionDocument::fromSection($section);
    $second = SiteSectionDocument::fromSection($section);

    expect($second)->toEqual($first);
});

it('migrates rich legacy sections into editable v2 elements', function (): void {
    $section = new SiteSection(['site_page_id' => 7, 'type' => 'testimonials', 'label' => 'Testemunhos', 'content' => ['title' => 'Clientes', 'items' => [['name' => 'Ana', 'quote' => 'Excelente.']]], 'settings' => [], 'sort_order' => 0, 'is_visible' => true]);
    $section->id = 42;

    $document = SiteSectionDocument::fromSection($section);
    $quote = collect($document['nodes'][0]['children'])->firstWhere('type', 'quote');

    expect($quote['content']['text'])->toBe('Excelente.');
});

it('preserves unknown data and stores the normalized v2 document alongside legacy data', function (): void {
    $section = new SiteSection(['site_page_id' => 7, 'type' => 'card', 'label' => 'Card', 'content' => ['title' => 'Título', 'custom' => 'keep-me'], 'settings' => ['custom_setting' => 'keep-me'], 'sort_order' => 0, 'is_visible' => true]);
    $section->id = 42;

    $document = SiteSectionDocument::fromSection($section);
    $data = SiteSectionDocument::toSectionData($document, $section->content, $section->settings);

    expect($data['content']['custom'])->toBe('keep-me')
        ->and($data['settings']['custom_setting'])->toBe('keep-me')
        ->and($data['settings']['builder_document']['schema_version'])->toBe(2);
});

it('handles empty legacy data without inventing content', function (): void {
    $section = new SiteSection(['site_page_id' => 7, 'type' => 'text', 'label' => 'Texto', 'content' => [], 'settings' => [], 'sort_order' => 0, 'is_visible' => true]);
    $section->id = 42;

    $document = SiteSectionDocument::fromSection($section);

    expect($document['nodes'][0]['children'][0]['content'])->toBe(['text' => '']);
});

it('rejects invalid v2 documents through the existing validator', function (): void {
    $section = new SiteSection(['site_page_id' => 7, 'type' => 'text', 'label' => 'Texto', 'content' => [], 'settings' => ['builder_document' => ['schema_version' => 999, 'nodes' => []]], 'sort_order' => 0, 'is_visible' => true]);
    $section->id = 42;

    expect(fn () => SiteSectionDocument::fromSection($section))->toThrow(\InvalidArgumentException::class);
});
