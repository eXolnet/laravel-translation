<?php

namespace Integration\Http\Middleware;

use Exolnet\Translation\Http\Middleware\SetLocaleFromUrlSegment;
use Exolnet\Translation\Tests\Integration\TestCase;
use Illuminate\Support\Facades\App;

class SetLocaleFromUrlSegmentTest extends TestCase
{
    /**
     * @return void
     * @test
     */
    public function testLocaleCanBeConfiguredFromSegment(): void
    {
        $this->getRouter()
            ->fallback(function () {
                return App::getLocale();
            })
            ->middleware(SetLocaleFromUrlSegment::class);

        $this->get('fr/test');
        $this->assertEquals('fr', $this->app->getLocale());

        $this->get('en/test');
        $this->assertEquals('en', $this->app->getLocale());

        $this->get('es/test');
        $this->assertEquals('es', $this->app->getLocale());
    }

    /**
     * @return void
     * @test
     */
    public function testLocaleFromCustomSegement(): void
    {
        $this->getRouter()
            ->fallback(function () {
                return App::getLocale();
            })
            ->middleware(SetLocaleFromUrlSegment::class . ':2');

        $this->get('test/fr/test');
        $this->assertEquals('fr', $this->app->getLocale());

        $this->get('test/es/test');
        $this->assertEquals('es', $this->app->getLocale());
    }

    /**
     * @return void
     * @test
     */
    public function testLocaleFallbackOnCurrentLocale(): void
    {
        $this->getRouter()
            ->fallback(function () {
                return App::getLocale();
            })
            ->middleware(SetLocaleFromUrlSegment::class);

        $this->get('es/test');
        $this->assertEquals('es', $this->app->getLocale());

        $this->get('invalid/test');
        $this->assertEquals('es', $this->app->getLocale());
    }

    /**
     * @return void
     * @test
     */
    public function testLocaleWithoutSegmentFallbackOnCurrentLocale(): void
    {
        $this->getRouter()
            ->fallback(function () {
                return App::getLocale();
            })
            ->middleware(SetLocaleFromUrlSegment::class);

        $this->get('en/test');
        $this->assertEquals('en', $this->app->getLocale());

        $this->get('/')->assertSuccessful();
        $this->assertEquals('en', $this->app->getLocale());
    }
}
