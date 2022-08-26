<?php

namespace Exolnet\Translation\Routing;

use Illuminate\Routing\Route as LaravelRoute;
use Illuminate\Support\Facades\Lang;

class RouteLocalized extends LaravelRoute
{
    /**
     * @param array|string $methods
     * @param string $uri
     * @param \Closure|array $action
     * @param string $locale
     */
    public function __construct($methods, $uri, $action, string $locale)
    {
        parent::__construct($methods, $this->translateUri($uri, $locale), $action);

        $this->action['locale'] = $locale;
        $this->action['localeBaseUri'] = $this->buildLocaleBaseUri($uri);

        if (isset($this->action['as'])) {
            $this->action['as'] = $this->translateName($this->action['as']);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function name($name)
    {
        $name = $this->translateName($name);

        return parent::name($name);
    }

    /**
     * @param string $uri
     * @return string
     */
    protected function buildLocaleBaseUri(string $uri): string
    {
        $uri = preg_replace('#(?<=/|\A)' . $this->getLocale() . '(?:/|\Z)#', '', $uri) ?: '/';

        // The method parseUri was added in Laravel 7.x
        if (! method_exists($this, 'parseUri')) {
            return $uri;
        }

        return $this->parseUri($uri);
    }

    /**
     * @param string $name
     * @return string
     */
    protected function translateName(string $name): string
    {
        return $name . '.' . $this->getLocale();
    }

    /**
     * @param string $uri
     * @param string $locale
     * @return string
     */
    protected function translateUri(string $uri, string $locale): string
    {
        $parts = explode('/', $uri);

        $parts = array_map(function (string $part) use ($locale) {
            return $this->translateUriPart($part, $locale);
        }, $parts);

        return implode('/', $parts);
    }

    /**
     * @param string $part
     * @param string $locale
     * @return string
     */
    protected function translateUriPart(string $part, string $locale): string
    {
        $translationKey = 'routes.' . $part;
        $translation = Lang::get($translationKey, [], $locale);

        if ($translation === $translationKey) {
            return $part;
        }

        return $translation;
    }
}
