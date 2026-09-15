<?php

declare(strict_types=1);

namespace App\Support\WebsiteBuilder;

final class ResponsiveStyles
{
    /** @param array<string,mixed> $settings */
    public static function forDevice(array $settings, string $device): array
    {
        $device = in_array($device, ['desktop', 'tablet', 'mobile'], true) ? $device : 'desktop';
        $base = is_array($settings['responsive']['base'] ?? null) ? $settings['responsive']['base'] : $settings;
        $overrides = is_array($settings['responsive'][$device] ?? null) ? $settings['responsive'][$device] : [];

        if ($device === 'desktop') {
            return array_replace($base, $overrides);
        }

        $tablet = is_array($settings['responsive']['tablet'] ?? null) ? $settings['responsive']['tablet'] : [];
        if ($device === 'tablet') {
            return array_replace($base, $tablet);
        }

        return array_replace($base, $tablet, $overrides);
    }
}
