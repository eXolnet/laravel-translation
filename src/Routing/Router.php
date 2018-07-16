<?php namespace Exolnet\Routing;

use App;
use Closure;
use Exolnet\Http\Middleware\SetLocaleFromUrlSegment;
use Exolnet\Translation\LocaleService;
use Exolnet\Translation\TranslationException;
use Illuminate\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Routing\Router as LaravelRouter;
use Mockery\Exception\RuntimeException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Redirect;

class Router extends LaravelRouter
{
    /**
     * @var array
     */
    protected $localeStack = [];

    /**
     * @var \Exolnet\Translation\LocaleService
     */
    protected $localeService;

    /**
     * Create a new Router instance.
     *
     * @param  \Illuminate\Contracts\Events\Dispatcher $events
     * @param  \Illuminate\Container\Container         $container
     */
    public function __construct(Dispatcher $events, Container $container = null)
    {
        parent::__construct($events, $container);

        $this->localeService = $this->container->make(LocaleService::class);
    }

    /**
     * @return string|null
     */
    protected function getLastLocale()
    {
        if (count($this->localeStack) === 0) {
            return null;
        }

        return end($this->localeStack);
    }

    /**
     * @return array
     */
    public function getLocales()
    {
        return $this->localeService->getSupportedLocales();
    }

    /**
     * @return string
     */
    public function getBaseLocale()
    {
        return $this->localeService->getBaseLocale();
    }

    /**
     * @return array
     */
    public function getAlternateLocales()
    {
        return array_filter($this->getLocales(), function ($locale) {
            return App::getLocale() !== $locale;
        });
    }

    /**
     * @param \Closure   $callback
     * @param array|null $locales
     * @param bool       $avoidPrefixOnBaseLocale
     */
    public function groupLocales(Closure $callback, array $locales = null, $avoidPrefixOnBaseLocale = false)
    {
        $this->stackLocales(function ($locale) use ($callback, $avoidPrefixOnBaseLocale) {
            $shouldPrefixLocale = ! $avoidPrefixOnBaseLocale || $this->getBaseLocale() !== $locale;
            $prefix = $shouldPrefixLocale ? $locale : '';

            $this->group(['prefix' => $prefix, 'middleware' => SetLocaleFromUrlSegment::class], $callback);
        }, $locales);
    }

    /**
     * @param \Closure   $callback
     * @param array|null $locales
     */
    public function stackLocales(Closure $callback, array $locales = null)
    {
        if ($locales === null) {
            $locales = $this->getLocales();
        }

        foreach ($locales as $locale) {
            $this->localeStack[] = $locale;

            $callback($locale);

            array_pop($this->localeStack);
        }
    }

    /**
     * @param array|string $methods
     * @param string       $uri
     * @param string       $action
     * @return \Exolnet\Routing\Route|\Illuminate\Routing\Route
     */
    protected function newRoute($methods, $uri, $action)
    {
        if (count($this->localeStack) === 0) {
            return parent::newRoute($methods, $uri, $action);
        }

        $locale = end($this->localeStack);

        // Since we use the "prefix", Laravel will automatically append it to auto generated
        // resources names. Thus, we may obtain routes named like this "admin.en.page.en" or
        // "en.page.en" (the local is in double). To avoid this, we replace all locales that are
        // not at the end.
        if (array_key_exists('as', $action)) {
            $action['as'] = preg_replace('/(^|\.)' . $locale . '\./', '\1', $action['as']);
        }

        $route = (new Route($methods, $uri, $action, $locale))->setContainer($this->container);

        if (method_exists($route, 'setRouter')) {
            $route->setRouter($this);
        }

        return $route;
    }

    //==========================================================================

    /**
     * @return array
     */
    public function currentAlternates()
    {
        $route = $this->current();

        if (! $route instanceof Route) {
            return [];
        }

        return $route->alternates();
    }

    //==========================================================================

    /**
     * @param string       $route
     * @param string|array $aliases
     * @throws \Mockery\Exception\RuntimeException
     */
    public function alias($route, $aliases)
    {
        $route = $this->getRoutes()->getByName($route);

        if ($route === null) {
            throw new RuntimeException('No route named "' . $route . '" found for alias.');
        }

        foreach ((array)$aliases as $alias) {
            $this->match($route->methods(), $alias, function () use ($route) {
                return Redirect::route($route->getName());
            });
        }
    }

    /**
     * @param               $key
     * @param \Closure      $query
     * @param \Closure|null $callback
     */
    public function bindQuery($key, Closure $query, Closure $callback = null)
    {
        $this->bind($key, function ($value) use ($query, $callback) {
            if ($value === null) {
                return;
            }

            if ($model = call_user_func($query, $value)) {
                return $model;
            }

            // If a callback was supplied to the method we will call that to determine
            // what we should do when the model is not found. This just gives these
            // developer a little greater flexibility to decide what will happen.
            if ($callback instanceof Closure) {
                return call_user_func($callback, $value);
            }

            throw new NotFoundHttpException;
        });
    }

    /**
     * @param string        $key
     * @param string        $class
     * @param \Closure|null $callback
     */
    public function bindSlugable($key, $class, Closure $callback = null)
    {
        $this->bindQuery($key, function ($value) use ($class) {
            // For model binders, we will attempt to retrieve the models using the first
            // method on the model instance. If we cannot retrieve the models we'll
            // throw a not found exception otherwise we will return the instance.
            $instance = $this->container->make($class);

            /** @var \Exolnet\Routing\Slugable $model */
            $model = $instance->where($instance->getRouteKeyName(), $value)->first();

            if ($model && $model->getSlug() === $value) {
                return $model;
            }

            return null;
        }, $callback);
    }
}
