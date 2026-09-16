<?php

declare(strict_types=1);

namespace App\Support\WebsiteBuilder;

use App\Models\SiteSection;
use InvalidArgumentException;

final class SiteSectionDocument
{
    public static function fromSection(SiteSection $section): array
    {
        $settings = is_array($section->settings) ? $section->settings : [];
        $storedDocument = $settings['builder_document'] ?? null;

        if (is_string($storedDocument) && trim($storedDocument) !== '') {
            try {
                $storedDocument = json_decode($storedDocument, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                throw new InvalidArgumentException('Stored builder document contains invalid JSON.');
            }
        }

        if (is_array($storedDocument)) {
            BuilderDocument::validate($storedDocument);
            $document = BuilderDocument::normalize($storedDocument);

            foreach ($document['nodes'] as &$node) {
                if (($node['type'] ?? null) !== 'container') {
                    continue;
                }

                $legacy = is_array($node['settings']['legacy'] ?? null) ? $node['settings']['legacy'] : [];
                $legacy['section_id'] = $section->getKey();
                $legacy['site_page_id'] = $section->site_page_id;
                $legacy['type'] = (string) ($section->type ?? '');
                $legacy['label'] = $section->label;
                $legacy['sort_order'] = (int) ($section->sort_order ?? 0);
                $legacy['is_visible'] = (bool) ($section->is_visible ?? true);
                $node['settings']['legacy'] = $legacy;
            }
            unset($node);

            return BuilderDocument::normalize($document);
        }

        $type = (string) ($section->type ?? '');
        $content = is_array($section->content) ? $section->content : [];
        $containerId = BuilderNodeId::stable('container', self::seed($section));

        $node = [
            'id' => $containerId,
            'type' => 'container',
            'settings' => [
                'section_type' => $type,
                'section_type_known' => SectionRegistry::has($type),
                'legacy' => [
                    'section_id' => $section->getKey(),
                    'site_page_id' => $section->site_page_id,
                    'type' => $type,
                    'label' => $section->label,
                    'content' => $content,
                    'settings' => $settings,
                    'sort_order' => (int) ($section->sort_order ?? 0),
                    'is_visible' => (bool) ($section->is_visible ?? true),
                ],
            ],
            'children' => self::elements($section, $containerId, $content, $settings),
        ];

        return BuilderDocument::normalize([
            'schema_version' => BuilderDocument::VERSION,
            'nodes' => [$node],
        ]);
    }

    public static function toSectionData(array $document, ?array $legacyContent = null, ?array $legacySettings = null): array
    {
        $document = BuilderDocument::normalize($document);
        $storageDocument = self::compactLegacyPayload($document);
        $sections = [];

        foreach ($document['nodes'] as $node) {
            $legacy = $node['settings']['legacy'] ?? null;

            if (! is_array($legacy) || ! array_key_exists('type', $legacy)) {
                throw new InvalidArgumentException('Document node does not contain SiteSection legacy metadata.');
            }

            $content = $legacyContent ?? [];
            $settings = $legacySettings ?? [];
            $nodeSettings = is_array($settings) ? $settings : [];
            $nodeSettings['builder_document'] = $storageDocument;

            $section = [
                'site_page_id' => $legacy['site_page_id'] ?? null,
                'type' => (string) $legacy['type'],
                'label' => $legacy['label'] ?? null,
                'content' => $content,
                'settings' => $nodeSettings,
                'sort_order' => (int) ($legacy['sort_order'] ?? 0),
                'is_visible' => (bool) ($legacy['is_visible'] ?? true),
            ];

            if (array_key_exists('section_id', $legacy) && $legacy['section_id'] !== null) {
                $section['id'] = $legacy['section_id'];
            }

            $sections[] = $section;
        }

        if ($legacyContent !== null || $legacySettings !== null) {
            return $sections[0] ?? [];
        }

        return $sections;
    }

    private static function compactLegacyPayload(array $document): array
    {
        foreach ($document['nodes'] as &$node) {
            if (! is_array($node) || ($node['type'] ?? null) !== 'container') {
                continue;
            }

            if (is_array($node['settings']['legacy'] ?? null)) {
                unset($node['settings']['legacy']['content'], $node['settings']['legacy']['settings']);
            }
        }
        unset($node);

        return $document;
    }

    private static function elements(SiteSection $section, string $containerId, array $content, array $settings): array
    {
        $elements = [];
        $align = in_array($settings['align'] ?? null, ['left', 'center', 'right', 'justify'], true) ? $settings['align'] : 'left';

        if (is_string($content['title'] ?? null) && $content['title'] !== '') {
            $elements[] = self::element('heading', BuilderNodeId::stable('heading', $containerId.':title'), ['text' => $content['title'], 'level' => $section->type === 'hero' ? 'h1' : 'h2'], ['align' => $align]);
        }

        foreach (['subtitle', 'body', 'description'] as $field) {
            if (is_string($content[$field] ?? null) && $content[$field] !== '') {
                $elements[] = self::element('text', BuilderNodeId::stable('text', $containerId.':'.$field), ['text' => $content[$field]], ['align' => $align]);
            }
        }

        if ($section->type === 'image' && is_string($content['url'] ?? null) && $content['url'] !== '') {
            $elements[] = self::element('image', BuilderNodeId::stable('image', $containerId.':image'), ['url' => $content['url'], 'alt' => is_string($content['alt'] ?? null) ? $content['alt'] : '', 'caption' => is_string($content['caption'] ?? null) ? $content['caption'] : ''], ['width' => 'full', 'radius' => 'none']);
        }

        $buttonLabel = $content['button_label'] ?? $content['label'] ?? null;
        $buttonUrl = $content['button_url'] ?? $content['url'] ?? null;
        if (is_string($buttonLabel) && $buttonLabel !== '' && is_string($buttonUrl)) {
            $elements[] = self::element('button', BuilderNodeId::stable('button', $containerId.':button'), ['label' => $buttonLabel, 'url' => $buttonUrl], ['style' => 'primary', 'align' => $align]);
        }

        if ($section->type === 'video' && is_string($content['url'] ?? null) && $content['url'] !== '') {
            $elements[] = self::element('video', BuilderNodeId::stable('video', $containerId.':video'), ['url' => $content['url'], 'title' => $content['title'] ?? 'Vídeo'], ['ratio' => '16:9']);
        }

        if ($section->type === 'gallery' && is_array($content['items'] ?? null)) {
            $items = array_values(array_filter($content['items'], 'is_array'));
            $elements[] = self::element('gallery', BuilderNodeId::stable('gallery', $containerId.':gallery'), ['items' => $items], ['columns' => 3]);
        }

        if ($section->type === 'faq' && is_array($content['items'] ?? null)) {
            $elements[] = self::element('faq', BuilderNodeId::stable('faq', $containerId.':faq'), ['title' => $content['title'] ?? 'Perguntas frequentes', 'items' => array_values(array_filter($content['items'], 'is_array'))], []);
        }

        if ($section->type === 'social_links' && is_array($content['items'] ?? null)) {
            $elements[] = self::element('social_links', BuilderNodeId::stable('social_links', $containerId.':social'), ['items' => array_values(array_filter($content['items'], 'is_array'))], ['align' => $align]);
        }

        if ($section->type === 'testimonials' && is_array($content['items'] ?? null)) {
            foreach (array_values(array_filter($content['items'], 'is_array')) as $index => $item) {
                $elements[] = self::element('quote', BuilderNodeId::stable('quote', $containerId.':testimonial:'.$index), ['text' => (string) ($item['quote'] ?? $item['description'] ?? ''), 'author' => (string) ($item['name'] ?? '')], ['align' => $align]);
            }
        }

        if ($section->type === 'feature_grid' && is_array($content['items'] ?? null)) {
            foreach (array_values(array_filter($content['items'], 'is_array')) as $index => $item) {
                $elements[] = self::element('heading', BuilderNodeId::stable('heading', $containerId.':feature:'.$index), ['text' => (string) ($item['title'] ?? 'Benefício'), 'level' => 'h3'], ['align' => $align]);
                if (! empty($item['description'])) {
                    $elements[] = self::element('text', BuilderNodeId::stable('text', $containerId.':feature-description:'.$index), ['text' => (string) $item['description']], ['align' => $align]);
                }
            }
        }

        if ($section->type === 'pricing' && is_array($content['items'] ?? null)) {
            foreach (array_values(array_filter($content['items'], 'is_array')) as $index => $item) {
                $name = (string) ($item['name'] ?? $item['title'] ?? 'Plano');
                $price = (string) ($item['price'] ?? '');
                $description = (string) ($item['description'] ?? '');
                $elements[] = self::element('heading', BuilderNodeId::stable('heading', $containerId.':price:'.$index), ['text' => $name.($price !== '' ? ' — '.$price : ''), 'level' => 'h3'], ['align' => $align]);
                if ($description !== '') {
                    $elements[] = self::element('text', BuilderNodeId::stable('text', $containerId.':price-description:'.$index), ['text' => $description], ['align' => $align]);
                }
            }
        }

        if (in_array($section->type, ['contact_form', 'newsletter'], true)) {
            $elements[] = self::element('form', BuilderNodeId::stable('form', $containerId.':form'), ['title' => (string) ($content['title'] ?? ($section->type === 'newsletter' ? 'Subscreve a nossa newsletter' : 'Fala connosco')), 'submit_label' => (string) ($content['submit_label'] ?? 'Enviar')], ['style' => 'card']);
        }

        if ($section->type === 'cta') {
            $elements[] = self::element('button', BuilderNodeId::stable('button', $containerId.':cta'), ['label' => (string) ($content['button_label'] ?? 'Começar agora'), 'url' => (string) ($content['button_url'] ?? '#')], ['style' => 'primary', 'align' => $align]);
        }

        foreach (['product_grid' => 'Produtos', 'product_card' => 'Produto', 'blog_posts' => 'Artigos', 'map' => 'Localização'] as $type => $fallback) {
            if ($section->type === $type && $elements === []) {
                $elements[] = self::element('text', BuilderNodeId::stable('text', $containerId.':'.$type), ['text' => (string) ($content['title'] ?? $fallback)], ['align' => $align]);
            }
        }

        if ($section->type === 'card' && $elements === []) {
            $elements[] = self::element('text', BuilderNodeId::stable('text', $containerId.':card'), ['text' => (string) ($content['body'] ?? $content['description'] ?? 'Adiciona conteúdo ao cartão.')], ['align' => $align]);
        }

        return $elements;
    }

    private static function element(string $type, string $id, array $content, array $settings): array
    {
        return ['id' => $id, 'type' => $type, 'content' => $content, 'settings' => $settings];
    }

    private static function seed(SiteSection $section): string
    {
        if ($section->getKey() !== null) {
            return 'site-section:'.$section->getKey();
        }

        return 'site-section:'.hash('sha256', serialize([$section->site_page_id, $section->type, $section->label, $section->sort_order]));
    }
}
