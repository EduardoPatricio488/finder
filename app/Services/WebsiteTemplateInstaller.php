<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Site;
use App\Models\SitePage;
use App\Support\WebsiteBuilder\WebsiteTemplates;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

final class WebsiteTemplateInstaller
{
    public function install(Site $site, string $templateKey): SitePage
    {
        $definition = WebsiteTemplates::get($templateKey);

        return DB::transaction(function () use ($site, $definition): SitePage {
            $site->pages()->withTrashed()->get()->each(function (SitePage $page): void {
                $page->sections()->delete();
                $page->forceDelete();
            });

            $home = null;

            foreach ($definition['pages'] as $index => $definitionPage) {
                $page = $site->pages()->create([
                    'name' => Str::limit($definitionPage['name'], 120, ''),
                    'slug' => Str::slug($definitionPage['slug']),
                    'status' => 'draft',
                    'is_homepage' => $definitionPage['slug'] === 'home',
                    'sort_order' => $index,
                    'seo' => [],
                ]);

                foreach ($definitionPage['sections'] as $sectionIndex => $definitionSection) {
                    $type = $definitionSection['type'];
                    $page->sections()->create([
                        'type' => $type,
                        'label' => $definitionSection['label'],
                        'content' => $this->defaultContent($type),
                        'settings' => [
                            'background' => 'transparent',
                            'padding' => 'lg',
                            'align' => 'left',
                        ],
                        'sort_order' => $sectionIndex,
                        'is_visible' => true,
                    ]);
                }

                if ($definitionPage['slug'] === 'home') {
                    $home = $page;
                }
            }

            if (! $home instanceof SitePage) {
                throw new InvalidArgumentException('O template tem de definir uma página Home.');
            }

            $site->update([
                'theme' => $site->theme ?: $this->defaultTheme(),
            ]);

            return $home;
        });
    }

    private function defaultTheme(): array
    {
        return [
            'primary' => '#635bff',
            'secondary' => '#111827',
            'background' => '#ffffff',
            'text' => '#111827',
            'radius' => '1rem',
            'font_heading' => 'Inter',
            'font_body' => 'Inter',
        ];
    }

    private function defaultContent(string $type): array
    {
        return match ($type) {
            'hero' => ['title' => 'O teu novo website', 'subtitle' => 'Uma presença online profissional.', 'button_label' => 'Saber mais', 'button_url' => '#'],
            'text' => ['title' => 'Sobre nós', 'body' => 'Escreve aqui o conteúdo da tua secção.'],
            'image' => ['url' => '', 'alt' => '', 'caption' => ''],
            'button' => ['label' => 'Saber mais', 'url' => '#'],
            default => ['title' => Str::headline($type)],
        };
    }
}
