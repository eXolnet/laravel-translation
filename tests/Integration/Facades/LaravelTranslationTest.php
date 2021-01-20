<?php

namespace Exolnet\Translation\Tests\Integration\Facades;

use Exolnet\Translation\Facades\LaravelTranslation;
use Exolnet\Translation\LocaleService;
use Exolnet\Translation\Tests\Integration\TestCase;

class LaravelTranslationTest extends TestCase
{
    /**
     * @return void
     * @test
     */
    public function testFacadePointsToTheLocaleService(): void
    {
        $facadeRoot = LaravelTranslation::getFacadeRoot();

        $this->assertInstanceOf(LocaleService::class, $facadeRoot);
    }
}
