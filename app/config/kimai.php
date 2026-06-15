<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Kimai URL
    |--------------------------------------------------------------------------
    |
    | The base URL of your Kimai instance.
    |
    */
    'url' => env('KIMAI_URL', 'http://kimai.local'),

    /*
    |--------------------------------------------------------------------------
    | Kimai Username
    |--------------------------------------------------------------------------
    |
    | The Kimai username (or email) used for API authentication.
    |
    */
    'username' => env('KIMAI_USERNAME'),

    /*
    |--------------------------------------------------------------------------
    | Kimai Access Token
    |--------------------------------------------------------------------------
    |
    | API token generated in Kimai user settings.
    |
    */
    'access_token' => env('KIMAI_ACCESS_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Kimai User ID
    |--------------------------------------------------------------------------
    |
    | Optional default user ID to query. You can still pass a user ID directly
    | when calling the service methods.
    |
    */
    'user_id' => env('KIMAI_USER_ID'),
];