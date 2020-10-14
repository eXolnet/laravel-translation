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
     * Returns current language.
     *
     * @return array
     * @throws \Exolnet\Translation\TranslationException
     */
    public function getCurrentLocaleInformation()
    {
        return $this->getLocaleInformation($this->getCurrentLocale());
    }

    /**
     * Returns current language.
     *
     * @param string|null $locale
     * @return array|null
     * @throws \Exolnet\Translation\TranslationException
     */
    public function getLocaleInformation(string $locale = null)
    {
        if (!$locale) {
            $locale = $this->getCurrentLocale();
        }

        if (!$this->isLocaleAvailable($locale)) {
            throw new TranslationException('Locale "' . $locale . '" is not in the available_locales array.');
        }

        return $this->getLocalesAvailable()[$locale];
    }

    /**
     * Returns current regional.
     *
     * @return string
     * @throws \Exolnet\Translation\TranslationException
     */
    public function getCurrentLocaleSystem()
    {
        return $this->getLocaleSystem();
    }

    /**
     * @param string $locale
     * @return string|null
     * @throws \Exolnet\Translation\TranslationException
     */
    public function getLocaleSystem(string $locale = null)
    {
        if (!$locale) {
            $locale = $this->getCurrentLocale();
        }

        return $this->getLocaleInformation($locale)['system'] ?? null;
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
        $systemConfig = (array) ($this->getLocaleSystem($locale) ?? []);

        if (! empty($systemConfig)) {
            setlocale(LC_COLLATE, ...$systemConfig);
            setlocale(LC_CTYPE, ...$systemConfig);
            setlocale(LC_MONETARY, ...$systemConfig);
            setlocale(LC_TIME, ...$systemConfig);
        }
    }

    /**
     * Return the current locale name
     * @return string
     * @throws \Exolnet\Translation\TranslationException
     */
    public function getCurrentLocaleName()
    {
        return $this->getLocaleName();
    }

    /**
     * Return the locale name for a specified locale
     * @param string|null $locale
     * @return string
     * @throws \Exolnet\Translation\TranslationException
     */
    public function getLocaleName(string $locale = null)
    {
        if (!$locale) {
            $locale = $this->getCurrentLocale();
        }

        if (!isset($this->getLocalesAvailable()[$locale])) {
            return '';
        }

        return array_get($this->getLocalesAvailable()[$locale], 'name', '');
    }

    /**
     * Return the current locale native name
     * @return string
     * @throws \Exolnet\Translation\TranslationException
     */
    public function getCurrentLocaleNativeName()
    {
        return $this->getLocaleNativeName();
    }

    /**
     * Return the locale native name for a specified locale
     * @param string|null $locale
     * @return string
     * @throws \Exolnet\Translation\TranslationException
     */
    public function getLocaleNativeName(string $locale = null)
    {
        if (!$locale) {
            $locale = $this->getCurrentLocale();
        }

        if (!isset($this->getLocalesAvailable()[$locale])) {
            return $this->getLocaleName($locale);
        }

        return array_get($this->getLocalesAvailable()[$locale], 'native', $this->getLocaleName($locale));
    }

    /**
     * Return the current locale native name
     * @return string
     * @throws \Exolnet\Translation\TranslationException
     */
    public function getCurrentLocaleScript()
    {
        return $this->getLocaleScript();
    }

    /**
     * Return the locale native name for a specified locale
     * @param string|null $locale
     * @return string
     * @throws \Exolnet\Translation\TranslationException
     */
    public function getLocaleScript(string $locale = null)
    {
        if (!$locale) {
            $locale = $this->getCurrentLocale();
        }

        if (!isset($this->getLocalesAvailable()[$locale])) {
            return '';
        }

        return array_get($this->getLocalesAvailable()[$locale], 'script', '');
    }

    /**
     * Returns current locale direction.
     *
     * @return string current locale direction
     * @throws \Exolnet\Translation\TranslationException
     */
    public function getCurrentLocaleDirection()
    {
        return $this->getLocaleDirection();
    }

    /**
     * Returns locale direction.
     *
     * @param string|null $locale
     * @return string current locale direction
     * @throws \Exolnet\Translation\TranslationException
     */
    public function getLocaleDirection(string $locale = null)
    {
        if (!$locale) {
            $locale = $this->getCurrentLocale();
        }

        switch ($this->getLocaleScript($locale)) {
            // Other (historic) RTL scripts exist, but this list contains the only ones in current use.
            case 'Arab':
            case 'Hebr':
            case 'Mong':
            case 'Tfng':
            case 'Thaa':
                return 'rtl';
            default:
                return 'ltr';
        }
    }
}
