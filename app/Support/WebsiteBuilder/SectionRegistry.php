<?php

declare(strict_types=1);

namespace App\Support\WebsiteBuilder;

use InvalidArgumentException;

final class SectionRegistry
{
    /** @var list<string> */
    private const TYPES = [
        'hero', 'text', 'image', 'button', 'feature_grid', 'card', 'testimonials', 'faq',
        'gallery', 'contact_form', 'product_grid', 'product_card', 'pricing', 'blog_posts',
        'social_links', 'video', 'map', 'newsletter', 'cta',
    ];

    public static function all(): array
    {
        $definitions = [];

        foreach (self::TYPES as $type) {
            $definitions[$type] = [
                'type' => $type,
                'label' => str_replace('_', ' ', ucfirst($type)),
                'category' => 'Section',
                'icon' => 'layout',
                'description' => 'Secção existente do Finder.',
                'allowed_parents' => ['page'],
                'schema' => [
                    'content' => 'legacy-section-content',
                    'settings' => 'legacy-section-settings',
                ],
            ];
        }

        return $definitions;
    }

    public static function has(string $type): bool
    {
        return in_array($type, self::TYPES, true);
    }

    public static function get(string $type): array
    {
        return self::all()[$type] ?? throw new InvalidArgumentException("Unknown website section type [{$type}].");
    }

    /** @return list<string> */
    public static function types(): array
    {
        return self::TYPES;
    }
}
