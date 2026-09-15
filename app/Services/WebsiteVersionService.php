<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Site;
use App\Models\SiteVersion;
use Illuminate\Support\Facades\DB;

final class WebsiteVersionService
{
    public function create(Site $site, ?int $userId = null, string $label = 'Versão guardada'): SiteVersion
    {
        return DB::transaction(function () use ($site, $userId, $label): SiteVersion {
            $snapshot = [
                'schema_version' => 2,
                'site' => [
                    'theme' => $site->theme ?? [],
                    'settings' => $site->settings ?? [],
                    'seo' => $site->seo ?? [],
                    'is_published' => (bool) $site->is_published,
                    'status' => $site->status,
                    'published_at' => $site->getRawOriginal('published_at'),
                ],
                'pages' => $site->pages()->with('sections')->orderBy('sort_order')->get()->map(fn ($page): array => [
                    'name' => $page->name,
                    'slug' => $page->slug,
                    'status' => $page->status,
                    'is_homepage' => (bool) $page->is_homepage,
                    'seo' => $page->seo ?? [],
                    'theme' => $page->theme ?? [],
                    'sections' => $page->sections->map(fn ($section): array => [
                        'type' => $section->type,
                        'label' => $section->label,
                        'content' => $section->content ?? [],
                        'settings' => $section->settings ?? [],
                        'sort_order' => $section->sort_order,
                        'is_visible' => (bool) $section->is_visible,
                    ])->values()->all(),
                ])->values()->all(),
            ];

            $latest = $site->versions()->orderByDesc('version_number')->first();
            if ($latest && self::snapshotHash($latest->snapshot) === self::snapshotHash($snapshot)) {
                return $latest;
            }

            $number = ((int) $site->versions()->max('version_number')) + 1;
            $version = $site->versions()->create([
                'created_by' => $userId,
                'label' => trim($label) !== '' ? trim($label) : 'Versão guardada',
                'version_number' => $number,
                'snapshot' => $snapshot,
            ]);

            $versionIds = $site->versions()
                ->orderByDesc('version_number')
                ->pluck('id')
                ->all();
            $oldVersionIds = array_slice($versionIds, 30);

            if ($oldVersionIds !== []) {
                SiteVersion::query()->whereIn('id', $oldVersionIds)->delete();
            }

            return $version;
        });
    }

    private static function snapshotHash(mixed $snapshot): string
    {
        return hash('sha256', json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR));
    }
}
