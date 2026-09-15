<?php

declare(strict_types=1);

use App\Support\WebsiteBuilder\BuilderDocument;
use App\Support\WebsiteBuilder\BuilderDocumentEditor;
use App\Support\WebsiteBuilder\BuilderNodeId;

function editorDocument(): array
{
    return BuilderDocument::normalize([
        'schema_version' => 2,
        'nodes' => [[
            'id' => BuilderNodeId::generate('container'),
            'type' => 'container',
            'settings' => [],
            'children' => [],
        ]],
    ]);
}

it('adds an element using registry defaults', function (): void {
    $document = editorDocument();
    $container = $document['nodes'][0]['id'];
    $updated = BuilderDocumentEditor::addElement($document, $container, 'heading');

    expect($updated['nodes'][0]['children'])->toHaveCount(1)
        ->and($updated['nodes'][0]['children'][0]['type'])->toBe('heading')
        ->and(BuilderDocument::isValid($updated))->toBeTrue();
});

it('updates, moves and removes elements without changing their ids', function (): void {
    $document = editorDocument();
    $container = $document['nodes'][0]['id'];
    $document = BuilderDocumentEditor::addElement($document, $container, 'heading', ['text' => 'Primeiro']);
    $document = BuilderDocumentEditor::addElement($document, $container, 'text', ['text' => 'Segundo']);
    $heading = $document['nodes'][0]['children'][0]['id'];
    $text = $document['nodes'][0]['children'][1]['id'];

    $document = BuilderDocumentEditor::updateElement($document, $heading, ['text' => 'Atualizado']);
    expect($document['nodes'][0]['children'][0]['content']['text'])->toBe('Atualizado');

    $document = BuilderDocumentEditor::moveElement($document, $heading, 1);
    expect($document['nodes'][0]['children'][0]['id'])->toBe($text)
        ->and($document['nodes'][0]['children'][1]['id'])->toBe($heading);

    $document = BuilderDocumentEditor::removeElement($document, $text);
    expect($document['nodes'][0]['children'])->toHaveCount(1)
        ->and($document['nodes'][0]['children'][0]['id'])->toBe($heading)
        ->and(BuilderDocument::isValid($document))->toBeTrue();
});

it('does not fail when moving an element beyond its container boundary', function (): void {
    $document = editorDocument();
    $container = $document['nodes'][0]['id'];
    $document = BuilderDocumentEditor::addElement($document, $container, 'heading');
    $elementId = $document['nodes'][0]['children'][0]['id'];

    $updated = BuilderDocumentEditor::moveElement($document, $elementId, -1);

    expect($updated)->toEqual($document);
});
