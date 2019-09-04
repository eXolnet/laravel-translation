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
     * @return mixed
     * @throws \Exolnet\Translation\TranslationException
     */
    public function handle($request, Closure $next)
    {
        $locale = $this->localeService->extractLocale($request);

        $this->app->setLocale($locale);

        return $next($request);
    }
}
