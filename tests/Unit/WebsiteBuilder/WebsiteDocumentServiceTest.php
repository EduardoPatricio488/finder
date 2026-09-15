<?php

declare(strict_types=1);

use App\Models\Site;
use App\Support\WebsiteBuilder\BuilderDocumentEditor;
use App\Support\WebsiteBuilder\WebsiteDocumentService;
use App\Support\WebsiteBuilder\SiteSectionDocument;

it('reads and writes a builder document back to the same section', function () {
    $site = Site::factory()->create();
    $page = $site->pages()->create([
        'name' => 'Home',
        'slug' => 'home',
        'status' => 'draft',
        'is_homepage' => true,
        'sort_order' => 0,
        'seo' => [],
    ]);
    $section = $page->sections()->create([
        'type' => 'text',
        'label' => 'Texto',
        'content' => ['title' => 'Original', 'body' => 'Texto'],
        'settings' => [],
        'sort_order' => 0,
        'is_visible' => true,
    ]);

    $service = app(WebsiteDocumentService::class);
    $document = $service->read($section);
    $elementId = $document['nodes'][0]['children'][0]['id'];
    $document = BuilderDocumentEditor::updateElement($document, $elementId, ['text' => 'Actualizado']);

    $service->write($section, $document);
    $section->refresh();

    expect($section->settings['builder_document'])->toEqual(SiteSectionDocument::fromSection($section))
        ->and($section->settings['builder_document']['nodes'][0]['children'][0]['content']['text'])->toBe('Actualizado');
});
