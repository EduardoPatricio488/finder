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

it('writes responsive values into the correct breakpoint bucket', function (): void {
    $settings = [];

    ResponsiveStyles::setForDevice($settings, 'mobile', 'font_size', 'sm');
    ResponsiveStyles::setForDevice($settings, 'desktop', 'padding', 'xl');

    expect($settings['responsive']['mobile']['font_size'])->toBe('sm')
        ->and($settings['responsive']['base']['padding'])->toBe('xl');
});
