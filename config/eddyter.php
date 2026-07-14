<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Eddyter API Key
    |--------------------------------------------------------------------------
    |
    | This key is passed to the browser and used by the Eddyter SDK.
    | Eddyter performs automatic key validation by default.
    |
    */

    'api_key' => env('EDDYTER_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Default Editor Options
    |--------------------------------------------------------------------------
    |
    | Global defaults merged into every <x-eddyter /> instance.
    | Per-component props and the :options array override these values.
    |
    | Supported keys mirror @eddyter/core EddyterInitOptions (except
    | container, apiKey, initialContent, and JS-only callbacks).
    |
    */

    'defaults' => [
        // 'user' => [
        //     'id' => 'anonymous',
        //     'name' => 'Anonymous',
        // ],

        // 'mode' => 'edit',
        // 'darkMode' => null,

        // 'toolbar' => [
        //     'mode' => 'sticky',
        //     'offset' => 20,
        //     'zIndex' => 1000,
        // ],

        // 'editor' => [
        //     'maxHeight' => null,
        // ],

        // 'defaultFontFamilies' => [],
        // 'mentionUserList' => [],
    ],
];
