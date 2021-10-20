<?php

namespace Exolnet\Translation\Tests\Integration;

use Exolnet\Translation\Routing\UrlGenerator;
use Illuminate\Support\Facades\URL;

class UrlGeneratorTest extends TestCase
{
    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->getRouter()->groupLocales(function () {
            $this->getRouter()->get('/')->name('home');

            $this->getRouter()->get('/example', function () {
                //
            })->name('example');

            $this->getRouter()->get('/show/{id}', function () {
                //
            })->name('show');

            $this->getRouter()->view('view', 'blade-view');
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

    /**
     * @return void
     * @test
     */
    public function testAlternateFullUrlsForHiddenBaseLocale(): void
    {
        $this->get('example?foo=bar');

        $this->assertEquals(
            [
                'es' => 'http://localhost/es/example?foo=bar',
                'fr' => 'http://localhost/fr/example?foo=bar',
            ],
            URL::alternateFullUrls()
        );
    }

    /**
     * @return void
     * @test
     */
    public function testAlternateFullUrlsOnHome(): void
    {
        $this->get('fr?foo=bar');

        $this->assertEquals(
            [
                'en' => 'http://localhost?foo=bar',
                'es' => 'http://localhost/es?foo=bar',
            ],
            URL::alternateFullUrls()
        );
    }

    /**
     * @return void
     * @test
     */
    public function testAlternateFullUrlsOnHomeForHiddenBaseLocale(): void
    {
        $this->get('/?foo=bar');

        $this->assertEquals(
            [
                'es' => 'http://localhost/es?foo=bar',
                'fr' => 'http://localhost/fr?foo=bar',
            ],
            URL::alternateFullUrls()
        );
    }

    /**
     * @return void
     * @test
     */
    public function testAlternateFullUrlsWithParameters(): void
    {
        $this->get('show/1?foo=bar');

        $this->assertEquals(
            [
                'es' => 'http://localhost/es/show/1?foo=bar',
                'fr' => 'http://localhost/fr/show/1?foo=bar',
            ],
            URL::alternateFullUrls()
        );
    }

    /**
     * @return void
     * @test
     */
    public function testAlternateFullUrlsWithParametersAlternate(): void
    {
        $this->get('show/1?foo=bar');

        $this->assertEquals(
            [
                'es' => 'http://localhost/es/show/1?foo=bar',
                'fr' => 'http://localhost/fr/show/42?foo=bar',
            ],
            URL::alternateFullUrls(['fr' => ['id' => 42]])
        );
    }

    /**
     * @return void
     * @test
     */
    public function testAlternateFullUrlsForView(): void
    {
        $this->get('view?foo=bar');

        $this->assertEquals(
            [
                'es' => 'http://localhost/es/view?foo=bar',
                'fr' => 'http://localhost/fr/view?foo=bar',
            ],
            URL::alternateFullUrls()
        );
    }
}
