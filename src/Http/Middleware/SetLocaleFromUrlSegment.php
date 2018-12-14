<?php namespace Exolnet\Translation\Http\Middleware;

use Closure;
use Exolnet\Translation\LocaleService;
use Illuminate\Support\Facades\App;

class SetLocaleFromUrlSegment
{
    /**
     * @var \Exolnet\Translation\LocaleService
     */
    private $localeService;

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

        // TODO-TR: Support something else than _CA.utf8 <trochette@exolnet.com>
        setlocale(LC_COLLATE, $locale . '_CA.utf8');
        setlocale(LC_CTYPE, $locale . '_CA.utf8');
        setlocale(LC_TIME, $locale . '_CA.utf8');

        return $next($request);
    }
}
