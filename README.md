[![Build Status](https://travis-ci.org/joskolenberg/laravel-jory.svg?branch=master)](https://travis-ci.org/joskolenberg/laravel-jory)
[![StyleCI](https://github.styleci.io/repos/148323995/shield?branch=master)](https://github.styleci.io/repos/148323995)
[![Code Coverage](https://codecov.io/gh/joskolenberg/laravel-jory/branch/master/graph/badge.svg)](https://codecov.io/gh/joskolenberg/laravel-jory/branch/master/graph/badge.svg)

# Jory
Jory is a way of defining database queries using a JSON string, useful for loading dynamic data from the front-end. Jory can add high flexibility to your REST API and can easily be used alongside your existing code.

This package can be used for setting up Jory endpoints.

To learn about the conventions for setting up Jory queries, take a look at the [jory](https://packagist.org/packages/joskolenberg/jory) package.

## Installation
```
composer require joskolenberg/laravel-jory
```

## Usage
Apply the JoryTrait to a model to enable querying using Jory:
```php
use JosKolenberg\LaravelJory\Traits\JoryTrait;

class Band extends Model
{
    use JoryTrait;
}
```

You can now get the model's JoryBuilder like this:
```php
Band::jory();
```

Next you can apply a Jory array:
```php
$result = Band::jory()
    ->applyArray([
        'filter' => [ // Filter bands having a name containing 'le'
            'field' => 'name',
            'operator' => 'like',
            'data' => '%le%'
        ],
        'fields' => [ // Only return the band's name en starting year
            'year_start',
            'name',
        ],
        'sorts' => [ // Order the band by name descending
            '-name',
        ],
        'limit' => 2, // Return only the first two bands
        'relations' => [
            'albums' => [ // Load the albums relation
                'sorts' => [ // Order the albums by release date
                    'release_date'
                ],
                'fields' => [ // Only return the album's name and release date
                    'name',
                    'release_date',
                ],
            ]
        ]
    ])
    ->toArray();
```
Or a Jory Json string (minified):
```php
$result = Band::jory()
    ->applyJson('{"flt":{"f":"name","o":"like","d":"%le%"},"fld":["year_start","name"],"srt":["-name"],"lmt":2,"rlt":{"albums":{"srt":["release_date"],"fld":["name","release_date"]}}}')
    ->toArray();
```

Both examples will produce the same result which could be:
```json
[
  {
    "name": "Led Zeppelin",
    "year_start": 1968,
    "albums": [
      {
        "name": "Led Zeppelin",
        "release_date": "1969-01-12"
      },
      {
        "name": "Led Zeppelin II",
        "release_date": "1969-10-22"
      },
      {
        "name": "Led Zeppelin III",
        "release_date": "1970-10-05"
      }
    ]
  },
  {
    "name": "Beatles",
    "year_start": 1960,
    "albums": [
      {
        "name": "Sgt. Peppers lonely hearts club band",
        "release_date": "1967-06-01"
      },
      {
        "name": "Abbey road",
        "release_date": "1969-09-26"
      },
      {
        "name": "Let it be",
        "release_date": "1970-05-08"
      }
    ]
  }
]
```

Notes on the previous example:
- The Band models should have an "albums" eloquent relation.
- Both the Band and Album model should be applying the JoryTrait.
- Take a look at the [jory](https://packagist.org/packages/joskolenberg/jory) package to learn about how to write Jory arrays or strings.

## Registering & Routes
Registering JoryBuilders is not required but allows you to register standard routes for each model and apply custom JoryBuilders (more on that later).

JoryBuilders can best be registered in the boot method of a service provider (use the AppServiceProvider or create a dedicated one),
```php
use App\Band;
use Illuminate\Support\ServiceProvider;
use JosKolenberg\LaravelJory\JoryBuilder;

class JoryServiceProvider extends ServiceProvider
{
    public function boot()
    {
        JoryBuilder::register(Band::class);
        JoryBuilder::register(Album::class);
    }
}
```

Each registered model will be available as a Jory API resource when the following routing method is applied in one of your routes files:
```php
use JosKolenberg\LaravelJory\JoryBuilder;

JoryBuilder::routes('my-jory-api');
```

### Available routes
The JoryBuilder::routes() method will register the following routes:
- ```OPTIONS /``` List all available resources.
- ```OPTIONS /{resource}``` Get the options for this resource.
- ```GET /{resource}``` Get a list of items for this resource based on the Jory string in the 'jory' parameter.
- ```GET /{resource}/{id}``` Get a single record and apply the 'jory' parameter. 
- ```GET /{resource}/count``` Get the record count for the resource based on the 'jory' parameter.
- ```GET /``` Retrieve multiple unrelated resources in one call.

For example; the previous result could now be fetched by calling:
```
/my-jory-api/band?jory={"flt":{"f":"name","o":"like","d":"%le%"},"fld":["year_start","name"],"srt":["-name"],"lmt":2,"rlt":{"albums":{"srt":["release_date"],"fld":["name","release_date"]}}}
```

More to come...