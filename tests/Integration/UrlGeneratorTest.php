<?php

namespace Exolnet\Translation\Tests\Integration;

use Exolnet\Translation\Routing\UrlGenerator;

class UrlGeneratorTest extends TestCase
{
    public function testCustomUrlGeneratorIsRegistered()
    {
        $this->assertInstanceOf(UrlGenerator::class, $this->app['url']);
    }
}
