<?php

namespace Exolnet\Translation\Tests\Integration;

use Exolnet\Translation\TranslationServiceProvider;
use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    /**
     * Resolve application implementation.
     *
     * @return \Illuminate\Foundation\Application
     */
    protected function resolveApplication()
    {
        return tap(parent::resolveApplication(), function (Application $app) {
            $app->register(TranslationServiceProvider::class);
        });
    }
}
