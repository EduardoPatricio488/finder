<?php

declare(strict_types=1);

namespace App\Support\WebsiteBuilder;

use InvalidArgumentException;

final class ElementRegistry
{
    public static function all(): array
    {
        return [
            'heading' => [
                'type' => 'heading',
                'label' => 'Título',
                'category' => 'Conteúdo',
                'icon' => 'heading',
                'description' => 'Adiciona um título à página.',
                'default_content' => ['text' => 'Novo título', 'level' => 'h2'],
                'default_settings' => ['align' => 'left'],
                'allowed_parents' => ['container'],
                'schema' => [
                    'content' => ['text' => 'string', 'level' => ['h1', 'h2', 'h3', 'h4', 'h5', 'h6']],
                    'settings' => ['align' => ['left', 'center', 'right']],
                ],
            ],
            'text' => [
                'type' => 'text',
                'label' => 'Texto',
                'category' => 'Conteúdo',
                'icon' => 'text',
                'description' => 'Adiciona texto à página.',
                'default_content' => ['text' => 'Escreve aqui o teu texto.'],
                'default_settings' => ['align' => 'left'],
                'allowed_parents' => ['container'],
                'schema' => [
                    'content' => ['text' => 'string'],
                    'settings' => ['align' => ['left', 'center', 'right', 'justify']],
                ],
            ],
            'image' => [
                'type' => 'image',
                'label' => 'Imagem',
                'category' => 'Media',
                'icon' => 'image',
                'description' => 'Adiciona uma imagem à página.',
                'default_content' => ['url' => '', 'alt' => '', 'caption' => ''],
                'default_settings' => ['width' => 'full', 'radius' => 'none'],
                'allowed_parents' => ['container'],
                'schema' => [
                    'content' => ['url' => 'string', 'alt' => 'string', 'caption' => 'string'],
                    'settings' => ['width' => ['auto', 'full'], 'radius' => ['none', 'sm', 'md', 'lg', 'xl', 'full']],
                ],
            ],
            'button' => [
                'type' => 'button',
                'label' => 'Botão',
                'category' => 'Ação',
                'icon' => 'cursor-arrow-rays',
                'description' => 'Adiciona um botão com ligação.',
                'default_content' => ['label' => 'Saber mais', 'url' => '#'],
                'default_settings' => ['style' => 'primary', 'align' => 'left'],
                'allowed_parents' => ['container'],
                'schema' => [
                    'content' => ['label' => 'string', 'url' => 'string'],
                    'settings' => ['style' => ['primary', 'secondary', 'outline', 'ghost'], 'align' => ['left', 'center', 'right']],
                ],
            ],
            'divider' => [
                'type' => 'divider',
                'label' => 'Divisor',
                'category' => 'Layout',
                'icon' => 'minus',
                'description' => 'Adiciona uma linha divisória.',
                'default_content' => [],
                'default_settings' => ['width' => 'full', 'style' => 'solid'],
                'allowed_parents' => ['container'],
                'schema' => [
                    'content' => [],
                    'settings' => ['width' => ['auto', 'full'], 'style' => ['solid', 'dashed', 'dotted']],
                ],
            ],
            'spacer' => [
                'type' => 'spacer',
                'label' => 'Espaçamento',
                'category' => 'Layout',
                'icon' => 'arrows-up-down',
                'description' => 'Adiciona espaço vertical entre elementos.',
                'default_content' => [],
                'default_settings' => ['height' => 'md'],
                'allowed_parents' => ['container'],
                'schema' => [
                    'content' => [],
                    'settings' => ['height' => ['sm', 'md', 'lg', 'xl']],
                ],
            ],
        ];
    }

    public static function has(string $type): bool
    {
        return array_key_exists($type, self::all());
    }

    public static function get(string $type): array
    {
        return self::all()[$type] ?? throw new InvalidArgumentException("Unknown website element type [{$type}].");
    }

    public static function types(): array
    {
        return array_keys(self::all());
    }

    public static function defaults(string $type): array
    {
        $definition = self::get($type);

        return [
            'content' => $definition['default_content'],
            'settings' => $definition['default_settings'],
        ];
    }
}
