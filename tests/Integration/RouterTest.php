<?php

namespace Exolnet\Translation\Tests\Integration;

use Exolnet\Translation\Routing\Router;

class RouterTest extends TestCase
{
    public function testCustomRouterIsRegistered()
    {
        $this->assertInstanceOf(Router::class, $this->getRouter());
    }

    public function testRegisterTranslatedRoute()
    {
        $this->app['config']->set('translation.available_locales', ['en', 'fr', 'es']);

        $this->getRouter()->groupLocales(function () {
            $this->getRouter()->get('example', 'ExampleController@index');
        });

        $routes = collect($this->getRouter()->getRoutes()->getRoutes())->pluck('uri')->all();

        $this->assertEquals(['en/example', 'fr/example', 'es/example'], $routes);
    }
}
