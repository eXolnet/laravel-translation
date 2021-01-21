<?php

namespace Exolnet\Translation\Tests\Integration;

use Exolnet\Translation\Routing\RouteLocalized;
use Illuminate\Routing\Route as LaravelRoute;
use Illuminate\Support\Facades\App;

class RouteTest extends TestCase
{
    /**
     * @return void
     * @test
     */
    public function testRouteLocaleCanBeDefined(): void
    {
        $route = new RouteLocalized('GET', 'fr/foo', [
            'as' => 'bar',
            'uses' => function () {
                //
            },
        ], 'fr');

        $this->assertTrue($route->isLocalized());
        $this->assertEquals('fr', $route->getLocale());
        $this->assertEquals('foo', $route->getLocaleBaseUri());
        $this->assertEquals('bar.fr', $route->getName());
    }

    /**
     * @return void
     * @test
     */
    public function testLaravelRouteAreNotLocalized(): void
    {
        $route = new LaravelRoute('GET', 'foo', [
            'as' => 'bar',
            'uses' => function () {
                //
            },
        ]);

        $this->assertFalse($route->isLocalized());
        $this->assertNull($route->getLocale());
        $this->assertNull($route->getLocaleBaseUri());
        $this->assertEquals('bar', $route->getName());
    }

    /**
     * @return void
     * @test
     */
    public function testRouteLocalizationWithBindingFields(): void
    {
        if (version_compare(App::version(), '7.0', '<')) {
            $this->markTestSkipped('Route binding fields was added on Laravel 7.0');
        }

        $route = new RouteLocalized('GET', 'fr/{user}/posts/{post:slug}', [
            'as' => 'bar',
            'uses' => function () {
                //
            },
        ], 'fr');

        $this->assertEquals('fr/{user}/posts/{post}', $route->uri);
        $this->assertEquals('slug', $route->bindingFieldFor('post'));
    }

    /**
     * @return void
     * @test
     */
    public function testGetLocaleAlternates(): void
    {
        $this->getRouter()->groupLocales(function () {
            $this->getRouter()->get('example', 'ExampleController@index')->name('example');
        })->locales(['en', 'fr', 'es'])->hiddenBaseLocale();

        $routes = $this->getRouter()->getRoutes();

        $routes->refreshNameLookups();

        $alternates = $routes->getByName('example.fr')->getLocaleAlternates();

        $this->assertCount(2, $alternates);

        $this->assertArrayHasKey('en', $alternates);
        $this->assertArrayHasKey('es', $alternates);
    }
}
