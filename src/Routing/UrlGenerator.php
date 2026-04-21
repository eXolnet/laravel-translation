<?php

namespace Exolnet\Translation\Routing;

use Illuminate\Routing\Route;
use Illuminate\Routing\UrlGenerator as LaravelUrlGenerator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;

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
     * @param array|null $queryOnly
     * @param array|null $queryExcept
     * @return array
     */
    public function alternateFullUrls(
        array $alternateParametersByLocale = [],
        ?array $queryOnly = null,
        ?array $queryExcept = null
    ): array {
        $query = $this->queryStringFiltered($queryOnly, $queryExcept);

        return array_map(
            function ($url) use ($query) {
                return $query ? $url . '?' . $query : $url;
            },
            $this->alternateUrls($alternateParametersByLocale)
        );
    }

    /**
     * @param array|null $queryOnly
     * @param array|null $queryExcept
     * @return string|null
     */
    protected function queryStringFiltered(?array $queryOnly = null, ?array $queryExcept = null): ?string
    {
        $query = $this->request->query();

        if (! $query) {
            return null;
        }

        $queryOnly ??= Config::get('translation.alternate_urls.query.only');
        $queryExcept ??= Config::get('translation.alternate_urls.query.except');

        if (isset($queryOnly)) {
            $query = Arr::only($query, $queryOnly);
        }

        if (isset($queryExcept)) {
            $query = Arr::except($query, $queryExcept);
        }

        if (empty($query)) {
            return null;
        }

        return http_build_query($query);
    }
}
