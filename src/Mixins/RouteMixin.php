<?php

namespace Exolnet\Translation\Mixins;

use Closure;
use Illuminate\Routing\Route;

/**
 * @mixin \Illuminate\Routing\Route
 */
class RouteMixin
{
    /**
     * @return \Closure
     */
    public function getLocale(): Closure
    {
        /**
         * @return ?string
         */
        return function (): ?string {
            return $this->action['locale'] ?? null;
        };
    }

    /**
     * @return \Closure
     */
    public function getLocaleAlternates(): Closure
    {
        /**
         * @return array
         */
        return function (): array {
            if (! $this->isLocalized()) {
                return [];
            }

            $allRoutes = $this->router->getRoutes()->getRoutes();

            return collect($allRoutes)
                ->filter(function (Route $route) {
                    return $this->isLocaleAlternate($route);
                })
                ->keyBy(function (Route $route) {
                    return $route->getLocale();
                })
                ->all();
        };
    }

    /**
     * @return \Closure
     */
    public function getLocaleBaseUri(): Closure
    {
        /**
         * @return ?string
         */
        return function (): ?string {
            return $this->action['localeBaseUri'] ?? null;
        };
    }

    /**
     * @return \Closure
     */
    public function isLocalized(): Closure
    {
        /**
         * @return bool
         */
        return function (): bool {
            return $this->getLocale() !== null;
        };
    }

    /**
     * @return \Closure
     */
    public function isLocaleAlternate(): Closure
    {
        return function (Route $route) {
            if ($this->getLocaleBaseUri() !== $route->getLocaleBaseUri()) {
                return false;
            }

            if ($this->getLocale() === $route->getLocale()) {
                return false;
            }

            // Validate methods
            if ($this->methods() != $route->methods()) {
                return false;
            }

            // Validate scheme
            if ($this->httpOnly() !== $route->httpOnly()) {
                return false;
            }

            if ($this->getDomain() !== $route->getDomain()) {
                return false;
            }

            return true;
        };
    }
}
