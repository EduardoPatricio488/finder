<?php

declare(strict_types=1);

namespace App\Support\WebsiteBuilder;

use InvalidArgumentException;

final class BuilderDocument
{
    public const VERSION = 2;

    public static function normalize(array $document): array
    {
        $version = $document['schema_version'] ?? null;
        if ($version !== self::VERSION) {
            throw new InvalidArgumentException('Unsupported builder document schema version.');
        }

        $nodes = $document['nodes'] ?? null;
        if (! is_array($nodes)) {
            throw new InvalidArgumentException('Builder documents require nodes.');
        }

        $normalized = [
            'schema_version' => self::VERSION,
            'nodes' => array_values(array_map(static fn (mixed $node): array => self::normalizeNode($node), $nodes)),
        ];

        self::validate($normalized);

        return $normalized;
    }

    public static function isValid(array $document): bool
    {
        try {
            self::validate($document);

            return true;
        } catch (InvalidArgumentException) {
            return false;
        }
    }

    public static function validate(array $document): void
    {
        if (($document['schema_version'] ?? null) !== self::VERSION) {
            throw new InvalidArgumentException('Unsupported builder document schema version.');
        }
        if (! isset($document['nodes']) || ! is_array($document['nodes'])) {
            throw new InvalidArgumentException('Builder documents require nodes.');
        }

        $ids = [];
        foreach ($document['nodes'] as $node) {
            self::validateNode($node, null, $ids);
        }
    }

    private static function normalizeNode(mixed $node): array
    {
        if (! is_array($node)) {
            throw new InvalidArgumentException('Builder document nodes must be arrays.');
        }

        $type = $node['type'] ?? null;
        if (! is_string($type)) {
            throw new InvalidArgumentException('Builder document nodes require a type.');
        }

        if ($type === 'container') {
            $normalized = [
                'id' => is_string($node['id'] ?? null) ? $node['id'] : BuilderNodeId::generate('container'),
                'type' => 'container',
                'settings' => is_array($node['settings'] ?? null) ? $node['settings'] : [],
                'children' => array_values(array_map(static fn (mixed $child): array => self::normalizeNode($child), is_array($node['children'] ?? null) ? $node['children'] : [])),
            ];

            return $normalized;
        }

        $defaults = ElementRegistry::defaults($type);
        $content = is_array($node['content'] ?? null) ? $node['content'] : [];
        $settings = is_array($node['settings'] ?? null) ? $node['settings'] : [];

        return [
            'id' => is_string($node['id'] ?? null) ? $node['id'] : BuilderNodeId::generate($type),
            'type' => $type,
            'content' => array_replace($defaults['content'], $content),
            'settings' => array_replace($defaults['settings'], $settings),
        ];
    }

    private static function validateNode(mixed $node, ?string $parentType, array &$ids): void
    {
        if (! is_array($node)) {
            throw new InvalidArgumentException('Builder document nodes must be arrays.');
        }

        $type = $node['type'] ?? null;
        $id = $node['id'] ?? null;
        if (! is_string($type) || ! is_string($id)) {
            throw new InvalidArgumentException('Builder document nodes require type and id.');
        }
        if (isset($ids[$id])) {
            throw new InvalidArgumentException('Builder document contains duplicate node IDs.');
        }
        $ids[$id] = true;

        if ($type === 'container') {
            if (! BuilderNodeId::isValid($id, 'container')) {
                throw new InvalidArgumentException('Container node ID is invalid.');
            }
            if (! is_array($node['settings'] ?? null) || ! is_array($node['children'] ?? null)) {
                throw new InvalidArgumentException('Container nodes require settings and children arrays.');
            }
            foreach ($node['children'] as $child) {
                self::validateNode($child, 'container', $ids);
            }

            return;
        }

        if (! ElementRegistry::has($type)) {
            throw new InvalidArgumentException("Unknown website element type [{$type}].");
        }
        if ($parentType !== 'container') {
            throw new InvalidArgumentException('Elements must belong to a container.');
        }
        if (! BuilderNodeId::isValid($id, $type)) {
            throw new InvalidArgumentException('Element node ID does not match its type.');
        }

        $definition = ElementRegistry::get($type);
        if (! in_array($parentType, $definition['allowed_parents'], true)) {
            throw new InvalidArgumentException('Element parent is not allowed.');
        }
        if (! isset($node['content']) || ! is_array($node['content'])) {
            throw new InvalidArgumentException('Elements require an array of content.');
        }
        if (! isset($node['settings']) || ! is_array($node['settings'])) {
            throw new InvalidArgumentException('Elements require an array of settings.');
        }

        self::validateSchemaMap($node['content'], $definition['schema']['content']);
        self::validateSchemaMap($node['settings'], $definition['schema']['settings']);

        if (array_key_exists('children', $node)) {
            throw new InvalidArgumentException('Elements cannot contain children.');
        }
    }

    private static function validateSchemaMap(array $values, mixed $schema): void
    {
        if ($schema === [] || $schema === 'legacy-section-content' || $schema === 'legacy-section-settings') {
            return;
        }
        if (! is_array($schema)) {
            return;
        }

        foreach ($schema as $key => $rule) {
            if (! array_key_exists($key, $values)) {
                continue;
            }

            $value = $values[$key];
            $valid = match ($rule) {
                'string' => is_string($value),
                'array' => is_array($value),
                'integer' => is_int($value),
                'boolean' => is_bool($value),
                default => is_array($rule) && in_array($value, $rule, true),
            };

            if (! $valid) {
                throw new InvalidArgumentException("Document field [{$key}] does not match its schema.");
            }
        }
    }
}
