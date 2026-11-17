<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    // Fortify's root-level auth routes are included because the first-party SPA logs in
    // with a session cookie: GET /sanctum/csrf-cookie, then POST /login (see Domains/Auth).
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout', 'two-factor-challenge'],

    'allowed_methods' => ['*'],

    // Must be an explicit list, never '*': with supports_credentials enabled, a wildcard makes
    // the CORS layer echo back any Origin, letting any site read cookie-authenticated responses.
    'allowed_origins' => array_filter(explode(',', (string) env('CORS_ALLOWED_ORIGINS', 'http://localhost:5173'))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // Lets the browser send the session and XSRF-TOKEN cookies cross-origin (SPA on
    // app.example.com calling api.example.com). Bearer-token clients are unaffected.
    'supports_credentials' => true,

];
