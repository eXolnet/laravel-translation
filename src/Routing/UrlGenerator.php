<?php namespace Exolnet\Translation\Routing;

use App;
use Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\UrlGenerator as LaravelUrlGenerator;

class UrlGenerator extends LaravelUrlGenerator
{
    /**
     * @param string $name
     * @param mixed $parameters
     * @param bool $absolute
     * @param string|null $locale
     * @return string
     */
    public function route($name, $parameters = [], $absolute = true, $locale = null)
    {
        $name = $this->getBestRouteName($name, $locale);

        return parent::route($name, $parameters, $absolute);
    }

    /**
     * @param array $parameters
     * @param bool $append
     * @return string
     */
    public function relative(array $parameters = [], $append = true)
    {
        $uri = $this->current();

        if ($append && $this->request->getQueryString()) {
            $parameters = array_merge($this->request->query(), $parameters);
        }

        $parameters = array_filter($parameters);

        if (count($parameters) > 0) {
            $uri .= '?'. http_build_query($parameters);
        }

        return $uri;
    }

    /**
     * @return string
     */
    public function canonical()
    {
        return $this->full();
    }

    // Is it possible to do something for this kind of method?
    // public function to($path, $extra = array(), $secure = null)
    // {
    //
    // }

    // Is it possible to do something for this kind of method?
    // public function action($action, $parameters = array(), $absolute = true)
    // {
    //
    // }

    /**
     * @param string $path
     * @param bool|null $secure
     * @return string
     */
    public function cdn($path, $secure = null)
    {
        $root = $this->getCdnUrl($secure);

        return $this->removeIndex($root) . '/' . trim($path, '/');
    }

    /**
     * @param bool|null $secure
     * @return string
     */
    public function getCdnUrl($secure = null)
    {
        $cdn_url = Config::get('app.cdn_url');

        return $this->getRootUrl($this->getScheme($secure), $cdn_url);
    }

    /**
     * @param \Exolnet\Routing\Route $route
     * @return array
     */
    public function alternateRoutes(Route $route)
    {
        $alternates = [];

        foreach ($this->routes as $alternate) {
            if (! $alternate instanceof Route) {
                continue;
            }

            if ($route->isAlternate($alternate)) {
                $alternates[] = $alternate;
            }
        }

        return $alternates;
    }

    /**
     * @param array $alternateParameters
     * @param bool  $absolute
     * @param bool  $addParameters
     * @return array
     */
    public function alternates(array $alternateParameters = array(), $absolute = true, $addParameters = false)
    {
        $currentRoute = $this->request->route();

        if (! $currentRoute || ! $currentRoute instanceof Route) {
            return [];
        }

        $routeAlternates = $currentRoute->alternates();

        if (count($routeAlternates) === 0) {
            return [];
        }

        $currentParameters = $currentRoute->parameters();
        $alternates        = [];

        /** @var \Exolnet\Routing\Route $route */
        foreach ($routeAlternates as $route) {
            $locale = $route->getLocale();

            $parameters = array_key_exists($locale, $alternateParameters)
                ? $alternateParameters[$locale] + $currentParameters
                : $currentParameters;

            if (! $addParameters) {
                $parameters = array_intersect_key($parameters, $currentParameters);
            }

            $alternates[$locale] = $this->toRoute($route, $parameters, $absolute);
        }

        return $alternates;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $translatableModel
     * @param callable                            $callback
     * @return mixed
     */
    public function buildAlternateParameters(Model $translatableModel, callable $callback)
    {
        return $translatableModel->buildAlternateParameters($callback);
    }

    /**
     * @param string $name
     * @param string|null $locale
     * @return string
     */
    protected function getBestRouteName($name, $locale = null)
    {
        if ($this->routes->getByName($name) !== null) {
            return $name;
        }

        // Check for a route with the current locale
        if ($locale === null) {
            $locale = App::getLocale();
        }

        if ($locale === null) {
            return $name;
        }

        $localeName = $name . '.' . $locale;

        if ($this->routes->getByName($localeName) !== null) {
            return $localeName;
        }

        return $name;
    }
}
