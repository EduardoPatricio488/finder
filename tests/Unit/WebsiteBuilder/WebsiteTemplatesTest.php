<?php

declare(strict_types=1);

namespace Tests\Unit\WebsiteBuilder;

use App\Support\WebsiteBuilder\WebsiteTemplates;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class WebsiteTemplatesTest extends TestCase
{
    public function test_all_templates_have_valid_complete_page_definitions(): void
    {
        $templates = WebsiteTemplates::all();

        self::assertCount(10, $templates);

        foreach ($templates as $key => $template) {
            self::assertTrue(WebsiteTemplates::has($key));
            self::assertNotSame('', $template['name']);
            self::assertNotSame('', $template['description']);
            self::assertNotEmpty($template['pages']);

            $slugs = [];
            $homepages = 0;

            foreach ($template['pages'] as $page) {
                self::assertNotSame('', $page['name']);
                self::assertNotSame('', $page['slug']);
                self::assertNotContains($page['slug'], $slugs);
                $slugs[] = $page['slug'];
                $homepages += $page['slug'] === 'home' ? 1 : 0;
                self::assertNotEmpty($page['sections']);

                foreach ($page['sections'] as $section) {
                    self::assertNotSame('', $section['type']);
                    self::assertNotSame('', $section['label']);
                }
            }

            self::assertSame(1, $homepages);
            self::assertSame($template, WebsiteTemplates::get($key));
        }
    }

    public function test_unknown_template_is_rejected(): void
    {
        self::assertFalse(WebsiteTemplates::has('does-not-exist'));

        $this->expectException(InvalidArgumentException::class);
        WebsiteTemplates::get('does-not-exist');
    }
}
