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
        $payload = SiteSectionDocument::toSectionData($document)[0] ?? null;

        if (! is_array($payload)) {
            throw new InvalidArgumentException('A section document must contain one root container.');
        }

        $section->fill([
            'content' => is_array($payload['content'] ?? null) ? $payload['content'] : [],
            'settings' => is_array($payload['settings'] ?? null) ? $payload['settings'] : [],
        ]);
        $section->save();
    }
}
