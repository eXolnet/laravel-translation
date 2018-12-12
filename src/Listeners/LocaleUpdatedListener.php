<?php

namespace Exolnet\Translation\Listeners;

use \Illuminate\Foundation\Events\LocaleUpdated;

class LocaleUpdatedListener
{
    /**
     * Handle the event.
     *
     * @param \Illuminate\Foundation\Events\LocaleUpdated $event
     * @return void
     */
    public function handle(LocaleUpdated $event)
    {
        setlocale(LC_ALL, $event->locale);
    }
}
