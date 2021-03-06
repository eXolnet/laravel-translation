<?php

namespace Exolnet\Translation\Tests\Integration;

use Exolnet\Translation\Routing\UrlGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class UrlGeneratorTest extends TestCase
{
    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->getRouter()->get('/')->name('home');

        $this->getRouter()->groupLocales(function () {
            $this->getRouter()->get('example', function () {
                //
            })->name('example');
        })->locales(['en', 'fr', 'es'])->hiddenBaseLocale();
    }

    /**
     * @return void
     * @test
     */
    public function testCustomUrlGeneratorIsRegistered(): void
    {
        $this->assertInstanceOf(UrlGenerator::class, $this->app['url']);
    }

    /**
     * @return void
     * @test
     */
    public function testFindLocalizedRouteByName(): void
    {
        $this->assertEquals('http://localhost/example', URL::route('example', ['locale' => 'en']));
        $this->assertEquals('http://localhost/fr/example', URL::route('example', ['locale' => 'fr']));
        $this->assertEquals('http://localhost/es/example', URL::route('example', ['locale' => 'es']));
    }

    /**
     * @return void
     * @test
     */
    public function testRouteByNameWithoutLocalization(): void
    {
        $this->assertEquals('http://localhost', URL::route('home'));
    }

    /**
     * @return void
     * @test
     */
    public function testAlternateUrls(): void
    {
        $this->get('fr/example?foo=bar');

        $this->assertEquals(
            [
                'en' => 'http://localhost/example',
                'es' => 'http://localhost/es/example',
            ],
            URL::alternateUrls()
        );
    }

    /**
     * @return void
     * @test
     */
    public function testAlternateUrlsWithParameters(): void
    {
        $this->getRouter()->groupLocales(function () {
            $this->getRouter()->get('example/{id}', function () {
                //
            });
        })->locales(['en', 'fr', 'es'])->hiddenBaseLocale();

        $this->get('fr/example/foo?bar=baz');

        $this->assertEquals(
            [
                'en' => 'http://localhost/example/biz',
                'es' => 'http://localhost/es/example/foo',
            ],
            URL::alternateUrls([
                'en' => [
                    'id' => 'biz',
                    'unknown' => 'no-effect',
                    'array' => ['no-exception'],
                ]
            ])
        );
    }

    /**
     * @return void
     * @test
     */
    public function testAlternateUrlsOnNonLocalizedRoute(): void
    {
        $this->getRouter()->get('non-localized', function () {
            //
        });

        $this->get('non-localized?foo=bar');

        $this->assertEmpty(URL::alternateUrls());
    }

    /**
     * @return void
     * @test
     */
    public function testAlternateUrlsWithoutRoute(): void
    {
        $this->assertEmpty(URL::alternateUrls());
    }

    /**
     * @return void
     * @test
     */
    public function testAlternateFullUrls(): void
    {
        $this->get('fr/example?foo=bar');

        $this->assertEquals(
            [
                'en' => 'http://localhost/example?foo=bar',
                'es' => 'http://localhost/es/example?foo=bar',
            ],
            URL::alternateFullUrls()
        );
    }
}
