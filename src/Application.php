<?php

namespace Exolnet\Translation;

use Illuminate\Foundation\Application as LaravelApplication;

class Application extends LaravelApplication
{
    /**
     * {@inheritdoc}
     */
    protected function registerBaseServiceProviders()
    {
        parent::registerBaseServiceProviders();

        $this->register(new TranslationServiceProvider($this));
    }
}
