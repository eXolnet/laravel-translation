<?php

namespace Exolnet\Translation\Tests\Integration;

use Exolnet\Translation\Routing\Router;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Lang;

class RouterTest extends TestCase
{
    /**
     * @return void
     * @test
     */
    public function testCustomRouterIsRegistered(): void
    {
        $this->assertInstanceOf(Router::class, $this->getRouter());
    }

    /**
     * @return void
     * @test
     */
    public function testRegisterTranslatedRoute(): void
    {
        $this->getRouter()->groupLocales(function () {
            $this->getRouter()->get('example', 'ExampleController@index')->name('example');
        });

        $this->assertEquals(
            ['en/example', 'fr/example', 'es/example'],
            $this->getRegisteredRouteUris()
        );

        $this->assertEquals(
            ['example.en', 'example.fr', 'example.es'],
            $this->getRegisteredRouteNames()
        );
    }

    /**
     * @return void
     * @test
     */
    public function testRoutesCache(): void
    {
        $this->getRouter()->groupLocales(function () {
            $this->getRouter()->get('example', 'ExampleController@index')->name('example');
        });

        $compiled = $this->getRouter()->getRoutes()->compile();
        $this->getRouter()->setCompiledRoutes($compiled);

        $this->assertEquals(
            ['en/example', 'fr/example', 'es/example'],
            $this->getRegisteredRouteUris()
        );

        $this->assertEquals(
            ['example.en', 'example.fr', 'example.es'],
            $this->getRegisteredRouteNames()
        );
    }

    /**
     * @return void
     * @test
     */
    public function testCustomLocales(): void
    {
        $this->getRouter()->groupLocales(function () {
            $this->getRouter()->get('example', 'ExampleController@index')->name('example');
        })->locales(['en', 'fr']);

        $this->assertEquals(
            ['en/example', 'fr/example'],
            $this->getRegisteredRouteUris()
        );

        $this->assertEquals(
            ['example.en', 'example.fr'],
            $this->getRegisteredRouteNames()
        );
    }

    /**
     * @return void
     * @test
     */
    public function testHiddenBaseLocale(): void
    {
        $this->getRouter()->groupLocales(function () {
            $this->getRouter()->get('example', 'ExampleController@index')->name('example');
        })->hiddenBaseLocale();

        $this->assertEquals(
            ['example', 'fr/example', 'es/example'],
            $this->getRegisteredRouteUris()
        );

        $this->assertEquals(
            ['example.en', 'example.fr', 'example.es'],
            $this->getRegisteredRouteNames()
        );
    }

    /**
     * @return void
     * @test
     */
    public function testTranslatedUri(): void
    {
        Lang::shouldReceive('setLocale');
        Lang::shouldReceive('get')->with('routes.fr', [], 'fr')->andReturn('routes.fr');
        Lang::shouldReceive('get')->with('routes.example', [], 'fr')->andReturn('exemple');

        $this->getRouter()->groupLocales(function () {
            $this->getRouter()->get('example', 'ExampleController@index')->name('example');
        })->locales(['fr']);

        $this->assertEquals(
            ['fr/exemple'],
            $this->getRegisteredRouteUris()
        );

        $this->assertEquals(
            ['example.fr'],
            $this->getRegisteredRouteNames()
        );
    }

    /**
     * @return void
     * @test
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
}
