<?php

namespace Exolnet\Translation\Tests\Integration\Routing;

use Exolnet\Translation\Routing\Router;
use Exolnet\Translation\Tests\Integration\TestCase;
use Exolnet\Translation\Tests\Mocks\ExampleController;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Lang;

class RouterTest extends TestCase
{
    /**
     * @return void
     */
    public function testCustomRouterIsRegistered(): void
    {
        $this->assertInstanceOf(Router::class, $this->getRouter());
    }

    /**
     * @return void
     */
    public function testGetLocales(): void
    {
        $this->assertEquals(['en', 'fr', 'es'], $this->getRouter()->getLocales());
    }

    /**
     * @return void
     */
    public function testGetAlternateLocales(): void
    {
        $this->assertEquals(['fr', 'es'], $this->getRouter()->getAlternateLocales());
    }

    /**
     * @return void
     */
    public function testRegisterTranslatedRoute(): void
    {
        $this->getRouter()->groupLocales(function () {
            $this->getRouter()->get('example', 'ExampleController@index')->name('example');
        });

        $this->assertContainsAll(
            ['en/example', 'fr/example', 'es/example'],
            $this->getRegisteredRouteUris()
        );

        $this->assertContainsAll(
            ['example.en', 'example.fr', 'example.es'],
            $this->getRegisteredRouteNames()
        );
    }

    /**
     * @return void
     */
    public function testRegisterTranslatedResources(): void
    {
        $this->getRouter()->groupLocales(function () {
            $this->getRouter()->resource('examples', 'ExampleController')->only('index', 'show');
        });

        $this->assertContainsAll(
            [
                'en/examples',
                'en/examples/{example}',
                'fr/examples',
                'fr/examples/{example}',
                'es/examples',
                'es/examples/{example}',
            ],
            $this->getRegisteredRouteUris()
        );

        $this->assertContainsAll(
            [
                'examples.index.en',
                'examples.show.en',
                'examples.index.fr',
                'examples.show.fr',
                'examples.index.es',
                'examples.show.es',
            ],
            $this->getRegisteredRouteNames()
        );
    }

    /**
     * @return void
     */
    public function testRoutesCache(): void
    {
        $this->getRouter()->groupLocales(function () {
            $this->getRouter()->get('example', 'ExampleController@index')->name('example');
        });

        $routes = $this->getRouter()->getRoutes();

        if (! method_exists($routes, 'compile')) {
            $this->markTestSkipped('Route compilation is not available for this version of Laravel.');
        }

        $compiled = $routes->compile();
        $this->getRouter()->setCompiledRoutes($compiled);

        $this->assertContainsAll(
            ['en/example', 'fr/example', 'es/example'],
            $this->getRegisteredRouteUris()
        );

        $this->assertContainsAll(
            ['example.en', 'example.fr', 'example.es'],
            $this->getRegisteredRouteNames()
        );
    }

    /**
     * @return void
     */
    public function testCustomLocales(): void
    {
        $this->getRouter()->groupLocales(function () {
            $this->getRouter()->get('example', 'ExampleController@index')->name('example');
        })->locales(['en', 'fr']);

        $this->assertContainsAll(
            ['en/example', 'fr/example'],
            $this->getRegisteredRouteUris()
        );

        $this->assertContainsAll(
            ['example.en', 'example.fr'],
            $this->getRegisteredRouteNames()
        );
    }

    /**
     * @return void
     */
    public function testHiddenBaseLocale(): void
    {
        $this->getRouter()->groupLocales(function () {
            $this->getRouter()->get('example', 'ExampleController@index')->name('example');
        })->hiddenBaseLocale();

        $this->assertContainsAll(
            ['example', 'fr/example', 'es/example'],
            $this->getRegisteredRouteUris()
        );

        $this->assertContainsAll(
            ['example.en', 'example.fr', 'example.es'],
            $this->getRegisteredRouteNames()
        );
    }

    /**
     * @return void
     */
    public function testTranslatedUri(): void
    {
        Lang::shouldReceive('setLocale');
        Lang::shouldReceive('get')->with('routes.fr', [], 'fr')->andReturn('routes.fr');
        Lang::shouldReceive('get')->with('routes.example', [], 'fr')->andReturn('exemple');

        $this->getRouter()->groupLocales(function () {
            $this->getRouter()->get('example', 'ExampleController@index')->name('example');
        })->locales(['fr']);

        $this->assertContainsAll(
            ['fr/exemple'],
            $this->getRegisteredRouteUris()
        );

        $this->assertContainsAll(
            ['example.fr'],
            $this->getRegisteredRouteNames()
        );
    }

    /**
     * @return void
     */
    public function testRouteAreLocalized(): void
    {
        $this->getRouter()->groupLocales(function () {
            $this->getRouter()->get('example', function () {
                return App::getLocale();
            });
        });

        $this->get('en/example')->assertSee('en');
        $this->get('fr/example')->assertSee('fr');
        $this->get('es/example')->assertSee('es');
    }

    /**
     * @return void
     */
    public function testRouteParametersAreBound(): void
    {
        $this->getRouter()->groupLocales(function () {
            $this->getRouter()->get('example/{id}', function ($id) {
                $this->assertEquals('foo', $id);
            });
        });

        $this->get('en/example/foo');
        $this->get('fr/example/foo');
        $this->get('es/example/foo');
    }

    /**
     * @return void
     */
    public function testResourcesParametersAreBound(): void
    {
        $this->getRouter()->groupLocales(function () {
            $this->getRouter()->resource('example', ExampleController::class)->only('show');
        });

        $this->get('en/example/foo')->assertSee('show#foo');
        $this->get('fr/example/foo')->assertSee('show#foo');
        $this->get('es/example/foo')->assertSee('show#foo');
    }
}
