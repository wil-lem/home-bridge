<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Kitchen Owl URL
    |--------------------------------------------------------------------------
    |
    | The base URL of your Kitchen Owl instance.
    |
    */
    'url' => env('KITCHEN_OWL_URL', 'http://kitchenowl.local'),

    /*
    |--------------------------------------------------------------------------
    | Kitchen Owl Access Token
    |--------------------------------------------------------------------------
    |
    | Long-lived access token for Kitchen Owl API authentication.
    | Generate this from your Kitchen Owl user settings.
    |
    */
    'access_token' => env('KITCHEN_OWL_ACCESS_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Kitchen Owl Household ID
    |--------------------------------------------------------------------------
    |
    | The household ID to use for Kitchen Owl requests.
    | You can get this from the /api/household endpoint.
    |
    */
    'household_id' => env('KITCHEN_OWL_HOUSEHOLD_ID'),
];
