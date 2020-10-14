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
     * @return array
     */
    public function getLocaleConfig(string $locale): array
    {
        return array_merge(
            $this->buildLocaleInformationDefault($locale),
            $this->getLocalesConfig()[$locale] ?? []
        );
    }

    /**
     * @param string $locale
     */
    public function setSystemLocale(string $locale): void
    {
        if (! $systemConfig = $this->getLocaleConfig($locale)['system']) {
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
