<?php

declare(strict_types=1);

use App\Models\Site;
use App\Support\WebsiteBuilder\BuilderDocumentEditor;
use App\Support\WebsiteBuilder\SiteSectionDocument;
use App\Support\WebsiteBuilder\WebsiteDocumentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

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
    $element = collect($document['nodes'][0]['children'])->firstWhere('type', 'text');

    expect($element)->toBeArray();

    $elementId = $element['id'];
    $document = BuilderDocumentEditor::updateElement($document, $elementId, ['text' => 'Actualizado']);

    $service->write($section, $document);
    $section->refresh();

    $savedDocument = $section->settings['builder_document'];
    $savedTextElement = collect($savedDocument['nodes'][0]['children'])
        ->firstWhere('type', 'text');
    $reloadedDocument = SiteSectionDocument::fromSection($section);

    expect($savedDocument)->toBeArray()
        ->and($savedTextElement['content']['text'])->toBe('Actualizado')
        ->and($reloadedDocument['nodes'][0]['children'])->toEqual($savedDocument['nodes'][0]['children']);
});
