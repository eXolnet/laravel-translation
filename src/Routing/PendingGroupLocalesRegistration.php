<?php

namespace Exolnet\Translation\Routing;

use Exolnet\Translation\Http\Middleware\SetLocaleFromRouteLocalized;
use Exolnet\Translation\Http\Middleware\SetLocaleFromUrlSegment;
use Illuminate\Routing\Router as LaravelRouter;
use Illuminate\Support\Facades\App;

class PendingGroupLocalesRegistration
{
    /**
     * @var \Illuminate\Routing\Router
     */
    protected $router;

    /**
     * @var \Closure|string
     */
    protected $routes;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var bool
     */
    protected $registered = false;

    /**
     * @param \Illuminate\Routing\Router $router
     * @param \Closure|string $routes
     * @param array $options
     */
    public function __construct(LaravelRouter $router, $routes, array $options)
    {
        $this->router = $router;
        $this->routes = $routes;
        $this->options = $options;
    }

    /**
     * @param array $locales
     * @return $this
     */
    public function locales(array $locales): self
    {
        $this->options['locales'] = $locales;

        return $this;
    }

    /**
     * @param string|null $locale
     * @return $this
     */
    public function hiddenLocale(string $locale = null): self
    {
        $this->options['hidden_locale'] = $locale;

        return $this;
    }

    /**
     * @return $this
     */
    public function hiddenBaseLocale(): self
    {
        return $this->hiddenLocale($this->router->getLocaleBase());
    }

    /**
     * @return void
     */
    public function register(): void
    {
        $this->registered = true;

        $locales = $this->options['locales'] ?? $this->router->getLocales();
        $currentLocale = App::getLocale();

        foreach ($locales as $locale) {
            $attributes = [
                'groupLocale' => $locale,
                'prefix' => $locale,
                'middleware' => SetLocaleFromRouteLocalized::class,
            ];

            if (($this->options['hidden_locale'] ?? null) === $locale) {
                $attributes['prefix'] = '';
            }

            App::setLocale($locale);

            $this->router->group($attributes, $this->routes);
        }

        App::setLocale($currentLocale);
    }

    /**
     * @return void
     */
    public function __destruct()
    {
        if (! $this->registered) {
            $this->register();
        }
    }
}
