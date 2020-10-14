<?php

namespace Exolnet\Translation\Listeners;

use Exolnet\Translation\LocaleService;
use Illuminate\Foundation\Events\LocaleUpdated;

class LocaleUpdatedListener
{
    /**
     * Locale service.
     *
     * @var \Exolnet\Translation\LocaleService
     */
    protected $localeService;

    /**
     * LocaleUpdatedListener constructor.
     * @param \Exolnet\Translation\LocaleService $localeService
     */
    public function __construct(LocaleService $localeService)
    {
        $this->localeService = $localeService;
    }

    /**
     * Handle the event.
     *
     * @param \Illuminate\Foundation\Events\LocaleUpdated $event
     * @return void
     * @throws \Exolnet\Translation\TranslationException
     */
    public function handle(LocaleUpdated $event)
    {
        $this->localeService->setSystemLocale($event->locale);
    }
}
