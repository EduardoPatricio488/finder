<?php

declare(strict_types=1);

namespace App\Support\WebsiteBuilder;

use Illuminate\Support\Str;

final class BuilderNodeId
{
    public static function generate(string $type): string
    {
        return self::prefix($type).'_'.Str::lower(Str::random(16));
    }

    public static function stable(string $type, string $seed): string
    {
        return self::prefix($type).'_'.substr(hash('sha256', $seed), 0, 16);
    }

    public static function isValid(string $id, ?string $type = null): bool
    {
        $prefix = match ($type) {
            null => '(?:cnt|el)',
            'container' => 'cnt',
            'heading', 'text', 'image', 'button', 'divider', 'spacer' => 'el',
            default => '',
        };

        return $prefix !== '' && preg_match('/^'.$prefix.'_[a-z0-9]{16}$/', $id) === 1;
    }

    private static function prefix(string $type): string
    {
        return match ($type) {
            'container' => 'cnt',
            'heading', 'text', 'image', 'button', 'divider', 'spacer' => 'el',
            default => throw new \InvalidArgumentException("Unsupported document node type [{$type}]."),
        };
    }
}
