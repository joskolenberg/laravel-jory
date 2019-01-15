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
    | The case key can be used by users of your API to overwrite
    | the default casing when using the API ('snake' or 'camel').
    |
    */

    'request' => [

        'key' => 'jory',

        'case-key' => 'case',

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
            'is_null',
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

    /*
    |--------------------------------------------------------------------------
    | Case
    |--------------------------------------------------------------------------
    |
    | Here you can set the default casing to be used
    | for field, filter, sort and relation names.
    |
    | This casing will be used when calling the JoryBuilder/API.
    | NB. All configuration in JoryBuilder::config()
    | should always be in snake case.
    |
    | Possible values: 'snake', 'camel'.
    */

    'case' => 'snake',

];
