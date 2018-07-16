<?php namespace Exolnet\Translation;

use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Http\Request;

class LocaleService
{
    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    protected $config;

    /**
     * @param \Illuminate\Contracts\Config\Repository $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @return string
     */
    public function getLocaleBase()
    {
        return $this->config->get('app.locale', 'en');
    }

    /**
     * @return array
     */
    public function getLocalesAvailable()
    {
        $locales = $this->getLocalesAvailableBase();

        $locales[] = $this->getLocaleBase();

        return array_unique($locales);
    }

    /**
     * @param string $locale
     * @return bool
     */
    public function isLocaleAvailable($locale)
    {
        return in_array($locale, $this->getLocalesAvailable());
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return string
     */
    public function extractLocale(Request $request)
    {
        // 1. Try to extract the locale by with the first URI segment
        $locale = $request->segment(1);

        if ($this->isLocaleAvailable($locale)) {
            return $locale;
        }

        // Default locale
        return $this->getLocaleBase();
    }

    /**
     * @return array
     */
    protected function getLocalesAvailableBase()
    {
        return $this->config->get('translation.available_locales', []);
    }
}
