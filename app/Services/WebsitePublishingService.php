<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Site;
use App\Support\WebsiteBuilder\BuilderDocument;

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
            if ($pages->where('slug', $page->slug)->count() > 1) {
                $errors[] = 'O slug "'.$page->slug.'" está duplicado.';
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

            foreach ($page->sections as $section) {
                $content = is_array($section->content) ? $section->content : [];
                $settings = is_array($section->settings) ? $section->settings : [];

                if ($this->containsUnsafeUrl($content) || $this->containsUnsafeUrl($settings)) {
                    $errors[] = 'A página "'.$page->name.'" contém uma ligação não permitida.';
                }

                $document = $settings['builder_document'] ?? null;
                if ($document !== null && (! is_array($document) || ! BuilderDocument::isValid($document))) {
                    $errors[] = 'A secção "'.$section->label.'" na página "'.$page->name.'" contém uma estrutura inválida.';
                }

                if ($section->type === 'image' && blank($content['url'] ?? null)) {
                    $warnings[] = 'A secção de imagem "'.$section->label.'" na página "'.$page->name.'" não tem imagem.';
                }
                if ($section->type === 'image' && ! blank($content['url'] ?? null) && blank($content['alt'] ?? null)) {
                    $warnings[] = 'A imagem "'.$section->label.'" na página "'.$page->name.'" não tem texto alternativo.';
                }
                if (in_array($section->type, ['video', 'map'], true) && blank($content['url'] ?? $content['embed_url'] ?? null) && blank($content['address'] ?? null)) {
                    $warnings[] = 'A secção "'.$section->label.'" na página "'.$page->name.'" precisa de conteúdo.';
                }
            }
        }

        if (blank($site->favicon)) {
            $warnings[] = 'Adiciona um favicon para uma apresentação mais profissional.';
        }
        if (blank($site->seo['og_image'] ?? null)) {
            $warnings[] = 'Adiciona uma imagem social para melhorar as partilhas.';
        }
        if (blank($site->description) && blank($site->tagline)) {
            $warnings[] = 'Adiciona uma descrição do website para melhorar o SEO e a partilha social.';
        }

        return [
            'ok' => $errors === [],
            'errors' => array_values(array_unique($errors)),
            'warnings' => array_values(array_unique($warnings)),
        ];
    }

    public function publish(Site $site, ?int $actorId = null): void
    {
        $result = $this->validate($site);

        if (! $result['ok']) {
            abort(422, implode(' ', $result['errors']));
        }

        $latest = $site->versions()->latest('created_at')->first();
        if (! $latest || ! $latest->created_at || $latest->created_at->lt(now()->subSeconds(5))) {
            $this->versions->create($site->fresh(), $actorId ?? auth()->id(), 'Backup automático antes da publicação');
        }

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
            'published_at' => null,
        ])->save();
    }

    private function containsUnsafeUrl(array $value): bool
    {
        foreach ($value as $item) {
            if (is_array($item) && $this->containsUnsafeUrl($item)) {
                return true;
            }
            if (is_string($item) && preg_match('/(?:^|\s)(?:javascript|data|vbscript):/i', trim($item))) {
                return true;
            }
        }

        return false;
    }
}
