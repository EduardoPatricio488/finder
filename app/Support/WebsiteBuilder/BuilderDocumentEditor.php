<?php

declare(strict_types=1);

namespace App\Support\WebsiteBuilder;

use InvalidArgumentException;

final class BuilderDocumentEditor
{
    public static function addElement(array $document, string $parentId, string $type, ?array $content = null, ?array $settings = null): array
    {
        BuilderDocument::validate($document);
        $definition = ElementRegistry::get($type);
        $document = BuilderDocument::normalize($document);
        $element = [
            'id' => BuilderNodeId::generate($type),
            'type' => $type,
            'content' => $content ?? $definition['default_content'],
            'settings' => $settings ?? $definition['default_settings'],
        ];

        $found = self::walk($document['nodes'], function (array &$node) use ($parentId, $element): bool {
            if (($node['id'] ?? null) !== $parentId) {
                return false;
            }
            if (($node['type'] ?? null) !== 'container') {
                throw new InvalidArgumentException('Elements can only be inserted into containers.');
            }
            $node['children'][] = $element;

            return true;
        });

        if (! $found) {
            throw new InvalidArgumentException("Unknown builder node [{$parentId}].");
        }

        return BuilderDocument::normalize($document);
    }

    public static function updateElement(array $document, string $elementId, ?array $content = null, ?array $settings = null): array
    {
        BuilderDocument::validate($document);
        $document = BuilderDocument::normalize($document);
        $found = self::walk($document['nodes'], function (array &$node) use ($elementId, $content, $settings): bool {
            if (($node['id'] ?? null) !== $elementId) {
                return false;
            }
            if (($node['type'] ?? null) === 'container') {
                throw new InvalidArgumentException('Container nodes cannot be edited as elements.');
            }
            if ($content !== null) {
                $node['content'] = $content;
            }
            if ($settings !== null) {
                $node['settings'] = $settings;
            }

            return true;
        });

        if (! $found) {
            throw new InvalidArgumentException("Unknown builder node [{$elementId}].");
        }

        return BuilderDocument::normalize($document);
    }

    public static function removeElement(array $document, string $elementId): array
    {
        BuilderDocument::validate($document);
        $document = BuilderDocument::normalize($document);
        $removed = self::removeFromChildren($document['nodes'], $elementId);
        if (! $removed) {
            throw new InvalidArgumentException("Unknown builder element [{$elementId}].");
        }

        return BuilderDocument::normalize($document);
    }

    public static function moveElement(array $document, string $elementId, int $offset): array
    {
        BuilderDocument::validate($document);
        $document = BuilderDocument::normalize($document);
        if ($offset === 0) {
            return $document;
        }

        $moved = false;
        self::walk($document['nodes'], function (array &$node) use ($elementId, $offset, &$moved): bool {
            if (($node['type'] ?? null) !== 'container') {
                return false;
            }
            foreach ($node['children'] as $index => $child) {
                if (($child['id'] ?? null) !== $elementId) {
                    continue;
                }
                $target = $index + $offset;
                if (! isset($node['children'][$target])) {
                    return true;
                }
                [$node['children'][$index], $node['children'][$target]] = [$node['children'][$target], $node['children'][$index]];
                $moved = true;

                return true;
            }

            return false;
        });

        if (! $moved) {
            throw new InvalidArgumentException("Builder element [{$elementId}] cannot be moved.");
        }

        return BuilderDocument::normalize($document);
    }

    private static function walk(array &$nodes, callable $callback): bool
    {
        foreach ($nodes as &$node) {
            if ($callback($node)) {
                return true;
            }
            if (! empty($node['children']) && self::walk($node['children'], $callback)) {
                return true;
            }
        }

        return false;
    }

    private static function removeFromChildren(array &$nodes, string $elementId): bool
    {
        foreach ($nodes as $index => &$node) {
            if (! empty($node['children'])) {
                foreach ($node['children'] as $childIndex => $child) {
                    if (($child['id'] ?? null) === $elementId) {
                        array_splice($node['children'], $childIndex, 1);

                        return true;
                    }
                }
                if (self::removeFromChildren($node['children'], $elementId)) {
                    return true;
                }
            }
        }

        return false;
    }
}
