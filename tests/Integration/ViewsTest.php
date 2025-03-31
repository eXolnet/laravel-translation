<?php

namespace Exolnet\Translation\Tests\Integration;

use Illuminate\Support\Facades\URL;

class ViewsTest extends TestCase
{
    /**
     * @return void
     */
    public function testHeadAlternates(): void
    {
        URL::shouldReceive('alternateFullUrls')->andReturn([
            'en' => 'http://example.com/en',
            'fr' => 'http://example.com/fr',
        ]);

        $html = view('translation::head-alternates')->render();

        $this->assertStringContainsString('<link rel="alternate" hreflang="en" href="http://example.com/en" />', $html);
        $this->assertStringContainsString('<link rel="alternate" hreflang="fr" href="http://example.com/fr" />', $html);
    }

    /**
     * @return void
     */
    public function testLinkAlternates(): void
    {
        URL::shouldReceive('alternateFullUrls')->andReturn([
            'en' => 'http://example.com/en',
            'fr' => 'http://example.com/fr',
        ]);

        $html = view('translation::links-alternates')->render();

        $this->assertStringContainsString('<a href="http://example.com/en" lang="en" hreflang="en">', $html);
        $this->assertStringContainsString('<a href="http://example.com/fr" lang="fr" hreflang="fr">', $html);
    }
}
