<?php

namespace Exolnet\Translation\Routing;

use Illuminate\Routing\Route;
use Illuminate\Routing\UrlGenerator as LaravelUrlGenerator;
use Illuminate\Support\Facades\App;

class UrlGenerator extends LaravelUrlGenerator
{
    /**
     * {@inheritDoc}
     */
    public function route($name, $parameters = [], $absolute = true, $locale = null)
    {
        if (is_array($parameters) && array_key_exists('locale', $parameters)) {
            $locale = $parameters['locale'];
            unset($parameters['locale']);
        }

        $nameLocalized = $name . '.' . ($locale ?? App::getLocale());

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
        $currentParameters = $currentRoute->parameters();

        return array_map(
            function (Route $route) use ($currentParameters, $alternateParametersByLocale) {
                $locale = $route->getLocale();
                $parameters = [];

                foreach ($route->parameterNames() as $parameterName) {
                    $parameter = $alternateParametersByLocale[$locale][$parameterName]
                        ?? $currentParameters[$parameterName];

                    if (is_object($parameter) && method_exists($parameter, 'getRouteKeyLocalized')) {
                        $parameter = $parameter->getRouteKeyLocalized($locale);
                    }

                    $parameters[$parameterName] = $parameter;
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
