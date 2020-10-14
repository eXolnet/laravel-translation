<?php namespace Exolnet\Translation\Routing;

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
    public function __construct($methods, $uri, $action, $locale)
    {
        parent::__construct($methods, $uri, $action);

        $this->action['locale'] = $locale;
        $this->action['localeBaseUri'] = $this->buildLocaleBaseUri($uri);

        $this->uri = $this->translateUri($uri);

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
        return preg_replace('#(?<=/|\A)' . $this->getLocale() . '(?:/|\Z)#', '', $uri);
    }

    /**
     * @param string $name
     * @return string
     */
    protected function translateName(string $name): string
    {
        return $name .'.'. $this->getLocale();
    }

    /**
     * @param string $uri
     * @return string
     */
    protected function translateUri(string $uri): string
    {
        $parts = explode('/', $uri);

        $parts = array_map([$this, 'translateUriPart'], $parts);

        return implode('/', $parts);
    }

    /**
     * @param string $part
     * @return string
     */
    protected function translateUriPart(string $part): string
    {
        $translationKey = 'routes.' . $part;
        $translation = Lang::get($translationKey, [], $this->getLocale());

        if ($translation === $translationKey) {
            return $part;
        }

        return $translation;
    }
}
