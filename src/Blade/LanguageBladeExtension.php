<?php

namespace Exolnet\Translation\Blade;

use Exolnet\Translation\Facades\LaravelTranslation;

class LanguageBladeExtension implements BladeExtension
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
            'currentLocaleScript' => [$this, 'getCurrentLocaleScript'],
            'currentLocaleDirection' => [$this, 'getCurrentLocaleDirection'],
        ];
    }

    public function getConditionals()
    {
        return [
            'isrtl' => [$this, 'isRtl'],
            'isltr' => [$this, 'isLtr'],
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

    /**
     * @return string
     */
    public function getCurrentLocaleScript()
    {
        return '<?php echo LaravelTranslation::getCurrentLocaleScript(); ?>';
    }

    /**
     * @return string
     */
    public function getCurrentLocaleDirection()
    {
        return '<?php echo LaravelTranslation::getCurrentLocaleDirection(); ?>';
    }

    public function isRtl()
    {
        return !$this->isLtr();
    }

    public function isLtr()
    {
        return LaravelTranslation::getCurrentLocaleDirection() === "rtl";
    }
}
