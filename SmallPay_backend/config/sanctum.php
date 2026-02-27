<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Sanctum Guard
    |--------------------------------------------------------------------------
    |
    | This value defines the authentication guard that will be used when a
    | request is validated using Sanctum. The "sanctum" guard maps, by
    | default, to the token guard above.
    |
    */

    'guard' => ['sanctum'],

    /*
    |--------------------------------------------------------------------------
    | Expiration Minutes
    |--------------------------------------------------------------------------
    |
    | This value controls the number of minutes until an issued token will be
    | considered expired. If this value is null, personal access tokens do
    | not expire. This won't tweak the lifetime of first-party sessions.
    |
    */

    'expiration' => null,

    /*
    |--------------------------------------------------------------------------
    | Sanctum Middleware
    |--------------------------------------------------------------------------
    |
    | When authenticating your first-party SPA that is executing on the same
    | top-level domain as your API, Sanctum requires some middleware to
    | ensure that your requests are coming from your own application.
    |
    */

    'middleware' => [
        'verify_csrf_token' => App\Http\Middleware\VerifyCsrfToken::class,
        'encrypt_cookies' => App\Http\Middleware\EncryptCookies::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Stateful Domains
    |--------------------------------------------------------------------------
    |
    | Requests from the following domains / hosts will receive stateful API
    | authentication cookies that are persisted by Sanctum. Requests from
    | other domains will receive token authentication headers only.
    |
    */

    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
        '%s%s',
        'localhost,localhost:3000,localhost:3001,localhost:8000,localhost:8001,127.0.0.1,127.0.0.1:3000,127.0.0.1:3001,127.0.0.1:8000,127.0.0.1:8001',
        env('SANCTUM_STATEFUL_DOMAINS') ? ',' . env('SANCTUM_STATEFUL_DOMAINS') : ''
    ))),

    /*
    |--------------------------------------------------------------------------
    | Sanctum Routes Prefix / Subdomain
    |--------------------------------------------------------------------------
    |
    | This value controls the routing prefix for Sanctum's routes. You are
    | free to change this value to anything you like. Note that the routes
    | will only be registered if Sanctum is enabled as a guard.
    |
    */

    'prefix' => 'sanctum',

    /*
    |--------------------------------------------------------------------------
    | Sanctum Routes Middleware
    |--------------------------------------------------------------------------
    |
    | These middleware will be assigned to every Sanctum route, giving you
    | the chance to add your own middleware to this list or change any of
    | the existing middleware. Or, you may simply stick with this list.
    |
    */

    'middleware' => [
        'api' => [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ],

];
