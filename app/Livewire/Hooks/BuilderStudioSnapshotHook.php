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

        $pageId = $this->component->pageId;
        if ($pageId === null) {
            return;
        }

        $sections = $this->component->site->pages()
            ->whereKey($pageId)
            ->with('sections')
            ->first()?->sections;

        if ($sections === null) {
            return;
        }

        $byId = $sections->keyBy('id');

        foreach ($this->component->sections as $index => $data) {
            $id = isset($data['id']) ? (int) $data['id'] : 0;
            if ($id <= 0 || ! $byId->has($id)) {
                continue;
            }

            $section = $byId->get($id);
            $settings = is_array($section->settings) ? $section->settings : [];
            if (! isset($settings['builder_document'])) {
                continue;
            }

            $this->component->sections[$index]['settings']['builder_document'] = SiteSectionDocument::fromSection($section);
        }
    }

    public function dehydrate(): void
    {
        if (! $this->component instanceof BuilderStudio) {
            return;
        }

        $pageId = $this->component->pageId;
        if ($pageId === null) {
            return;
        }

        $page = $this->component->site->pages()->find($pageId);
        if ($page === null) {
            return;
        }

        foreach ($this->component->sections as $index => $data) {
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

            $sectionData = SiteSectionDocument::toSectionData($document);
            $payload = $sectionData[0] ?? null;
            if (! is_array($payload)) {
                continue;
            }

            $section->update([
                'content' => is_array($payload['content'] ?? null) ? $payload['content'] : [],
                'settings' => is_array($payload['settings'] ?? null) ? $payload['settings'] : [],
            ]);

            unset($this->component->sections[$index]['settings']['builder_document']);
        }
    }
}
