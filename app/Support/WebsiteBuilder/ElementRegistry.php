<?php

declare(strict_types=1);

namespace App\Support\WebsiteBuilder;

use InvalidArgumentException;

final class ElementRegistry
{
    public static function all(): array
    {
        return [
            'heading' => self::definition('heading', 'Título', 'Conteúdo', 'Adiciona um título à página.', ['text' => 'Novo título', 'level' => 'h2'], ['align' => 'left']),
            'text' => self::definition('text', 'Texto', 'Conteúdo', 'Adiciona texto à página.', ['text' => 'Escreve aqui o teu texto.'], ['align' => 'left']),
            'image' => self::definition('image', 'Imagem', 'Media', 'Adiciona uma imagem à página.', ['url' => '', 'alt' => '', 'caption' => ''], ['width' => 'full', 'radius' => 'none']),
            'button' => self::definition('button', 'Botão', 'Ação', 'Adiciona um botão com ligação.', ['label' => 'Saber mais', 'url' => '#'], ['style' => 'primary', 'align' => 'left']),
            'divider' => self::definition('divider', 'Divisor', 'Layout', 'Adiciona uma linha divisória.', [], ['width' => 'full', 'style' => 'solid']),
            'spacer' => self::definition('spacer', 'Espaçamento', 'Layout', 'Adiciona espaço vertical entre elementos.', [], ['height' => 'md']),
            'video' => self::definition('video', 'Vídeo', 'Media', 'Adiciona um vídeo incorporado.', ['url' => '', 'title' => 'Vídeo'], ['ratio' => '16:9']),
            'quote' => self::definition('quote', 'Citação', 'Conteúdo', 'Adiciona uma citação destacada.', ['text' => 'Uma frase importante para os teus visitantes.', 'author' => ''], ['align' => 'left']),
            'social_links' => self::definition('social_links', 'Redes sociais', 'Conteúdo', 'Mostra ligações para redes sociais.', ['items' => []], ['align' => 'left']),
            'faq' => self::definition('faq', 'FAQ', 'Conteúdo', 'Adiciona perguntas e respostas.', ['title' => 'Perguntas frequentes', 'items' => []], []),
            'gallery' => self::definition('gallery', 'Galeria', 'Media', 'Apresenta várias imagens.', ['items' => []], ['columns' => 3]),
            'form' => self::definition('form', 'Formulário', 'Conversão', 'Adiciona um formulário de contacto.', ['title' => 'Fala connosco', 'submit_label' => 'Enviar'], ['style' => 'card']),
            'html' => self::definition('html', 'HTML', 'Avançado', 'Adiciona HTML personalizado.', ['html' => ''], [], ['content' => ['html' => 'string']]),
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

        return ['content' => $definition['default_content'], 'settings' => $definition['default_settings']];
    }

    private static function definition(string $type, string $label, string $category, string $description, array $content, array $settings, ?array $schema = null): array
    {
        $contentSchema = [];
        foreach ($content as $key => $value) {
            $contentSchema[$key] = is_array($value) ? 'array' : 'string';
        }
        $settingsSchema = [];
        foreach ($settings as $key => $value) {
            $settingsSchema[$key] = is_array($value) ? array_values($value) : 'string';
        }

        return [
            'type' => $type,
            'label' => $label,
            'category' => $category,
            'icon' => match ($type) {
                'heading' => 'heading', 'text' => 'text', 'image', 'gallery' => 'image',
                'button', 'form' => 'cursor-arrow-rays', 'video' => 'play', 'quote' => 'chat-bubble-left-right',
                'faq' => 'question-mark-circle', 'social_links' => 'share', 'html' => 'code-bracket',
                'divider' => 'minus', default => 'arrows-up-down',
            },
            'description' => $description,
            'default_content' => $content,
            'default_settings' => $settings,
            'allowed_parents' => ['container'],
            'schema' => $schema ?? ['content' => $contentSchema, 'settings' => $settingsSchema],
        ];
    }
}
