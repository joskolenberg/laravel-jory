[![Build Status](https://travis-ci.org/joskolenberg/laravel-jory.svg?branch=master)](https://travis-ci.org/joskolenberg/laravel-jory)
[![StyleCI](https://github.styleci.io/repos/148323995/shield?branch=master)](https://github.styleci.io/repos/148323995)
[![Code Coverage](https://codecov.io/gh/joskolenberg/laravel-jory/branch/master/graph/badge.svg)](https://codecov.io/gh/joskolenberg/laravel-jory/branch/master/graph/badge.svg)

# Laravel-Jory
Jory is a way of defining database queries using a JSON string, useful for loading dynamic data from the front-end. Jory can add high flexibility to your REST API and can easily be used alongside your existing code.

This package can be used for setting up Jory endpoints in Laravel, to learn about the conventions for setting up Jory queries, take a look at the [jory](https://packagist.org/packages/joskolenberg/jory) package.

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
- The Band model should have an "albums" eloquent relation.
- Both the Band and Album model should be applying the JoryTrait.
- Take a look at the [jory](https://packagist.org/packages/joskolenberg/jory) package to learn about how to write Jory arrays/Json-strings.

## Registering & Routes
Registering JoryBuilders is not required but allows you to register standard routes for each model and apply [custom JoryBuilders](#custom-jorybuilders).

JoryBuilders can best be registered in the boot method of a service provider (use the AppServiceProvider or create a dedicated one):
```php
use Illuminate\Support\ServiceProvider;
use JosKolenberg\LaravelJory\JoryBuilder;

class JoryServiceProvider extends ServiceProvider
{
    public function boot()
    {
        JoryBuilder::register(\App\Band::class);
        JoryBuilder::register(\App\Album::class);
        JoryBuilder::register(\App\AlbumCover::class);
        JoryBuilder::register(\App\Song::class);
    }
}
```

Each registered model will be available as a Jory API resource when the following routing method is applied in one of your routes files:
```php
use JosKolenberg\LaravelJory\JoryBuilder;

JoryBuilder::routes('my-jory-api');
```

### Available routes
The JoryBuilder::routes() method will register the following routes (using kebab-cased model names for ```{resource}```):
- ```OPTIONS``` ```/``` [List](#available-resources) all available resources.
- ```OPTIONS``` ```/{resource}``` Show the [options](#resource-options) for this resource.
- ```GET``` ```/{resource}``` Get a [list of items](#resource-list) for this resource based on the Jory string in the 'jory' parameter.
- ```GET``` ```/{resource}/{id}``` Get a [single record](#single-resource) and apply the 'jory' parameter. 
- ```GET``` ```/{resource}/count``` Get the [record count](#resource-count) for the resource based on the 'jory' parameter.
- ```GET``` ```/``` Get [multiple](#multiple-resources) unrelated resources in one call.

#### Available resources
An ```OPTIONS``` call to ```/``` lists all available resources without further detail.

Output example:
```json
{
    "resources": [
        "band",
        "album",
        "album-cover",
        "song"
    ]
}
```

#### Resource options
An ```OPTIONS``` call to ```/{resource}``` shows the options for the given resource. This will become more useful when [custom JoryBuilders](#custom-jorybuilders) are defined.

Example call:
```
OPTIONS /my-jory-api/band
```
Result:
```json
{
    "fields": "Not defined.",
    "filters": "Not defined.",
    "sorts": "Not defined.",
    "limit": {
        "default": 100,
        "max": 1000
    },
    "relations": "Not defined."
}
```

#### Resource list
A ```GET``` call to ```/{resource}``` returns an array of items based on the ```jory``` parameter.

Example call:
```
GET /my-jory-api/band?jory={"flt":{"f":"name","o":"like","d":"%le%"},"fld":["year_start","name"],"srt":["-name"],"lmt":2,"rlt":{"albums":{"srt":["release_date"],"fld":["name","release_date"]}}}
```
Result:
```json
{
    "data": [
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
}
```

#### Single resource
A ```GET``` call to ```/{resource}/{id}``` returns a single item based on the ```id``` with the ```jory``` parameter applied.

Example call fetching a band's name including related album names and release dates:
```
GET /my-jory-api/band/1?jory={"fld":["name"],"rlt":{"albums":{"fld":["name","release_date"]}}}
```
Result:
```json
{
    "data": {
        "name": "Rolling Stones",
        "albums": [
            {
                "name": "Let it bleed",
                "release_date": "1969-12-05"
            },
            {
                "name": "Sticky Fingers",
                "release_date": "1971-04-23"
            },
            {
                "name": "Exile on main st.",
                "release_date": "1972-05-12"
            }
        ]
    }
}
```

#### Resource count
A ```GET``` call to ```/{resource}/count``` returns the number of records based on the filtering within the ```jory``` parameter.

Example call counting the bands with a name containing 'le':
```
GET /my-jory-api/band/count?jory={"flt":{"f":"name","o":"like","d":"%le%"}}
```
Result:
```json
{
    "data": 2
}
```

#### Multiple resources
A ```GET``` call to ```/``` allows you to fetch multiple resources in a single call.
Each supplied parameter key will be translated to a resource-call using the following convention:
- ```'{resource}'``` get a list for this resource.
- ```'{resource}_{id}``` get a resource by id.
- ```'{resource}_count``` get a resource count.
- All keys can be post-fixed with ```_as_{alias}``` to return the result on a different key, which allows to fetch a single resource multiple times.

For example, the following keys would all be valid: ```band```, ```band_2```, ```band_count```, ```band_2_as_beatles```, ```band_count_as_number_of_bands```

Example call fetching:
- All bands having a name containing 'le'. ```band```: ```{"flt":{"f":"name","o":"like","d":"%le%"}}```
- All bands without a year_end returned as active_bands. ```band_as_active_bands```: ```{"flt":{"f":"year_end","o":"is_null"}}```
- The title of the song with id 101 including the related album's name. ```song_101```: ```{"fld":["title"],"rlt":{"album":{"fld":["name"]}}}```
- The number of albums with a name containing 'love' returned as love_album_count. ```album_count_as_love_album_count```: ```{"flt":{"f":"name","o":"like","d":"%love%"}}```
```
GET /my-jory-api?band={"flt":{"f":"name","o":"like","d":"%le%"}}&band_as_active_bands={"flt":{"f":"year_end","o":"is_null"}}&song_101={"fld":["title"],"rlt":{"album":{"fld":["name"]}}}&album_count_as_love_album_count={"flt":{"f":"name","o":"like","d":"%love%"}}
```
Result:
```json
{
    "data": {
        "band": [
            {
                "id": 2,
                "name": "Led Zeppelin",
                "year_start": 1968,
                "year_end": 1980
            },
            {
                "id": 3,
                "name": "Beatles",
                "year_start": 1960,
                "year_end": 1970
            }
        ],
        "active_bands": [
            {
                "id": 1,
                "name": "Rolling Stones",
                "year_start": 1962,
                "year_end": null
            }
        ],
        "song_99": {
            "title": "I Me Mine\" (Harrison",
            "album": {
                "name": "Let it be"
            }
        },
        "love_album_count": 1
    }
}
```

## Custom JoryBuilders
When registering a model, by default the base JoryBuilder will be applied, but creating custom JoryBuilders allow you to:
- Validate incoming requests and returning useful error messages.
- Give the users good documentation when calling the ```OPTIONS``` for a resource.
- Define custom [filter](#defining-custom-filters) and [sort](#defining-custom-sorts) options.
- [Hook](#hooks) into queries and collections.

You can bind a custom JoryBuilder to a model by creating a JoryBuilder class which extends the base JoryBuilder and pass it as the second argument to the register method:
```php
namespace App\Http\JoryBuilders;

use JosKolenberg\LaravelJory\JoryBuilder;

class BandJoryBuilder extends JoryBuilder
{

}
```

```php
JoryBuilder::register(\App\Band::class, \App\Http\JoryBuilders\BandJoryBuilder::class);
```

### Configuring a custom JoryBuilder
To configure your JoryBuilder override the ```config``` method, this method receives a ```Config``` object on which all configuration should be applied.

```php
class BandJoryBuilder extends JoryBuilder
{
    protected function config(Config $config): void
    {
        // Apply config
    }
}
```

#### Registering filters
A filter option can be registered using the ```filter()``` method. Optionally a description and available operators can be provided:
```php
$config->filter('name')
    ->description('Filter by the band\'s name.')
    ->operators(['like', '=']);
```

Now when someone calls the ```OPTIONS``` on ```/my-jory-api/band``` this filter is documented:
```json
    ...
    "filters": {
        "name": {
            "description": "Filter by the band's name.",
            "operators": [
                "like",
                "="
            ]
        }
    },
    ...
```

And the api will return useful messages when a typo is made:

Example call:
```
/my-jory-api/band?jory={"flt":{"f":"naame","o":"like","d":"%le%"}}
```

Result:
```json
{
    "errors": [
        "Field \"naame\" is not available for filtering. Did you mean \"name\"? (Location: filter(naame))"
    ]
}
```

Because filters will be validated once one filter has been registered; when you register one you should register all.

#### Registering sorts
A sort option can be registered using the ```sort()``` method. Optionally a description can be provided.

Apply the ```default()``` method on the sort to apply sorting on this field by default.
```php
$config->sort('id');

$config->sort('name')
    ->description('Sort by the band\'s name.')
    ->default();
```

Just like registering filters all registered sorts will be used for validating and are shown in the ```OPTIONS```.


#### Registering fields
A field can be registered using the ```field()``` method. Optionally a description can be provided.

Apply the ```hideByDefault()``` method to NOT return this field when no explicit fields are requested.

Because all fields will mostly be used for filtering and sorting as well, convenient ```filterable()``` and ```sortable()``` methods are provided to register this in one go. You can use an optional callback to fill out any filter and sort details. 
```php
$config->field('id')->description('The band\'s id.')->sortable()->hideByDefault();

$config->field('name')
    ->description('The band\'s name.')
    ->filterable(function (Filter $filter) {
        $filter->description('Filter by the band\'s name.')
            ->operators(['like', '=']);
    })->sortable(function (Sort $sort) {
        $sort->description('Sort by the band\'s name.')
            ->default();
    });
```

Registered fields will be used for validating and are shown in the ```OPTIONS```.

#### Registering relations
A relation can be registered using the ```relation()``` method. Optionally a description can be provided.

```php
$config->relation('albums')->description('The band\'s albums');
```

Registered relations will be used for validating and are shown in the ```OPTIONS```.

#### Setting pagination defaults
Use the ```limitDefault()``` method to set the default limit when no limit is given.
Use the ```limitMax()``` to set the maximum number of records that can be requested at once for this resource.

```php
$config->limitDefault(25)->limitMax(100);
```

These options will be used for validating and are shown in the ```OPTIONS```.

### Defining custom filters
Often you want to be able to filter on more than just a field. You can add custom filters using the following convention ```scope{CustomName}Filter()```.

For each filter the JoryBuilder will look for that method in the JoryBuilder.
```php
protected function scopeHasSongWithTitleFilter($query, $operator, $data)
{
    $query->whereHas('songs', function ($query) use ($operator, $data) {
        $query->where('title', $operator, $data);
    });
}
```
And don't forget to register the field in the configuration:
```php
$config->filter('has_song_with_title')
    ->description('Filter only bands which have a given title.')
    ->operators(['like', '=']);
```

Alternatively you can make use of Laravel's built in scopes on a model. When the custom filter function is available on the model the JoryBuilder will find it as well.

### Defining custom sorts
Applying custom sorts is the same as custom filters except for the naming convention being ```scope{CustomName}Sort()```.

### Defining custom fields
All the returned fields are collected using the Eloquent model's ```toArray()``` method. To make custom fields available for your Jory api add them as custom attributes and append them in your model. When using a custom JoryBuilder make sure to add this field in the config.

### Hooks
Sometimes you may want to hook into the process to do some additional tweaking.

The JoryBuilder has these methods which can be overridden to do so:
- ```beforeQueryBuild()``` Modify the query before all settings from the Jory input are applied.
- ```afterQueryBuild()``` Modify the query after all settings from the Jory input are applied but before it is executed.
- ```afterFetch()``` Modify the models right after they are fetched from the database.

The ```beforeQueryBuild()``` and ```afterQueryBuild()``` hooks can be useful to add some global scoping or add some additional fields when they are requested.
```php
protected function beforeQueryBuild($query, Jory $jory, $count = false)
{
    parent::beforeQueryBuild($query, $jory, $count);
    
    // Archived items are not available for the API
    $query->where('is_archived', false);

    // Only when the song_count field is requested, execute Laravel's withCount() method.
    if ($jory->hasField('song_count')) {
        $query->withCount('songs as song_count');
    }
}
```
The ```afterFetch()``` hook is useful to modify the models before the data is retrieved from them. For example, if an Invoice model has a calculated 'total_price' custom attribute which loops through all attached InvoiceLines you might want to eager load the InvoiceLines on the Invoices to save on querying. (This method always receives a collection even if only one item is requested.)
```php
protected function afterFetch(Collection $collection, Jory $jory): Collection
{
    $collection = parent::afterFetch($collection, $jory);

    if ($jory->hasField('total_price')) {
        $collection->load('invoiceLines');
    }
    
    return $collection;
}
``` 

## Config
To override Jory's default settings publish the config file using:
```
php artisan vendor:publish --provider="JosKolenberg\LaravelJory\JoryServiceProvider"
```



That's it! Any suggestions or issues? Please contact me!

Happy coding!

Jos Kolenberg <joskolenberg@gmail.com>