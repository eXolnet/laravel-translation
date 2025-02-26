<?php

namespace Exolnet\Translation\Tests\Integration;

use Exolnet\Translation\LocaleService;
use Illuminate\Support\Facades\App;
use RuntimeException;

class LocaleServiceTest extends TestCase
{
    /**
     * @var \Exolnet\Translation\LocaleService
     */
    protected $localeService;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->localeService = app()->make(LocaleService::class);
    }

    /**
     * @return void
     */
    public function testPushLocale(): void
    {
        $this->assertEquals('en', App::getLocale());

        $this->localeService->pushLocale('fr', function () {
            $this->assertEquals('fr', App::getLocale());
        });

        $this->assertEquals('en', App::getLocale());
    }

    /**
     * @return void
     */
    public function testPushLocaleWithException(): void
    {
        try {
            $this->assertEquals('en', App::getLocale());
            $this->expectException(RuntimeException::class);

            $this->localeService->pushLocale('fr', function () {
                $this->assertEquals('fr', App::getLocale());

                throw new RuntimeException('Something happen');
            });
        } finally {
            $this->assertEquals('en', App::getLocale());
        }
    }
}
