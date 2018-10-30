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

    'routes' => [//'user' => \App\User::class,
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

    /*
    |--------------------------------------------------------------------------
    | Filter operators
    |--------------------------------------------------------------------------
    |
    | Here you can define which operators are
    | available by default for any filter.
    |
    */

    'filters' => [

        'operators' => [
            '=',
            '!=',
            '<>',
            '>',
            '>=',
            '<',
            '<=',
            '<=>',
            'like',
            'null',
            'not_null',
            'in',
            'not_in',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Limit default & max
    |--------------------------------------------------------------------------
    |
    | Here you can set how much records should be returned by default.
    | The max parameter is the maximum value a client can set
    | for the limit parameter in the request.
    |
    */

    'limit' => [

        'default' => 100,

        'max' => 1000,

    ],

];
