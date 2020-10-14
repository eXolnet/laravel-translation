<?php

namespace Exolnet\Translation\Tests\Integration;

use Illuminate\Routing\Route;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->app['config']->set(
            'translation.available_locales',
            [
                'en' => ['system' => ['en_CA.UTF-8']],
                'fr' => ['system' => ['fr_CA.UTF-8']],
                'es' => [],
            ]
        );
    }

    /**
     * @return \Exolnet\Translation\Routing\Router
     */
    protected function getRouter()
    {
        return $this->app['router'];
    }

    /**
     * @return array
     */
    protected function getRegisteredRoutes(): array
    {
        return $this->getRouter()->getRoutes()->getRoutes();
    }

    /**
     * @return array
     */
    protected function getRegisteredRouteNames(): array
    {
        return collect($this->getRegisteredRoutes())
            ->map(function (Route $route) {
                return $route->getName();
            })
            ->all();
    }

    /**
     * @return array
     */
    protected function getRegisteredRouteUris(): array
    {
        return collect($this->getRegisteredRoutes())->pluck('uri')->all();
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
