<?php

namespace Exolnet\Translation\Tests\Integration\Facades;

use Exolnet\Translation\Facades\LaravelTranslation;
use Exolnet\Translation\LocaleService;
use Exolnet\Translation\Tests\Integration\TestCase;

class LaravelTranslationTest extends TestCase
{
    public function testFacadePointToTheLocaleService(): void
    {
        $facaderoot = LaravelTranslation::getFacadeRoot();

        $this->assertInstanceOf(LocaleService::class, $facaderoot);
    }
}
