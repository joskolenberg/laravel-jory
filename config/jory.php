<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Request key
    |--------------------------------------------------------------------------
    |
    | This keys will be looked for when making requests.
    |
    | 'key' must hold the JSON string with the Jory Query.
    |
    | 'case' can be used by consumers of your API to override the
    | default casing when calling the API ('snake' or 'camel').
    |
    | 'meta' is the key where will be checked for an array
    | to determine which metadata should be returned.
    |
    */

    'request' => [

        'key' => 'jory',

        'case-key' => 'case',

        'meta-key' => 'meta',

    ],

    /*
    |--------------------------------------------------------------------------
    | Response keys
    |--------------------------------------------------------------------------
    |
    | Here you can set the keys on which the data will be returned.
    |
    | Set 'data-key' to null to return data in the root, but note that it
    | is not possible use metadata when you set the data-key to null.
    |
    */

    'response' => [

        'data-key' => 'data',

        'errors-key' => 'errors',

        'meta-key' => 'meta',

    ],

    /*
    |--------------------------------------------------------------------------
    | Filter operators
    |--------------------------------------------------------------------------
    |
    | These are the default filter operators which can be
    | applied, these settings can be overridden for each
    | individual field in the Jory Resource classes.
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
    | Casing can be used to cast all your resources to a specific case.
    | Laravel's relations are typically camelCased while attributes
    | are snake_cased. If you want to deliver a more consistent
    | API to your clients you can set the casing here to
    | convert all the keys to that specific case. The
    | 'default' option does not apply any casing.
    |
    | Possible values: 'default', 'snake', 'camel'.
    */

    'case' => 'default',

    /*
    |--------------------------------------------------------------------------
    | Routes
    |--------------------------------------------------------------------------
    |
    | By default Laravel Jory will create an API at the /api endpoint
    | for you. If you want to change your API address or disable
    | the default API completely you can configure this below.
    |
    | Some useful middleware are added to get you up and running
    | quickly but don't forget to enable the 'auth' middleware
    | if you don't want your data to be publicly available!
    |
    */

    'routes' => [

        'enabled' => true,

        'path' => '/jory',

        'middleware' => [
            'api',
            // 'auth',
            \JosKolenberg\LaravelJory\Http\Middleware\SetJoryHandler::class
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Metadata
    |--------------------------------------------------------------------------
    |
    | Here you can define which metadata is available to be
    | requested. Note that returning metadata is disabled
    | when the response.data-key value is set to null.
    |
    */

    'metadata' => [

        'user' => \JosKolenberg\LaravelJory\Meta\User::class,
        'total' => \JosKolenberg\LaravelJory\Meta\Total::class,
        'query_count' => \JosKolenberg\LaravelJory\Meta\QueryCount::class,
        'time' => \JosKolenberg\LaravelJory\Meta\Time::class,

    ],

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
    | Generator
    |--------------------------------------------------------------------------
    |
    | The Generator command is a great help to quickly set up
    | your Jory Resources, you can modify the settings below
    | to fit your needs and make it even more powerful.
    |
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
        | your Jory Resources somewhere else.
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
        | Tell the generator command where your models are stored so he
        | can find them en generate a Jory Resource for all of them.
        |
        */

        'models' => [
            'namespace' => 'App',
            'path' => app_path(),
            'exclude' => [
                // \App\BaseModel::class;
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Fields
        |--------------------------------------------------------------------------
        |
        | Here you can tell the generator which fields should NOT be
        | automatically be configured, this is useful for hiding
        | sensitive data like password and token fields.
        |
        */

        'fields' => [
            'exclude' => [
                'password',
                'remember_token'
            ],
        ],

    ],

];
