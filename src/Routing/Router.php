<?php namespace Exolnet\Translation\Routing;

use App;
use Closure;
use Exolnet\Translation\Http\Middleware\SetLocaleFromUrlSegment;
use Exolnet\Translation\LocaleService;
use Illuminate\Routing\Router as LaravelRouter;
use Mockery\Exception\RuntimeException;
use Redirect;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
        return $this->getLocaleService()->getLocalesAvailable();
    }

    /**
     * @return string
     */
    public function getBaseLocale()
    {
        return $this->getLocaleService()->getLocaleBase();
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
     * @param mixed       $action
     * @return \Exolnet\Translation\Routing\Route|\Illuminate\Routing\Route
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

    /**
     * @return \Exolnet\Translation\LocaleService
     */
    protected function getLocaleService()
    {
        if (! $this->localeService) {
            $this->localeService = $this->container->make(LocaleService::class);
        }

        return $this->localeService;
    }
}
