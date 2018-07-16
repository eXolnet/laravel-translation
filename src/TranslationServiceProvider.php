<?php

namespace Exolnet\Translation;

use Exolnet\Routing\Router;
use Exolnet\Routing\UrlGenerator;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Support\ServiceProvider;

class TranslationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/translation.php' => config_path('translation.php'),
        ], 'config');
    }

    /**
     * Register the application services.
     *
     * @throws \Exolnet\Translation\TranslationException
     */
    public function register()
    {
        if ($this->app->resolved(HttpKernel::class)) {
            throw new TranslationException('TranslationServiceProvider should be registered before loading the Kernel');
        }

        if (! $this->app->has('routes')) {
            throw new TranslationException(
                'TranslationServiceProvider should be registered after creating the application'
            );
        }

        $this->mergeConfigFrom(__DIR__.'/../config/translation.php', 'translation');

        $this->registerRouter();
        $this->registerUrlGenerator();
    }

    /**
     * Register the router instance.
     *
     * @return void
     */
    protected function registerRouter()
    {
        $this->app->singleton('router', function ($app) {
            return new Router($app['events'], $app);
        });
    }

    /**
     * Register the URL generator service.
     *
     * @return void
     */
    protected function registerUrlGenerator()
    {
        $this->app->singleton('url', function (Container $app) {
            $url = new UrlGenerator($app['routes'], $app['request'] ?? null);

            $url->setSessionResolver(function () {
                return $this->app['session'];
            });

            return $url;
        });
    }
}
