<?php

declare(strict_types=1);

namespace App\Support\WebsiteBuilder;

final class ResponsiveStyles
{
    /** @param array<string,mixed> $settings @return array<string,mixed> */
    public static function forDevice(array $settings, string $device): array
    {
        $device = in_array($device, ['desktop', 'tablet', 'mobile'], true) ? $device : 'desktop';
        $base = is_array($settings['responsive']['base'] ?? null) ? $settings['responsive']['base'] : $settings;
        $tablet = is_array($settings['responsive']['tablet'] ?? null) ? $settings['responsive']['tablet'] : [];
        $mobile = is_array($settings['responsive']['mobile'] ?? null) ? $settings['responsive']['mobile'] : [];

        return match ($device) {
            'tablet' => array_replace($base, $tablet),
            'mobile' => array_replace($base, $tablet, $mobile),
            default => array_replace($base, is_array($settings['responsive']['desktop'] ?? null) ? $settings['responsive']['desktop'] : []),
        };
    }

    /** @param array<string,mixed> $settings */
    public static function setForDevice(array &$settings, string $device, string $key, mixed $value): void
    {
        if ($key === '' || ! in_array($device, ['desktop', 'tablet', 'mobile'], true)) {
            return;
        }
        if (! isset($settings['responsive']) || ! is_array($settings['responsive'])) {
            $settings['responsive'] = [];
        }
        $bucket = $device === 'desktop' ? 'base' : $device;
        if (! isset($settings['responsive'][$bucket]) || ! is_array($settings['responsive'][$bucket])) {
            $settings['responsive'][$bucket] = [];
        }
        $settings['responsive'][$bucket][$key] = $value;
    }
}
