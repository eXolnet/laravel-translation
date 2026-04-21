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
        'en' => ['system' => ['en_CA.UTF-8']],
    ],

    /*
    |--------------------------------------------------------------------------
    | Alternate URLs
    |--------------------------------------------------------------------------
    |
    | Configure how alternate URLs are generated. You can filter which query
    | parameters are included in the alternate URLs by using a whitelist
    | (only) and/or a blacklist (except).
    |
    | When both "only" and "except" are set, the whitelist is applied first,
    | then the blacklist removes from the remaining parameters.
    |
    | Set to null to disable filtering (default behavior).
    |
    */

    'alternate_urls' => [
        'query' => [
            'only' => null,
            'except' => null,
        ],
    ],

];
