<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Site;
use Illuminate\Http\Response;

final class SiteSeoController
{
    public function sitemap(Site $site): Response
    {
        abort_unless($site->is_published && $site->status === 'published', 404);

        $pages = $site->pages()
            ->where('status', 'published')
            ->orderBy('sort_order')
            ->get(['slug', 'updated_at', 'is_homepage']);

        $base = route('site.public', ['site' => $site->slug]);
        $urls = $pages->map(function ($page) use ($base): string {
            $loc = $page->is_homepage
                ? $base
                : route('site.public', ['site' => request()->route('site'), 'pageSlug' => $page->slug]);

            return '<url><loc>'.e($loc).'</loc><lastmod>'.e($page->updated_at->toAtomString()).'</lastmod></url>';
        })->implode('');

        return response(
            '<?xml version="1.0" encoding="UTF-8"?>'.
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.$urls.'</urlset>',
            200,
            ['Content-Type' => 'application/xml; charset=UTF-8', 'Cache-Control' => 'public, max-age=900'],
        );
    }

    public function robots(Site $site): Response
    {
        abort_unless($site->is_published && $site->status === 'published', 404);

        $sitemap = route('site.sitemap', ['site' => $site->slug]);
        $body = "User-agent: *\nAllow: /\n\nSitemap: {$sitemap}\n";

        return response($body, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=900',
        ]);
    }
}
