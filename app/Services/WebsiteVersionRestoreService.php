<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Site;
use App\Models\SiteVersion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

final class WebsiteVersionRestoreService
{
    public function restore(Site $site, SiteVersion $version): void
    {
        abort_unless($version->site_id === $site->id, 404);

        $snapshot = $version->snapshot;
        abort_unless(is_array($snapshot), 422);
        $pages = $snapshot['pages'] ?? null;
        abort_unless(is_array($pages), 422);

        DB::transaction(function () use ($site, $pages, $snapshot): void {
            $site->update([
                'theme' => is_array(data_get($snapshot, 'site.theme')) ? data_get($snapshot, 'site.theme') : [],
                'settings' => is_array(data_get($snapshot, 'site.settings')) ? data_get($snapshot, 'site.settings') : [],
                'seo' => is_array(data_get($snapshot, 'site.seo')) ? data_get($snapshot, 'site.seo') : [],
            ]);

            $site->pages()->withTrashed()->get()->each(function ($page): void {
                $page->sections()->delete();
                $page->forceDelete();
            });

            foreach (array_values($pages) as $pageIndex => $pageData) {
                if (! is_array($pageData)) {
                    continue;
                }

                $name = trim(strip_tags((string) ($pageData['name'] ?? 'Página')));
                $slug = Str::slug((string) ($pageData['slug'] ?? $name));
                if ($name === '' || $slug === '') {
                    throw new RuntimeException('A versão contém uma página inválida.');
                }

                $page = $site->pages()->create([
                    'name' => Str::limit($name, 120, ''),
                    'slug' => Str::limit($slug, 120, ''),
                    'status' => in_array($pageData['status'] ?? 'draft', ['draft', 'published'], true) ? $pageData['status'] : 'draft',
                    'is_homepage' => (bool) ($pageData['is_homepage'] ?? $pageIndex === 0),
                    'seo' => is_array($pageData['seo'] ?? null) ? $pageData['seo'] : [],
                    'theme' => is_array($pageData['theme'] ?? null) ? $pageData['theme'] : [],
                    'sort_order' => $pageIndex,
                ]);

                foreach (array_values(is_array($pageData['sections'] ?? null) ? $pageData['sections'] : []) as $sectionIndex => $sectionData) {
                    if (! is_array($sectionData)) {
                        continue;
                    }

                    $page->sections()->create([
                        'type' => trim((string) ($sectionData['type'] ?? 'text')),
                        'label' => Str::limit(trim(strip_tags((string) ($sectionData['label'] ?? 'Secção'))), 120, ''),
                        'content' => is_array($sectionData['content'] ?? null) ? $sectionData['content'] : [],
                        'settings' => is_array($sectionData['settings'] ?? null) ? $sectionData['settings'] : [],
                        'sort_order' => $sectionIndex,
                        'is_visible' => (bool) ($sectionData['is_visible'] ?? true),
                    ]);
                }
            }

            if (! $site->pages()->where('is_homepage', true)->exists()) {
                $site->pages()->orderBy('sort_order')->first()?->update(['is_homepage' => true]);
            }
        });
    }
}
