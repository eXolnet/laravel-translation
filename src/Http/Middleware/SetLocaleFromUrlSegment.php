<?php namespace Exolnet\Translation\Http\Middleware;

use Closure;
use Exolnet\Translation\LocaleService;
use Illuminate\Support\Facades\App;

class SetLocaleFromUrlSegment
{
    /**
     * @var \Exolnet\Translation\LocaleService
     */
    protected $localeService;

    /**
     * LocaleSetter constructor.
     *
     * @param \Exolnet\Translation\LocaleService $localeService
     */
    public function __construct(LocaleService $localeService)
    {
        $this->localeService = $localeService;
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $locale = $this->localeService->extractLocale($request);

        App::setLocale($locale);

        return $next($request);
    }
}
