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
     * Current locale.
     *
     * @var string
     */
    protected $currentLocale;

    /**
     * Availables locales.
     *
     * @var array
     */
    protected $availableLocales;

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
     * @throws \Exolnet\Translation\TranslationException
     */
    public function getLocalesAvailable()
    {
        if ($this->availableLocales) {
            return $this->availableLocales;
        }

        $locales = $this->getLocalesAvailableBase();

        if (!array_key_exists($this->getLocaleBase(), $locales)) {
            throw new TranslationException('Laravel default locale is not in the available_locales array.');
        }

        $this->availableLocales = $locales;

        return $this->availableLocales;
    }

    /**
     * @param string $locale
     * @return bool
     * @throws \Exolnet\Translation\TranslationException
     */
    public function isLocaleAvailable($locale)
    {
        return array_key_exists($locale, $this->getLocalesAvailable());
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return string
     * @throws \Exolnet\Translation\TranslationException
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

    /**
     * @return string
     */
    public function getLocaleSuffix()
    {
        return $this->config->get('translation.locale_suffix', '.utf8');
    }

    /**
     * Returns current language.
     *
     * @return string
     */
    public function getCurrentLocale()
    {
        if ($this->currentLocale) {
            return $this->currentLocale;
        }

        // or get application default language
        return $this->config->get('app.locale');
    }

    /**
     * Returns current regional.
     *
     * @return string
     * @throws \Exolnet\Translation\TranslationException
     */
    public function getCurrentLocaleRegional()
    {
        return $this->getLocaleRegional($this->getCurrentLocale());
    }

    /**
     * @param string $locale
     * @return string|null
     * @throws \Exolnet\Translation\TranslationException
     */
    public function getLocaleRegional(string $locale = null)
    {
        if (!$locale) {
            $locale = $this->getCurrentLocale();
        }

        if (isset($this->getLocalesAvailable()[$locale]['regional'])) {
            return $this->getLocalesAvailable()[$locale]['regional'];
        } else {
            //Support old versions
            return $this->getCurrentLocale() . '_CA';
        }
    }

    /**
     * @param string $locale
     * @throws \Exolnet\Translation\TranslationException
     */
    public function setCurrentLocale(string $locale)
    {
        if (!$this->isLocaleAvailable($locale)) {
            throw new TranslationException('Locale "' . $locale . '" is not in the available_locales array.');
        }
        $this->currentLocale = $locale;
        $this->setSystemLocale($locale);
    }

    /**
     * Set the system locale
     * @param string|null $locale
     * @throws \Exolnet\Translation\TranslationException
     */
    public function setSystemLocale(string $locale = null)
    {
        $suffix = $this->getLocaleSuffix();
        $regional = $this->getLocaleRegional($locale);
        setlocale(LC_ALL, $regional . $suffix);
    }
}
