<?php

declare(strict_types=1);

namespace App\Livewire\Hooks;

use App\Livewire\BuilderStudio;
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
        return null;
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
}
