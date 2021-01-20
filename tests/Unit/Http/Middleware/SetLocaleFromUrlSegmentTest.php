<?php

namespace Exolnet\Translation\Tests\Unit\Http\Middleware;

use Exolnet\Translation\Facades\LaravelTranslation;
use Exolnet\Translation\Http\Middleware\SetLocaleFromUrlSegment;
use Exolnet\Translation\LocaleService;
use Exolnet\Translation\Tests\Unit\UnitTest;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Mockery;

/**
 * @covers \Exolnet\Translation\Http\Middleware\SetLocaleFromUrlSegment
 */
class SetLocaleFromUrlSegmentTest extends UnitTest
{
    /**
     * @var \Illuminate\Foundation\Application|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    protected $app;

    /**
     * @var \Exolnet\Translation\LocaleService|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    protected $localeService;

    /**
     * @var \Illuminate\Http\Request|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    protected $request;

    /**
     * @var \Exolnet\Translation\Http\Middleware\SetLocaleFromUrlSegment
     */
    protected $middleware;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->app = Mockery::mock(Application::class);
        $this->localeService = Mockery::mock(LocaleService::class);
        $this->request = Mockery::mock(Request::class);

        $this->middleware = new SetLocaleFromUrlSegment($this->app, $this->localeService);
    }

    /**
     * @return void
     * @test
     */
    public function testLocaleCanBeConfiguredFromSegment(): void
    {
        // Setup
        $this->request->shouldReceive('segment')->with(1)->once()->andReturn('fr');
        $this->localeService->shouldReceive('getLocales')->withNoArgs()->once()->andReturn(['en', 'fr']);
        $this->app->shouldReceive('setLocale')->with('fr')->once();

        // Perform test
        $response = $this->middleware->handle($this->request, function ($request) {
            $this->assertEquals($this->request, $request);

            return 'foo';
        });

        // Assertions
        $this->assertEquals('foo', $response);
    }

    /**
     * @return void
     * @test
     */
    public function testLocaleFromCustomSegment(): void
    {
        // Setup
        $this->request->shouldReceive('segment')->with(2)->once()->andReturn('fr');
        $this->localeService->shouldReceive('getLocales')->withNoArgs()->once()->andReturn(['en', 'fr']);
        $this->app->shouldReceive('setLocale')->with('fr')->once();

        // Perform test
        $response = $this->middleware->handle(
            $this->request,
            function ($request) {
                $this->assertEquals($this->request, $request);

                return 'foo';
            },
            2
        );

        // Assertions
        $this->assertEquals('foo', $response);
    }

    /**
     * @return void
     * @test
     */
    public function testLocaleFallbackOnCurrentLocale(): void
    {
        // Setup
        $this->request->shouldReceive('segment')->with(1)->once()->andReturn('invalid');
        $this->localeService->shouldReceive('getLocales')->withNoArgs()->once()->andReturn(['en', 'fr']);

        // Perform test
        $response = $this->middleware->handle($this->request, function ($request) {
            $this->assertEquals($this->request, $request);

            return 'foo';
        });

        // Assertions
        $this->assertEquals('foo', $response);
    }

    /**
     * @return void
     * @test
     */
    public function testLocaleWithoutSegmentFallbackOnCurrentLocale(): void
    {
        // Setup
        $this->request->shouldReceive('segment')->with(1)->once()->andReturnNull();
        $this->localeService->shouldReceive('getLocales')->withNoArgs()->once()->andReturn(['en', 'fr']);

        // Perform test
        $response = $this->middleware->handle($this->request, function ($request) {
            $this->assertEquals($this->request, $request);

            return 'foo';
        });

        // Assertions
        $this->assertEquals('foo', $response);
    }
}
