<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Site;

final class WebsitePublishingService
{
    public function __construct(private readonly WebsiteVersionService $versions) {}

    /** @return array{ok:bool,errors:list<string>,warnings:list<string>} */
    public function validate(Site $site): array
    {
        $errors = [];
        $warnings = [];
        $pages = $site->pages()->with('sections')->get();

        if (trim((string) $site->name) === '') {
            $errors[] = 'O website precisa de um nome.';
        }

        if ($pages->isEmpty()) {
            $errors[] = 'Cria pelo menos uma página.';
        }

        if (! $pages->contains(fn ($page) => $page->is_homepage)) {
            $errors[] = 'Define uma homepage.';
        }

        foreach ($pages as $page) {
            if (trim((string) $page->name) === '' || trim((string) $page->slug) === '') {
                $errors[] = 'Existe uma página sem nome ou slug.';
            }

            if ($page->sections->isEmpty()) {
                $errors[] = 'A página "'.$page->name.'" está vazia.';
            }

            $seo = is_array($page->seo) ? $page->seo : [];
            if (blank($seo['title'] ?? null)) {
                $warnings[] = 'A página "'.$page->name.'" não tem título SEO.';
            }
            if (blank($seo['description'] ?? null)) {
                $warnings[] = 'A página "'.$page->name.'" não tem meta descrição.';
            }
        }

        if (blank($site->favicon)) {
            $warnings[] = 'Adiciona um favicon para uma apresentação mais profissional.';
        }

        if (blank($site->seo['og_image'] ?? null)) {
            $warnings[] = 'Adiciona uma imagem social para melhorar as partilhas.';
        }

        return [
            'ok' => $errors === [],
            'errors' => array_values(array_unique($errors)),
            'warnings' => array_values(array_unique($warnings)),
        ];
    }

    public function publish(Site $site): void
    {
        $result = $this->validate($site);

        if (! $result['ok']) {
            abort(422, implode(' ', $result['errors']));
        }

        $this->versions->create(
            $site->fresh(),
            auth()->id(),
            'Backup automático antes da publicação',
        );

        $site->forceFill([
            'is_published' => true,
            'status' => 'published',
            'published_at' => now(),
        ])->save();
    }

    public function unpublish(Site $site): void
    {
        $site->forceFill([
            'is_published' => false,
            'status' => 'draft',
        ])->save();
    }
}
