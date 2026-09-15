<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Site;
use App\Models\User;
use App\Services\WebsiteTemplateInstaller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class WebsiteTemplateInstallerTest extends TestCase
{
    use RefreshDatabase;

    public function test_install_replaces_existing_site_structure_with_complete_template(): void
    {
        $user = User::factory()->create();
        $site = Site::factory()->create(['user_id' => $user->id]);
        $oldPage = $site->pages()->create([
            'name' => 'Página antiga',
            'slug' => 'antiga',
            'status' => 'draft',
            'is_homepage' => true,
            'sort_order' => 0,
            'seo' => [],
        ]);
        $oldPage->sections()->create([
            'type' => 'text',
            'label' => 'Antiga',
            'content' => ['body' => 'conteúdo antigo'],
            'settings' => [],
            'sort_order' => 0,
            'is_visible' => true,
        ]);

        $home = app(WebsiteTemplateInstaller::class)->install($site, 'restaurant');

        $site->refresh();
        $pages = $site->pages()->with('sections')->orderBy('sort_order')->get();

        self::assertSame('home', $home->slug);
        self::assertCount(4, $pages);
        self::assertSame(1, $pages->where('is_homepage', true)->count());
        self::assertSame(['home', 'menu', 'sobre', 'contactos'], $pages->pluck('slug')->all());
        self::assertTrue($pages->every(fn ($page) => $page->sections->isNotEmpty()));
        self::assertSame(0, $site->pages()->where('slug', 'antiga')->count());
    }

    public function test_install_rejects_unknown_template(): void
    {
        $site = Site::factory()->create(['user_id' => User::factory()->create()->id]);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        app(WebsiteTemplateInstaller::class)->install($site, 'missing');
    }
}
