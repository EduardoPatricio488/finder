<?php

declare(strict_types=1);

namespace App\Support\WebsiteBuilder;

use InvalidArgumentException;

final class BuilderDocument
{
    public const VERSION = 2;

    /**
     * Normalize a Document v2 structure without silently converting legacy data.
     * Missing node IDs are generated; existing valid IDs and user data are kept.
     */
    public static function normalize(array $input): array
    {
        if (! array_key_exists('schema_version', $input)) {
            throw new InvalidArgumentException('Document schema_version is required.');
        }

        if ($input['schema_version'] !== self::VERSION) {
            throw new InvalidArgumentException('Unsupported document schema version.');
        }

        if (array_key_exists('nodes', $input) && ! is_array($input['nodes'])) {
            throw new InvalidArgumentException('Document nodes must be an array.');
        }

        $document = [
            'schema_version' => self::VERSION,
            'nodes' => [],
        ];

        foreach (($input['nodes'] ?? []) as $node) {
            if (! is_array($node)) {
                throw new InvalidArgumentException('Every document node must be an object-like array.');
            }

            $document['nodes'][] = self::normalizeContainer($node, $document['nodes']);
        }

        self::validate($document);

        return $document;
    }

    public static function validate(array $document): void
    {
        if (($document['schema_version'] ?? null) !== self::VERSION) {
            throw new InvalidArgumentException('Unsupported or missing document schema version.');
        }

        if (! isset($document['nodes']) || ! is_array($document['nodes'])) {
            throw new InvalidArgumentException('Document nodes must be an array.');
        }

        $ids = [];
        foreach ($document['nodes'] as $node) {
            self::validateNode($node, null, $ids);
        }
    }

    public static function isValid(mixed $document): bool
    {
        if (! is_array($document)) {
            return false;
        }

        try {
            self::validate($document);
        } catch (InvalidArgumentException) {
            return false;
        }

        return true;
    }

    /** @param array<int, array<string, mixed>> $siblings */
    private static function normalizeContainer(array $node, array $siblings): array
    {
        $type = $node['type'] ?? null;
        if ($type !== 'container') {
            throw new InvalidArgumentException('Document root nodes must be containers.');
        }

        $id = $node['id'] ?? BuilderNodeId::generate('container');
        if (! is_string($id) || ! BuilderNodeId::isValid($id, 'container')) {
            throw new InvalidArgumentException('Container node IDs are invalid.');
        }

        $settings = $node['settings'] ?? [];
        if (! is_array($settings)) {
            throw new InvalidArgumentException('Container settings must be an array.');
        }

        $children = $node['children'] ?? [];
        if (! is_array($children)) {
            throw new InvalidArgumentException('Container children must be an array.');
        }

        $normalized = [
            'id' => $id,
            'type' => 'container',
            'settings' => $settings,
            'children' => [],
        ];

        foreach ($children as $child) {
            if (! is_array($child)) {
                throw new InvalidArgumentException('Container children must be objects.');
            }

            $normalized['children'][] = self::normalizeElement($child);
        }

        return $normalized;
    }

    private static function normalizeElement(array $node): array
    {
        $type = $node['type'] ?? null;
        if (! is_string($type) || ! ElementRegistry::has($type)) {
            throw new InvalidArgumentException('Unknown document element type.');
        }

        $id = $node['id'] ?? BuilderNodeId::generate($type);
        if (! is_string($id) || ! BuilderNodeId::isValid($id, $type)) {
            throw new InvalidArgumentException('Element node IDs are invalid.');
        }

        $content = $node['content'] ?? ElementRegistry::get($type)['default_content'];
        $settings = $node['settings'] ?? ElementRegistry::get($type)['default_settings'];

        if (! is_array($content) || ! is_array($settings)) {
            throw new InvalidArgumentException('Element content and settings must be arrays.');
        }

        return [
            'id' => $id,
            'type' => $type,
            'content' => $content,
            'settings' => $settings,
        ];
    }

    /** @param array<string, bool> $ids */
    private static function validateNode(mixed $node, ?string $parentType, array &$ids): void
    {
        if (! is_array($node)) {
            throw new InvalidArgumentException('Document nodes must be arrays.');
        }

        $id = $node['id'] ?? null;
        $type = $node['type'] ?? null;

        if (! is_string($id) || ! is_string($type)) {
            throw new InvalidArgumentException('Every node requires a string id and type.');
        }

        if (isset($ids[$id])) {
            throw new InvalidArgumentException('Document node IDs must be unique.');
        }
        $ids[$id] = true;

        if ($type === 'container') {
            if ($parentType !== null) {
                throw new InvalidArgumentException('Containers cannot be nested inside another node.');
            }

            if (! BuilderNodeId::isValid($id, 'container')) {
                throw new InvalidArgumentException('Container node ID does not match its type.');
            }

            if (isset($node['content'])) {
                throw new InvalidArgumentException('Containers cannot define element content.');
            }

            if (! isset($node['settings']) || ! is_array($node['settings'])) {
                throw new InvalidArgumentException('Containers require an array of settings.');
            }

            if (! isset($node['children']) || ! is_array($node['children'])) {
                throw new InvalidArgumentException('Containers require an array of children.');
            }

            foreach ($node['children'] as $child) {
                self::validateNode($child, 'container', $ids);
            }

            return;
        }

        if ($parentType !== 'container' || ! ElementRegistry::has($type)) {
            throw new InvalidArgumentException('Elements must be registered children of a container.');
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
