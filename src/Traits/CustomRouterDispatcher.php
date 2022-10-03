<?php

namespace Exolnet\Translation\Traits;

use Exolnet\Translation\Http\Middleware\SetLocaleFromRouteLocalized;

trait CustomRouterDispatcher
{
    /**
     * Get the route dispatcher callback.
     *
     * @return \Closure
     */
    protected function dispatchToRouter()
    {
        /** @var \Illuminate\Routing\Router $oldRouter */
        $oldRouter = $this->router;
        /** @var \Exolnet\Translation\Routing\Router $newRouter */
        $newRouter = $this->app['router'];

        foreach ($this->middlewareGroups as $key => $middlewares) {
            $newRouter->middlewareGroup($key, $middlewares);
        }

        foreach ($oldRouter->getMiddleware() as $key => $middleware) {
            $newRouter->aliasMiddleware($key, $middleware);
        }

        $newRouter->middlewarePriority = $oldRouter->middlewarePriority;

        array_unshift($newRouter->middlewarePriority, SetLocaleFromRouteLocalized::class);

        $this->router = $newRouter;

        return parent::dispatchToRouter();
    }
}
