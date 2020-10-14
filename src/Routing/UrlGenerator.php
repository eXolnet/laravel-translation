<?php namespace Exolnet\Translation\Routing;

use Illuminate\Contracts\Routing\UrlRoutable;
use Illuminate\Routing\Route;
use Illuminate\Routing\UrlGenerator as LaravelUrlGenerator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;

class UrlGenerator extends LaravelUrlGenerator
{
    /**
     * {@inheritDoc}
     */
    public function route($name, $parameters = [], $absolute = true, $locale = null)
    {
        $nameLocalized = $name . '.' . ($parameters['locale'] ?? App::getLocale());

        if (is_array($parameters) && array_key_exists('locale', $parameters)) {
            unset($parameters['locale']);
        }

        if (! is_null($route = $this->routes->getByName($nameLocalized))) {
            return $this->toRoute($route, $parameters, $absolute);
        }

        return parent::route($name, $parameters, $absolute);
    }

    /**
     * @param array $alternateParametersByLocale
     * @return array
     */
    public function alternateUrls(array $alternateParametersByLocale = []): array
    {
        $currentRoute = $this->request->route();

        if (! $currentRoute) {
            return [];
        }

        $alternatesRoutes = $currentRoute->getLocaleAlternates();
        $currentParameters = Arr::except($currentRoute->parameters(), ['view']);

        return array_map(
            function (Route $route) use ($currentParameters, $alternateParametersByLocale) {
                $locale = $route->getLocale();
                $parameters = $currentParameters;
                $alternateParameters = $alternateParametersByLocale[$locale] ?? [];

                foreach ($parameters as $key => $parameter) {
                    if (array_key_exists($key, $alternateParameters)) {
                        $parameter = $alternateParameters[$key];
                    }

                    if (method_exists($parameter, 'getRouteKeyLocalized')) {
                        $parameter = $parameter->getRouteKeyLocalized($locale);
                    }

                    $parameters[$key] = $parameter;
                }

                return $this->toRoute($route, $parameters, true);
            },
            $alternatesRoutes
        );
    }

    /**
     * @param array $alternateParametersByLocale
     * @return array
     */
    public function alternateFullUrls(array $alternateParametersByLocale = []): array
    {
        $query = $this->request->getQueryString();

        return array_map(
            function ($url) use ($query) {
                return $query ? $url . '?' . $query : $url;
            },
            $this->alternateUrls($alternateParametersByLocale)
        );
    }
}
