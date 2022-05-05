<?php

namespace Exolnet\Translation;

use Closure;
use Exolnet\Translation\Listeners\LocaleUpdatedListener;
use Exolnet\Translation\Mixins\RouteMixin;
use Exolnet\Translation\Routing\Router;
use Exolnet\Translation\Routing\UrlGenerator;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Foundation\Events\LocaleUpdated;
use Illuminate\Routing\Route;
use Illuminate\Support\ServiceProvider;

class TranslationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->mergeConfigFrom($this->getPackagePath('config/translation.php'), 'translation');

        $this->app[LocaleService::class]->setSystemLocale($this->app->getLocale());
        $this->app[Dispatcher::class]->listen(LocaleUpdated::class, LocaleUpdatedListener::class);

        $this->loadTranslationsFrom($this->getPackagePath('resources/lang'), 'translation');
        $this->loadViewsFrom($this->getPackagePath('resources/views'), 'translation');

        if ($this->app->runningInConsole()) {
            $this->offerPublishing();
        }
    }

    /**
     * @return void
     */
    protected function offerPublishing(): void
    {
        $this->publishes([
            $this->getPackagePath('config/translation.php') => $this->app->configPath('translation.php'),
        ], 'translation-config');

        $this->publishes([
            $this->getPackagePath('resources/lang') => $this->app->langPath() . '/vendor/translation',
        ], 'translation-lang');

        $this->publishes([
            $this->getPackagePath('resources/views') => $this->app->resourcePath('views/vendor/translation'),
        ], 'translation-views');
    }

    /**
     * Register the application services.
     * @throws \ReflectionException
     */
    public function register()
    {
        $this->registerRouter();
        $this->registerUrlGenerator();
        $this->registerLocaleService();
        $this->registerMixins();
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
     * @return void
     * @throws \ReflectionException
     */
    protected function registerMixins(): void
    {
        Route::mixin(new RouteMixin());
    }

    /**
     * Register the URL generator service.
     *
     * @return void
     */
    protected function registerUrlGenerator()
    {
        $this->app->singleton('url', function (Container $app) {
            /** @var \Exolnet\Translation\Routing\Router $router */
            $router = $app['router'];

            $routes = $router->getRoutes();

            // The URL generator needs the route collection that exists on the router.
            // Keep in mind this is an object, so we're passing by references here
            // and all the registered routes will be available to the generator.
            $app->instance('routes', $routes);

            $url = new UrlGenerator(
                $routes,
                $app->rebinding('request', $this->requestRebinder()),
                $app['config']['app.asset_url']
            );

            // Next we will set a few service resolvers on the URL generator so it can
            // get the information it needs to function. This just provides some of
            // the convenience features to this URL generator like "signed" URLs.
            $url->setSessionResolver(function () {
                return $this->app['session'];
            });

            if (method_exists($url, 'setKeyResolver')) {
                $url->setKeyResolver(function () {
                    return $this->app->make('config')->get('app.key');
                });
            }

            // If the route collection is "rebound", for example, when the routes stay
            // cached for the application, we will need to rebind the routes on the
            // URL generator instance so it has the latest version of the routes.
            $app->rebinding('routes', function ($app, $routes) {
                $app['url']->setRoutes($routes);
            });

            return $url;
        });
    }

    protected function registerLocaleService()
    {
        $this->app->singleton(LocaleService::class, function (Container $app) {
            return new LocaleService($app['config']);
        });
    }

    /**
     * Get the URL generator request rebinder.
     *
     * @return \Closure
     */
    protected function requestRebinder(): Closure
    {
        return function ($app, $request) {
            $app['url']->setRequest($request);
        };
    }

    /**
     * @param string $path
     * @return string
     */
    protected function getPackagePath(string $path): string
    {
        return realpath(__DIR__ . '/../' . $path);
    }
}
