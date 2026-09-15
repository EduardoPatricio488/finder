<?php

declare(strict_types=1);

namespace App\Support\WebsiteBuilder;

final class ResponsiveStyles
{
    private const DEVICES = ['desktop', 'tablet', 'mobile'];

    private const ALLOWED_KEYS = ['font_size', 'padding', 'margin', 'align', 'width', 'visibility'];

    /** @param array<string,mixed> $settings @return array<string,mixed> */
    public static function forDevice(array $settings, string $device): array
    {
        $device = self::normalizeDevice($device);
        $responsive = is_array($settings['responsive'] ?? null) ? $settings['responsive'] : [];
        $base = is_array($responsive['base'] ?? null) ? $responsive['base'] : $settings;
        $tablet = is_array($responsive['tablet'] ?? null) ? $responsive['tablet'] : [];
        $mobile = is_array($responsive['mobile'] ?? null) ? $responsive['mobile'] : [];

        return match ($device) {
            'tablet' => array_replace($base, $tablet),
            'mobile' => array_replace($base, $tablet, $mobile),
            default => array_replace($base, is_array($responsive['desktop'] ?? null) ? $responsive['desktop'] : []),
        };
    }

    /** @param array<string,mixed> $settings */
    public static function setForDevice(array &$settings, string $device, string $key, mixed $value): void
    {
        $device = self::normalizeDevice($device);
        if (! in_array($key, self::ALLOWED_KEYS, true)) {
            return;
        }
        if (! isset($settings['responsive']) || ! is_array($settings['responsive'])) {
            $settings['responsive'] = [];
        }
        $bucket = $device === 'desktop' ? 'base' : $device;
        if (! isset($settings['responsive'][$bucket]) || ! is_array($settings['responsive'][$bucket])) {
            $settings['responsive'][$bucket] = [];
        }
        $settings['responsive'][$bucket][$key] = self::sanitizeValue($key, $value);
    }

    /** @param array<string,mixed> $settings @return array<string,string> */
    public static function cssForNode(array $settings, string $device = 'desktop'): array
    {
        $styles = self::forDevice($settings, $device);
        $css = [];
        foreach (['font_size' => 'font-size', 'padding' => 'padding', 'margin' => 'margin', 'width' => 'width', 'align' => 'text-align'] as $key => $property) {
            $value = $styles[$key] ?? null;
            if (is_string($value) && $value !== '') {
                $safe = self::sanitizeCssValue($key, $value);
                if ($safe !== '') {
                    $css[$property] = $safe;
                }
            }
        }
        if (($styles['visibility'] ?? null) === 'hidden') {
            $css['display'] = 'none';
        }
        return $css;
    }

    private static function normalizeDevice(string $device): string
    {
        return in_array($device, self::DEVICES, true) ? $device : 'desktop';
    }

    private static function sanitizeValue(string $key, mixed $value): mixed
    {
        if ($key === 'visibility') {
            return in_array($value, ['visible', 'hidden'], true) ? $value : 'visible';
        }
        if ($key === 'align') {
            return in_array($value, ['left', 'center', 'right'], true) ? $value : 'left';
        }
        return is_scalar($value) ? mb_substr((string) $value, 0, 100) : '';
    }

    private static function sanitizeCssValue(string $key, string $value): string
    {
        if ($key === 'align') {
            return in_array($value, ['left', 'center', 'right'], true) ? $value : '';
        }
        return preg_match('/^(?:0|auto|none|(?:-?[0-9]+(?:\.[0-9]+)?)(?:px|rem|em|%|vw|vh))$/', trim($value)) === 1 ? trim($value) : '';
    }
}
