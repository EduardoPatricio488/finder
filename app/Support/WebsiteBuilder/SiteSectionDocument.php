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
        if (is_array($settings['builder_document'] ?? null)) {
            BuilderDocument::validate($settings['builder_document']);
            return BuilderDocument::normalize($settings['builder_document']);
        }

        $type = (string) ($section->type ?? '');
        $content = is_array($section->content) ? $section->content : [];
        $containerId = BuilderNodeId::stable('container', self::seed($section));
        unset($settings['builder_document']);

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

        return BuilderDocument::normalize(['schema_version' => BuilderDocument::VERSION, 'nodes' => [$node]]);
    }

    public static function toSectionData(array $document): array
    {
        BuilderDocument::validate($document);
        $sections = [];
        foreach ($document['nodes'] as $node) {
            $legacy = $node['settings']['legacy'] ?? null;
            if (! is_array($legacy) || ! array_key_exists('type', $legacy)) throw new InvalidArgumentException('Document node does not contain SiteSection legacy metadata.');
            $section = [
                'site_page_id' => $legacy['site_page_id'] ?? null,
                'type' => (string) $legacy['type'],
                'label' => $legacy['label'] ?? null,
                'content' => is_array($legacy['content'] ?? null) ? $legacy['content'] : [],
                'settings' => is_array($legacy['settings'] ?? null) ? $legacy['settings'] : [],
                'sort_order' => (int) ($legacy['sort_order'] ?? 0),
                'is_visible' => (bool) ($legacy['is_visible'] ?? true),
            ];
            if (array_key_exists('section_id', $legacy) && $legacy['section_id'] !== null) $section['id'] = $legacy['section_id'];
            $sections[] = $section;
        }
        return $sections;
    }

    private static function elements(SiteSection $section, string $containerId, array $content, array $settings): array
    {
        $elements = [];
        $align = in_array($settings['align'] ?? null, ['left', 'center', 'right', 'justify'], true) ? $settings['align'] : 'left';
        if (is_string($content['title'] ?? null)) $elements[] = self::element('heading', BuilderNodeId::stable('heading', $containerId.':title'), ['text' => $content['title']], ['align' => $align, 'level' => $section->type === 'hero' ? 'h1' : 'h2']);
        foreach (['subtitle', 'body', 'description'] as $field) if (is_string($content[$field] ?? null)) $elements[] = self::element('text', BuilderNodeId::stable('text', $containerId.':'.$field), ['text' => $content[$field]], ['align' => $align]);
        if ($section->type === 'image' && is_string($content['url'] ?? null) && $content['url'] !== '') $elements[] = self::element('image', BuilderNodeId::stable('image', $containerId.':image'), ['url' => $content['url'], 'alt' => is_string($content['alt'] ?? null) ? $content['alt'] : '', 'caption' => is_string($content['caption'] ?? null) ? $content['caption'] : ''], ['width' => 'full', 'radius' => 'none']);
        $buttonLabel = $content['button_label'] ?? $content['label'] ?? null;
        $buttonUrl = $content['button_url'] ?? $content['url'] ?? null;
        if (is_string($buttonLabel) && $buttonLabel !== '' && is_string($buttonUrl)) $elements[] = self::element('button', BuilderNodeId::stable('button', $containerId.':button'), ['label' => $buttonLabel, 'url' => $buttonUrl], ['style' => 'primary', 'align' => $align]);
        return $elements;
    }

    private static function element(string $type, string $id, array $content, array $settings): array
    {
        return ['id' => $id, 'type' => $type, 'content' => $content, 'settings' => $settings];
    }

    private static function seed(SiteSection $section): string
    {
        if ($section->getKey() !== null) return 'site-section:'.$section->getKey();
        return 'site-section:'.hash('sha256', serialize([$section->site_page_id, $section->type, $section->label, $section->sort_order]));
    }
}
