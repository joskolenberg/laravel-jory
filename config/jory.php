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

        'default' => null,

        'max' => null,

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

    /*
    |--------------------------------------------------------------------------
    | Registrars
    |--------------------------------------------------------------------------
    |
    | Here you can register any Registrar classes which bind the JoryBuilders
    | to your Models. By default an auto-registrar is added which binds the
    | JoryBuilders in the default \App\Http\JoryBuilders namespace.
    */

    'registrars' => [
        \JosKolenberg\LaravelJory\Register\AutoRegistrar::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | AutoRegistrar settings
    |--------------------------------------------------------------------------
    |
    | Here you can edit the settings for the default autoRegistrar.
    | By default the autoRegistrar looks for models in the 'app'
    | directory and tries to bind them to JoryBuilders in the
    | 'app/Http/JoryBuilders' directory. The JoryBuilders
    | should be using the <Model>JoryBuilder convention.
    */

    'auto-registrar' => [

        'models-path' => app_path(),
        'jory-builders-path' => app_path('Http\\JoryBuilders'),
        'root-namespace' => '\App',
        'root-path' => app_path(),

    ],

    /*
    |--------------------------------------------------------------------------
    | Routes
    |--------------------------------------------------------------------------
    |
    | Here you can define if the default routes should be enabled, the
    | uri for these routes and which middleware should be applied.
    |
    */

    'routes' => [

        'enabled' => true,

        'path' => '/jory',

        'middleware' => [
            'api'
        ],

    ],

];
