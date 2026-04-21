<?php

namespace Exolnet\Translation\Tests\Integration\Routing;

use Exolnet\Translation\Routing\UrlGenerator;
use Exolnet\Translation\Tests\Integration\TestCase;
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
     */
    public function testCustomUrlGeneratorIsRegistered(): void
    {
        $this->assertInstanceOf(UrlGenerator::class, $this->app['url']);
    }

    /**
     * @return void
     */
    public function testFindLocalizedRouteByName(): void
    {
        $this->assertEquals('http://localhost/example', URL::route('example', ['locale' => 'en']));
        $this->assertEquals('http://localhost/fr/example', URL::route('example', ['locale' => 'fr']));
        $this->assertEquals('http://localhost/es/example', URL::route('example', ['locale' => 'es']));
    }

    /**
     * @return void
     */
    public function testRouteByNameWithoutLocalization(): void
    {
        $this->assertEquals('http://localhost', URL::route('home'));
    }

    /**
     * @return void
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
     */
    public function testAlternateUrlsWithoutRoute(): void
    {
        $this->assertEmpty(URL::alternateUrls());
    }

    /**
     * @return void
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

    /**
     * @return void
     */
    public function testAlternateFullUrlsWithoutQuery(): void
    {
        $this->get('fr/example');

        $this->assertEquals(
            [
                'en' => 'http://localhost/example',
                'es' => 'http://localhost/es/example',
            ],
            URL::alternateFullUrls([], ['foo'], ['bar'])
        );
    }

    /**
     * @return void
     */
    public function testAlternateFullUrlsWithQueryOnly(): void
    {
        $this->get('fr/example?foo=bar&baz=qux&page=2');

        $this->assertEquals(
            [
                'en' => 'http://localhost/example?foo=bar&page=2',
                'es' => 'http://localhost/es/example?foo=bar&page=2',
            ],
            URL::alternateFullUrls([], ['foo', 'page'])
        );
    }

    /**
     * @return void
     */
    public function testAlternateFullUrlsWithQueryExcept(): void
    {
        $this->get('fr/example?foo=bar&baz=qux&page=2');

        $this->assertEquals(
            [
                'en' => 'http://localhost/example?foo=bar&page=2',
                'es' => 'http://localhost/es/example?foo=bar&page=2',
            ],
            URL::alternateFullUrls([], null, ['baz'])
        );
    }

    /**
     * @return void
     */
    public function testAlternateFullUrlsWithQueryOnlyAndExcept(): void
    {
        $this->get('fr/example?foo=bar&baz=qux&page=2');

        $this->assertEquals(
            [
                'en' => 'http://localhost/example?foo=bar',
                'es' => 'http://localhost/es/example?foo=bar',
            ],
            URL::alternateFullUrls([], ['foo', 'baz'], ['baz'])
        );
    }

    /**
     * @return void
     */
    public function testAlternateFullUrlsWithQueryOnlyEmpty(): void
    {
        $this->get('fr/example?foo=bar&baz=qux');

        $this->assertEquals(
            [
                'en' => 'http://localhost/example',
                'es' => 'http://localhost/es/example',
            ],
            URL::alternateFullUrls([], [])
        );
    }

    /**
     * @return void
     */
    public function testAlternateFullUrlsWithGlobalQueryExcept(): void
    {
        $this->app['config']->set('translation.alternate_urls.query.except', ['baz']);

        $this->get('fr/example?foo=bar&baz=qux&page=2');

        $this->assertEquals(
            [
                'en' => 'http://localhost/example?foo=bar&page=2',
                'es' => 'http://localhost/es/example?foo=bar&page=2',
            ],
            URL::alternateFullUrls()
        );
    }

    /**
     * @return void
     */
    public function testAlternateFullUrlsWithGlobalQueryOnly(): void
    {
        $this->app['config']->set('translation.alternate_urls.query.only', ['foo']);

        $this->get('fr/example?foo=bar&baz=qux&page=2');

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
     */
    public function testAlternateFullUrlsLocalOverridesGlobalConfig(): void
    {
        $this->app['config']->set('translation.alternate_urls.query.except', ['foo', 'baz']);

        $this->get('fr/example?foo=bar&baz=qux&page=2');

        $this->assertEquals(
            [
                'en' => 'http://localhost/example?foo=bar&page=2',
                'es' => 'http://localhost/es/example?foo=bar&page=2',
            ],
            URL::alternateFullUrls([], null, ['baz'])
        );
    }

    /**
     * @return void
     */
    public function testAlternateFullUrlsWithNoQueryString(): void
    {
        $this->get('fr/example');

        $this->assertEquals(
            [
                'en' => 'http://localhost/example',
                'es' => 'http://localhost/es/example',
            ],
            URL::alternateFullUrls([], ['foo'])
        );
    }
}
