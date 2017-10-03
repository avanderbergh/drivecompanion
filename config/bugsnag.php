<?php

return [

    /*
    |--------------------------------------------------------------------------
    | API Key
    |--------------------------------------------------------------------------
    |
    | You can find your API key on your Bugsnag dashboard.
    |
    | This api key points the Bugsnag notifier to the project in your account
    | which should receive your application's uncaught exceptions.
    |
    */

    'api_key' => env('BUGSNAG_API_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Notify Release Stages
    |--------------------------------------------------------------------------
    |
    | Set which release stages should send notifications to Bugsnag.
    |
    | Example: array('development', 'production')
    |
    */

    'notify_release_stages' => empty(env('BUGSNAG_NOTIFY_RELEASE_STAGES')) ? null : explode(',', str_replace(' ', '', env('BUGSNAG_NOTIFY_RELEASE_STAGES'))),

    /*
    |--------------------------------------------------------------------------
    | Endpoint
    |--------------------------------------------------------------------------
    |
    | Set what server the Bugsnag notifier should send errors to. By default
    | this is set to 'https://notify.bugsnag.com', but for Bugsnag Enterprise
    | this should be the URL to your Bugsnag instance.
    |
    */

    'endpoint' => env('BUGSNAG_ENDPOINT', null),

    /*
    |--------------------------------------------------------------------------
    | Filters
    |--------------------------------------------------------------------------
    |
    | Use this if you want to ensure you don't send sensitive data such as
    | passwords, and credit card numbers to our servers. Any keys which
    | contain these strings will be filtered.
    |
    */

    'filters' => empty(env('BUGSNAG_FILTERS')) ? ['password'] : explode(',', str_replace(' ', '', env('BUGSNAG_FILTERS'))),

    /*
    |--------------------------------------------------------------------------
    | Callbacks
    |--------------------------------------------------------------------------
    |
    | Enable this if you'd like us to enable our default set of notification
    | callbacks. These add things like the cookie information and session
    | details to the error to be sent to Bugsnag.
    |
    | If you'd like to add your own callbacks, you can call the
    | Bugsnag::registerCallback method from the boot method of your app
    | service provider.
    |
    */

    'callbacks' => env('BUGSNAG_CALLBACKS', true),

    /*
    |--------------------------------------------------------------------------
    | User
    |--------------------------------------------------------------------------
    |
    | Enable this if you'd like us to set the current user logged in via
    | Laravel's authentication system.
    |
    | If you'd like to add your own user resolver, you can call the
    | Bugsnag::registerUserResolver method from the boot method of your app
    | service provider.
    |
    */

    'user' => env('BUGSNAG_USER', true),

    /*
    |--------------------------------------------------------------------------
    | Query
    |--------------------------------------------------------------------------
    |
    | Enable this if you'd like us to automatically record all queries executed
    | as breadcrumbs.
    |
    */

    'query' => env('BUGSNAG_QUERY', true),

    /*
    |--------------------------------------------------------------------------
    | Bindings
    |--------------------------------------------------------------------------
    |
    | Enable this if you'd like us to include the query bindings in our query
    | breadcrumbs.
    |
    */

    'bindings' => env('BUGSNAG_QUERY_BINDINGS', false),

    /*
    |--------------------------------------------------------------------------
    | Proxy
    |--------------------------------------------------------------------------
    |
    | This is where you can set the proxy settings you'd like us to use when
    | communicating with Bugsnag when reporting errors.
    |
    */

    'proxy' => array_filter([
        'http' => env('HTTP_PROXY'),
        'https' => env('HTTPS_PROXY'),
        'no' => empty(env('NO_PROXY')) ? null : explode(',', str_replace(' ', '', env('NO_PROXY'))),
    ]),

];
