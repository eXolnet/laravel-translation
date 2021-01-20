<?php

namespace Exolnet\Translation\Tests\Integration\Http\Middleware;

use Exolnet\Translation\Http\Middleware\SetLocaleFromUrlSegment;
use Exolnet\Translation\Tests\Integration\TestCase;
use Illuminate\Support\Facades\App;

/**
 * @see \Exolnet\Translation\Http\Middleware\SetLocaleFromUrlSegment
 */
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

        $this->get('fr/example')->assertSee('fr');
    }

    /**
     * @return void
     * @test
     */
    public function testLocaleFromCustomSegment(): void
    {
        $this->getRouter()
            ->fallback(function () {
                return App::getLocale();
            })
            ->middleware(SetLocaleFromUrlSegment::class . ':2');

        $this->get('foo/es/bar')->assertSee('es');
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

        $this->get('invalid/example')->assertSee('en');


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

        $this->get('/')->assertSee('en');
    }
}
