<?php

namespace Exolnet\Translation\Tests\Integration;

use Exolnet\Translation\TranslationServiceProvider;
use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    /**
     * @return \Exolnet\Translation\Routing\Router
     */
    protected function getRouter()
    {
        return $this->app['router'];
    }

    /**
     * @return \Exolnet\Translation\Routing\UrlGenerator
     */
    protected function getUrlGenerator()
    {
        return $this->app['url'];
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return ['Exolnet\Translation\TranslationServiceProvider'];
    }

    /**
     * Get package aliases.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return [
            'config' => 'Illuminate\Config\Repository'
        ];
    }
}
