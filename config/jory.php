<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Request key
    |--------------------------------------------------------------------------
    |
    | This key will be looked for to get the JSON string
    | holding the jory data in the request.
    |
    */

    'request' => [

        'key' => 'jory',

    ],

    /*
    |--------------------------------------------------------------------------
    | Response keys
    |--------------------------------------------------------------------------
    |
    | Here you can set the keys on
    | which the data will be returned.
    |
    | Set to null to return data in root.
    |
    */

    'response' => [

        'data-key' => 'data',

        'errors-key' => 'errors',

    ],

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
