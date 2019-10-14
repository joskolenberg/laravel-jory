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
    | The case key can be used by users of your API to override the
    | default casing when using the API ('snake' or 'camel').
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
            'not_like',
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
    | These defaults can be overridden for
    | specific resources when needed.
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
    | This casing will be used when calling the JoryResource or API.
    |
    | NB. All configuration in the JoryResource
    | classes should always be in snake case.
    |
    | Possible values: 'snake', 'camel'.
    */

    'case' => 'snake',

    /*
    |--------------------------------------------------------------------------
    | Registrars
    |--------------------------------------------------------------------------
    |
    | Here you can register any Registrar classes which bind the JoryResources
    | to your Models. By default an auto-registrar is added which binds all
    | JoryResources in the default \App\Http\JoryResources namespace.
    |
    | All registrars should implement the RegistersJoryResources interface.
    */

    'registrars' => [
        \JosKolenberg\LaravelJory\Register\AutoRegistrar::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | AutoRegistrar settings
    |--------------------------------------------------------------------------
    |
    | Here you can edit the settings for the basic autoRegistrar.
    | By default the autoRegistrar looks for JoryResources
    | in the App\Http\JoryResources namespace.
    */

    'auto-registrar' => [

        'namespace' => 'App\Http\JoryResources',
        'path' => app_path('Http' . DIRECTORY_SEPARATOR . 'JoryResources'),

    ],

    /*
    |--------------------------------------------------------------------------
    | Routes
    |--------------------------------------------------------------------------
    |
    | Here you can define if the default jory api routes should be enabled,
    | the uri for these routes and which middleware should be applied.
    |
    */

    'routes' => [

        'enabled' => true,

        'path' => '/jory',

        'middleware' => [
            'api',
            \JosKolenberg\LaravelJory\Http\Middleware\SetJoryHandler::class
        ],

    ],


    /*
    |--------------------------------------------------------------------------
    | Generator
    |--------------------------------------------------------------------------
    |
    | Define some settings for the artisan commands.
    | - jory:generate-for
    | - jory:generate-all
    | - make:jory-resource

    */

    'generator' => [

        /*
        |--------------------------------------------------------------------------
        | Jory Resources location
        |--------------------------------------------------------------------------
        |
        | Tell the generator commands where you want your jory-resources
        | to be stored. You probably want to match this setting with
        | the path and namespace for the auto-registrar, don't
        | forget to manually register them if you store
        | your jory-resources somewhere else.
        |
        */

        'jory-resources' => [
            'namespace' => 'App\Http\JoryResources',
            'path' => app_path('Http' . DIRECTORY_SEPARATOR . 'JoryResources'),
        ],

        /*
        |--------------------------------------------------------------------------
        | Models location
        |--------------------------------------------------------------------------
        |
        | Tell the generate:all command where your models are stored so he
        | can find them en generate a jory-resource for all of them.
        |
        */

        'models' => [
            'namespace' => 'App',
            'path' => app_path(),
            'exclude' => [
                // \App\BaseModel::class;
            ],
        ],

    ],

];
