<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Locales Available
    |--------------------------------------------------------------------------
    |
    | This variable lists all translations that is supported by this
    | application. The application locale configuration will also be appended
    | to this list.
    |
    */

    'available_locales' => [
        'en' => ['regional' => 'en_CA'],
        'fr' => ['regional' => 'fr_CA']
    ],

    'locale_suffix' => env('LARAVELTRANSLATION_LOCALE_SUFFIX', '.UTF-8'),

];
