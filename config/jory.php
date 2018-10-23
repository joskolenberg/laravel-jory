<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Routes
    |--------------------------------------------------------------------------
    |
    | This array of routes will be registered when the jory routes
    | are applied (using JoryBuilder::routes()). This will include
    | an index and show route for each resource.
    |
    | e.g: 'user' => \App\User::class will generate
    | an index route on '\user' (GET) and a show
    | route on '\user\{user}' (GET).
    |
    */

    'routes' => [
        //'user' => \App\User::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Request Key
    |--------------------------------------------------------------------------
    |
    | This key will be looked for to get the JSON string
    | holding the jory data in the request.
    |
    */

    'request-key' => 'jory',
];
