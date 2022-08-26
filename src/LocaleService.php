<?php

namespace Exolnet\Translation;

use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;

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
     * @return array
     */
    public function getLocales(): array
    {
        return $this->config->get('translation.locales') ?? array_keys($this->getLocalesConfig());
    }

    /**
     * @return string
     */
    public function getLocaleBase(): string
    {
        return $this->config->get('app.locale', 'en');
    }

    /**
     * @return array
     */
    public function getLocalesConfig(): array
    {
        return $this->config->get('translation.available_locales', []);
    }

    /**
     * @param string $locale
     * @param string|null $key
     * @param null $default
     * @return mixed
     */
    public function getLocaleConfig(string $locale, ?string $key = null, $default = null)
    {
        $config = array_merge(
            $this->buildLocaleInformationDefault($locale),
            $this->getLocalesConfig()[$locale] ?? []
        );

        if (! $key) {
            return $config;
        }

        return Arr::get($config, $key, $default);
    }

    /**
     * @param string $locale
     * @param callable $callback
     * @return mixed
     */
    public function pushLocale(string $locale, callable $callback)
    {
        $currentLocale = App::getLocale();

        try {
            App::setLocale($locale);

            return $callback();
        } finally {
            App::setLocale($currentLocale);
        }
    }

    /**
     * @param string $locale
     */
    public function setSystemLocale(string $locale): void
    {
        if (! $systemConfig = $this->getLocaleConfig($locale, 'system')) {
            return;
        }

        setlocale(LC_COLLATE, ...$systemConfig);
        setlocale(LC_CTYPE, ...$systemConfig);
        setlocale(LC_MONETARY, ...$systemConfig);
        setlocale(LC_TIME, ...$systemConfig);
    }

    /**
     * @param string $locale
     * @return array
     */
    protected function buildLocaleInformationDefault(string $locale): array
    {
        return [
            'system' => [$locale . '.UTF-8']
        ];
    }
}
