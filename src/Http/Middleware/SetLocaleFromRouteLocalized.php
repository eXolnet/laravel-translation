<?php

namespace Exolnet\Translation\Http\Middleware;

use Closure;
use Illuminate\Foundation\Application;

class SetLocaleFromRouteLocalized
{
    /**
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * @var \Exolnet\Translation\LocaleService
     */
    protected $localeService;

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param int $segment
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($locale = $request->route()->getLocale()) {
            $this->app->setLocale($locale);
        }

        return $next($request);
    }
}
