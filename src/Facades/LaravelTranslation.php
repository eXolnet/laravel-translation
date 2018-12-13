<?php

namespace Exolnet\Translation\Facades;

use Exolnet\Translation\LocaleService;
use Illuminate\Support\Facades\Facade;

class LaravelTranslation extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return LocaleService::class;
    }
}
