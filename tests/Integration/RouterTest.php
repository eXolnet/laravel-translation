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
        $this->app['config']->set(
            'translation.available_locales',
            [
                'en' => ['system' => ['en_CA.UTF-8']],
                'fr' => ['system' => ['fr_CA.UTF-8']],
                'es' => [],
            ]
        );

        $this->getRouter()->groupLocales(function () {
            $this->getRouter()->get('example', 'ExampleController@index');
        });

        $routes = collect($this->getRouter()->getRoutes()->getRoutes())->pluck('uri')->all();

        $this->assertEquals(['en/example', 'fr/example', 'es/example'], $routes);
    }
}
