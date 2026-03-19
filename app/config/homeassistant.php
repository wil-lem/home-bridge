<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Home Assistant URL
    |--------------------------------------------------------------------------
    |
    | The base URL of your Home Assistant instance.
    |
    */
    'url' => env('HOME_ASSISTANT_URL', 'http://homeassistant.local:8123'),

    /*
    |--------------------------------------------------------------------------
    | Home Assistant API Key
    |--------------------------------------------------------------------------
    |
    | Long-lived access token for Home Assistant API authentication.
    | Generate this from your Home Assistant profile.
    |
    */
    'api_key' => env('HOME_ASSISTANT_API_KEY'),
];
