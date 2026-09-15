<?php

declare(strict_types=1);

use App\Support\WebsiteBuilder\BuilderDocument;
use App\Support\WebsiteBuilder\BuilderNodeId;
use App\Support\WebsiteBuilder\ElementRegistry;

it('exposes the professional element catalogue', function (): void {
    expect(ElementRegistry::types())->toContain('heading', 'text', 'image', 'button', 'video', 'gallery', 'form', 'faq', 'social_links');
    foreach (ElementRegistry::types() as $type) {
        $definition = ElementRegistry::get($type);
        expect($definition['allowed_parents'])->toContain('container');
        expect(ElementRegistry::defaults($type))->toHaveKeys(['content', 'settings']);
    }
});

it('accepts every registered element in a valid document', function (): void {
    $children = [];
    foreach (ElementRegistry::types() as $index => $type) {
        $defaults = ElementRegistry::defaults($type);
        $children[] = [
            'id' => BuilderNodeId::stable($type, 'registry-test:'.$index),
            'type' => $type,
            'content' => $defaults['content'],
            'settings' => $defaults['settings'],
        ];
    }

    $document = BuilderDocument::normalize([
        'schema_version' => BuilderDocument::VERSION,
        'nodes' => [[
            'id' => BuilderNodeId::stable('container', 'registry-test'),
            'type' => 'container',
            'settings' => [],
            'children' => $children,
        ]],
    ]);

    expect(BuilderDocument::isValid($document))->toBeTrue();
});
