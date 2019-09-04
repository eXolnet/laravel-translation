<?php

namespace Exolnet\Translation\Tests\Integration;

use Exolnet\Translation\Routing\Router;
use Illuminate\Routing\Route;

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
            $this->getRouter()->get('example', 'ExampleController@index')->name('example');
        });

        $routes = $this->getRouter()->getRoutes()->getRoutes();

        // Route URLs
        $this->assertEquals(
            ['en/example', 'fr/example', 'es/example'],
            collect($routes)->pluck('uri')->all()
        );

        // Route names
        $routeNames = collect($routes)
            ->map(function (Route $route) {
                return $route->getName();
            })
            ->all();

        $this->assertEquals(['example.en', 'example.fr', 'example.es'], $routeNames);
    }
}
