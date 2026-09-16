<?php

declare(strict_types=1);

namespace App\Livewire\Hooks;

use App\Livewire\BuilderStudio;
use App\Models\SiteSection;
use App\Support\WebsiteBuilder\SiteSectionDocument;
use Livewire\ComponentHook;

final class BuilderStudioSnapshotHook extends ComponentHook
{
    public function hydrate(): void
    {
        if (! $this->component instanceof BuilderStudio) {
            return;
        }

        foreach ($this->component->sections as $index => $data) {
            $storedDocument = $data['settings']['builder_document'] ?? null;

            if (! is_string($storedDocument) || trim($storedDocument) === '') {
                continue;
            }

            try {
                $document = json_decode($storedDocument, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                continue;
            }

            if (is_array($document)) {
                $this->component->sections[$index]['settings']['builder_document'] = $document;
            }
        }
    }

    public function call($method, $params, $returnEarly, $metadata = null, $componentContext = null): ?callable
    {
        if (! $this->component instanceof BuilderStudio) {
            return null;
        }

        if (! in_array($method, [
            'addElement',
            'selectElement',
            'updateSelectedElement',
            'updateSelectedElementSetting',
            'removeElement',
            'moveElement',
            'updateElement',
            'addSection',
            'duplicateSection',
            'deleteSection',
            'toggleSectionVisibility',
            'reorderSections',
        ], true)) {
            return null;
        }

        return function (): void {
            $this->persistDocuments();
        };
    }

    public function dehydrate(): void
    {
        if (! $this->component instanceof BuilderStudio) {
            return;
        }

        foreach ($this->component->sections as $index => $data) {
            $document = $data['settings']['builder_document'] ?? null;

            if (! is_array($document)) {
                continue;
            }

            try {
                $this->component->sections[$index]['settings']['builder_document'] = json_encode(
                    $document,
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR,
                );
            } catch (\JsonException) {
                unset($this->component->sections[$index]['settings']['builder_document']);
            }
        }
    }

    private function persistDocuments(): void
    {
        $pageId = $this->component->pageId;
        if ($pageId === null) {
            return;
        }

        $page = $this->component->site->pages()->find($pageId);
        if ($page === null) {
            return;
        }

        foreach ($this->component->sections as $data) {
            $document = $data['settings']['builder_document'] ?? null;
            if (! is_array($document)) {
                continue;
            }

            $id = isset($data['id']) ? (int) $data['id'] : 0;
            if ($id <= 0) {
                continue;
            }

            $section = $page->sections()->find($id);
            if (! $section instanceof SiteSection) {
                continue;
            }

            $legacyContent = is_array($data['content'] ?? null) ? $data['content'] : [];
            $legacySettings = is_array($data['settings'] ?? null) ? $data['settings'] : [];
            unset($legacySettings['builder_document']);

            $sectionData = SiteSectionDocument::toSectionData($document, $legacyContent, $legacySettings);
            $payload = $sectionData[0] ?? null;
            if (! is_array($payload)) {
                continue;
            }

            $section->update([
                'content' => is_array($payload['content'] ?? null) ? $payload['content'] : [],
                'settings' => is_array($payload['settings'] ?? null) ? $payload['settings'] : [],
            ]);
        }
    }
}
