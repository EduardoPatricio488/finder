<?php

declare(strict_types=1);

use App\Support\WebsiteBuilder\ResponsiveStyles;

it('applies base then tablet then mobile overrides', function (): void {
    $settings = [
        'padding' => 'lg',
        'responsive' => [
            'base' => ['padding' => 'lg', 'align' => 'left'],
            'tablet' => ['padding' => 'md'],
            'mobile' => ['padding' => 'sm'],
        ],
    ];

    expect(ResponsiveStyles::forDevice($settings, 'desktop'))->toMatchArray(['padding' => 'lg', 'align' => 'left'])
        ->and(ResponsiveStyles::forDevice($settings, 'tablet'))->toMatchArray(['padding' => 'md', 'align' => 'left'])
        ->and(ResponsiveStyles::forDevice($settings, 'mobile'))->toMatchArray(['padding' => 'sm', 'align' => 'left']);
});

it('writes only allowlisted responsive values', function (): void {
    $settings = [];

    ResponsiveStyles::setForDevice($settings, 'mobile', 'font_size', '1.5rem');
    ResponsiveStyles::setForDevice($settings, 'desktop', 'padding', 'xl');
    ResponsiveStyles::setForDevice($settings, 'mobile', 'arbitrary_css', 'body{display:none}');

    expect($settings['responsive']['mobile']['font_size'])->toBe('1.5rem')
        ->and($settings['responsive']['base']['padding'])->toBe('xl')
        ->and($settings['responsive']['mobile'])->not->toHaveKey('arbitrary_css');
});

it('produces sanitized css declarations', function (): void {
    $settings = [
        'responsive' => [
            'base' => [
                'font_size' => '1.5rem',
                'padding' => '24px',
                'align' => 'center',
                'visibility' => 'visible',
            ],
        ],
    ];

    expect(ResponsiveStyles::cssForNode($settings))->toMatchArray([
        'font-size' => '1.5rem',
        'padding' => '24px',
        'text-align' => 'center',
    ]);
});

it('rejects unsafe css values', function (): void {
    $settings = [
        'responsive' => [
            'base' => [
                'padding' => 'expression(alert(1))',
                'font_size' => 'url(javascript:alert(1))',
            ],
        ],
    ];

    expect(ResponsiveStyles::cssForNode($settings))->toBe([]);
});
