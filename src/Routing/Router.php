<?php namespace Exolnet\Translation\Routing;

use Exolnet\Translation\LocaleService;
use Illuminate\Routing\Router as LaravelRouter;
use Illuminate\Support\Facades\App;

class Router extends LaravelRouter
{
    /**
     * @var \Exolnet\Translation\LocaleService
     */
    protected $localeService;

    /**
     * @return array
     */
    public function getLocales(): array
    {
        return $this->getLocaleService()->getLocales();
    }

    /**
     * @return array
     */
    public function getAlternateLocales(): array
    {
        return array_values(array_diff($this->getLocales(), [App::getLocale()]));
    }

    /**
     * @return string
     */
    public function getLocaleBase(): string
    {
        return $this->getLocaleService()->getLocaleBase();
    }

    /**
     * @return string|null
     */
    public function getLastGroupLocale(): ?string
    {
        $group = end($this->groupStack);

        return $group['groupLocale'] ?? null;
    }

    /**
     * @param \Closure|string $routes
     * @param array $options
     * @return \Exolnet\Translation\Routing\PendingGroupLocalesRegistration
     */
    public function groupLocales($routes, array $options = []): PendingGroupLocalesRegistration
    {
        return new PendingGroupLocalesRegistration($this, $routes, $options);
    }

    /**
     * @param array|string $methods
     * @param string $uri
     * @param mixed $action
     * @return \Exolnet\Translation\Routing\RouteLocalized|\Illuminate\Routing\Route
     */
    public function newRoute($methods, $uri, $action)
    {
        if (! $locale = $this->getLastGroupLocale()) {
            return parent::newRoute($methods, $uri, $action);
        }

        return $this->newRouteLocalized($methods, $uri, $action, $locale);
    }

    /**
     * @param array|string $methods
     * @param string $uri
     * @param \Closure|array $action
     * @param string $locale
     * @return \Exolnet\Translation\Routing\RouteLocalized
     */
    public function newRouteLocalized($methods, $uri, $action, $locale)
    {
        return (new RouteLocalized($methods, $uri, $action, $locale))
            ->setRouter($this)
            ->setContainer($this->container);
    }

    /**
     * @return \Exolnet\Translation\LocaleService
     */
    protected function getLocaleService()
    {
        if (!$this->localeService) {
            $this->localeService = $this->container->make(LocaleService::class);
        }

        return $this->localeService;
    }
}
