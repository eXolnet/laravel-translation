<?php

namespace Exolnet\Translation\Blade;

class LanguageBladeExtension
{
    /**
     * @return array
     */
    public function getDirectives()
    {
        return [
            'currentLocale' => [$this, 'getCurrentLocale'],
            'currentLocaleName' => [$this, 'getCurrentLocaleName'],
            'currentLocaleNativeName' => [$this, 'getCurrentLocaleNativeName'],
        ];
    }

    /**
     * @return string
     */
    public function getCurrentLocale()
    {
        return '<?php echo LaravelTranslation::getCurrentLocale(); ?>';
    }

    /**
     * @return string
     */
    public function getCurrentLocaleName()
    {
        return '<?php echo LaravelTranslation::getCurrentLocaleName(); ?>';
    }

    /**
     * @return string
     */
    public function getCurrentLocaleNativeName()
    {
        return '<?php echo LaravelTranslation::getCurrentLocaleNativeName(); ?>';
    }
}
