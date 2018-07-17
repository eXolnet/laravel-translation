<?php

namespace Exolnet\Translation\Tests\Integration;

use Exolnet\Translation\Routing\Router;

class RouterTest extends TestCase
{
    public function testCustomRouterIsRegistered()
    {
        $this->assertInstanceOf(Router::class, $this->app['router']);
    }
}
