<?php

namespace Exolnet\Translation\Http\Middleware;

use Closure;
use Exolnet\Translation\LocaleService;
use Illuminate\Foundation\Application;

class SetLocaleFromUrlSegment
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
     * @param \Exolnet\Translation\LocaleService $localeService
     */
    public function __construct(Application $app, LocaleService $localeService)
    {
        $this->app = $app;
        $this->localeService = $localeService;
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param int $segment
     * @return mixed
     */
    public function handle($request, Closure $next, int $segment = 1)
    {
        $locale = $request->segment($segment);

        if (in_array($locale, $this->localeService->getLocales())) {
            $this->app->setLocale($locale);
        }

        return $next($request);
    }
}
