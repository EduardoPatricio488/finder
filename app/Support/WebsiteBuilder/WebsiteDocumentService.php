<?php

declare(strict_types=1);

namespace App\Support\WebsiteBuilder;

use App\Models\SiteSection;
use InvalidArgumentException;

final class WebsiteDocumentService
{
    public function read(SiteSection $section): array
    {
        return SiteSectionDocument::fromSection($section);
    }

    public function write(SiteSection $section, array $document): void
    {
        $document = BuilderDocument::normalize($document);
        $data = SiteSectionDocument::toSectionData($document);
        $payload = $data[0] ?? null;

        if (! is_array($payload)) {
            throw new InvalidArgumentException('A section document must contain one root container.');
        }

        unset($payload['id']);
        $settings = is_array($payload['settings'] ?? null) ? $payload['settings'] : [];
        $settings['builder_document'] = $document;

        $section->fill([
            'content' => is_array($payload['content'] ?? null) ? $payload['content'] : [],
            'settings' => $settings,
        ]);
        $section->save();
    }
}
