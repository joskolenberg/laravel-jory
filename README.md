[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/joskolenberg/laravel-jory/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/joskolenberg/laravel-jory/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/joskolenberg/laravel-jory/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/joskolenberg/laravel-jory/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/joskolenberg/laravel-jory/badges/build.png?b=master)](https://scrutinizer-ci.com/g/joskolenberg/laravel-jory/build-status/master)
[![Total Downloads](https://poser.pugx.org/joskolenberg/laravel-jory/downloads)](https://packagist.org/packages/joskolenberg/laravel-jory)
[![Latest Stable Version](https://poser.pugx.org/joskolenberg/laravel-jory/v/stable)](https://packagist.org/packages/joskolenberg/laravel-jory)
[![License](https://poser.pugx.org/joskolenberg/laravel-jory/license)](https://packagist.org/packages/joskolenberg/laravel-jory)

# Laravel-Jory; Flexible Laravel Resources
This package creates a highly flexible API for your Laravel application.
JoryResources are comparable to Laravel's Resources, but can be queried in a flexible way by passing a [Jory query](https://packagist.org/packages/joskolenberg/jory) when calling the API.

## Installation
```
composer require joskolenberg/laravel-jory
```

## Usage
Every model you want to query using [Jory queries](https://packagist.org/packages/joskolenberg/jory) needs it's own JoryResource, in these JoryResource classes is defined what you want te expose in your api. These are:
- Fields: Which fields can be requested.
- Filters: Which filters can be applied.
- Sorts: By which fields can be sorted.
- Offset/Limit: Define a default and maximum number of records to be returned.
- Relations: Which relations can be requested (these relations will in turn use their own JoryResource for configuration).

Take a look [here](#configuring) to see how to configure a JoryResource.

### Creating a JoryResource
For a quick start you can call the ```jory:generate-all``` command to create a JoryResource for each of your models.
```
php artisan jory:generate-all
```
If you'd like to be more explicit you can use the ```jory:generate-for``` command which requires a model class as an attribute instead.
```
php artisan jory:generate-for 'App\User'
```
All these JoryResources will be pre-configured by using reflection on your model classes.

## Routes
The package will register the following routes for all JoryResources:
- ```GET``` ```/jory/{resource}``` Get an [array of items](#resource-list) for this resource based on the [Jory query](https://packagist.org/packages/joskolenberg/jory) in the 'jory' parameter.
- ```GET``` ```/jory/{resource}/{id}``` Get a [single record](#single-resource) and apply the 'jory' parameter. 
- ```GET``` ```/jory/{resource}/count``` Get the [record count](#resource-count) for the resource based on the 'jory' parameter.
- ```GET``` ```/jory``` Get [multiple](#multiple-resources) unrelated resources in one call.


#### Resource list
A ```GET``` call to ```/jory/{resource}``` returns an array of items based on the ```jory``` parameter holding a [Jory query](https://packagist.org/packages/joskolenberg/jory).

Example call using Axios (but you can use whatever tool you like):
```javascript
axios.get('jory/band', {
    params: {
        jory: {
            fields: ["year_start", "name"],
            filter: {
                field: "name",
                operator: "like",
                data: "%le%"
            },
            sorts: ["-name"],
            limit: 2,
            relations: {
                albums: {
                    fields: ["name", "release_date"],
                    sorts: ["release_date"]
                }
            }
        },
    },
});
```
Possible result:
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
A ```GET``` call to ```/jory/{resource}/{id}``` returns a single item based on the ```id``` with the ```jory``` parameter applied.

Example call fetching a band's name including related album names and release dates:
```javascript
axios.get('jory/band/1', {
    params: {
        jory: {
            fields: ["name"],
            relations: {
                albums: {
                    fields: ["name", "release_date"]
                }
            }
        },
    },
});
```
Possible result:
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
A ```GET``` call to ```/jory/{resource}/count``` returns the number of records based on the filtering within the ```jory``` parameter.

Example call counting the bands with a name containing 'le':
```javascript
axios.get('jory/band/count', {
    params: {
        jory: {
            filter: {
                field: "name",
                operator: "like",
                data: "%le%"
            }
        },
    },
});
```
Possible result:
```json
{
    "data": 2
}
```

#### Multiple resources
A ```GET``` call to ```/jory``` allows you to fetch multiple resources in a single call.
This endpoint accepts a json object with key value pairs, the key being the resource name and the value being the [Jory query](https://packagist.org/packages/joskolenberg/jory).
Possible key values:
- ```'{resource}'``` get a list for this resource.
- ```'{resource}:{id}``` get a resource by id.
- ```'{resource}:count``` get a resource count.
- All keys can be post-fixed with ``` as {alias}``` to return the result on a different key, which allows to fetch a single resource multiple times.

For example, the following keys would all be valid: ```band```, ```band:2```, ```band:count```, ```band:2 as beatles```, ```band:count as number_of_bands```

Example call fetching:
- All bands having a name containing 'le'. ```band```: ```{"filter":{"field":"name","operator":"like","data":"%le%"}}```
- All bands without a year_end returned as active_bands. ```band as active_bands```: ```{"filter":{"field":"year_end","operator":"is_null"}}```
```javascript
axios.get('jory/band', {
    params: {
        jory: {
            band: {
                filter: {
                    field: "name",
                    operator: "like",
                    data: "%le%"
                },
            },
            "band as active_bands": {
                filter: {
                    field: "year_end",
                    operator: "is_null"
                },
            }
        },
    },
});
```
Possible result:
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
    }
}
```

## JoryResources
Use the JoryResource classes to configure your Jory api.

### Registering
JoryResources are automatically discovered as long as they are in the default App\Http\JoryResources namespace.
Alter the jory [config](#config) file or use the Register() method on the Jory facade to change this behaviour.

### Linked model
The ```modelClass``` attribute needs a reference to the related model. Normally there would be only one JoryResource for a model (however multiple is possible).
```php
protected $modelClass = AlbumCover::class;
```

### Uri
By default your model can be called in the jory api using the kebabcased model name (e.g. ```/jory/album-cover```), you can set your own using the $uri attribute.
```php
protected $uri = 'albumcover';
```

### Configuring
To configure your JoryResource use the ```configure``` method.
- All fields, filters, sorts and relations you want to expose need to be configured here explicitly.
- All multiword configuration should be done using snake_case.

```php
class BandJoryResource extends JoryResource
{
    protected function configure(): void
    {
        // Apply config
    }
}
```

Note: It's advised to use the generator commands to create and pre-configure your JoryResources.


#### Registering filters
A filter option can be registered using the ```filter()``` method. By default all operators are available, use the ```operators``` method if you only want to offer a limited set of operators:
```php
$this->filter('name')->operators(['like', '=']);
```
The available operators are: '=', '!=', '<>', '>', '>=', '<', '<=', '<=>', 'like', 'not_like', 'is_null', 'not_null', 'in' and 'not_in'.

#### Registering sorts
A sort option can be registered using the ```sort()``` method.

Apply the ```default()``` method on the sort to apply sorting on this field by default.
```php
$this->sort('id');

$this->sort('name')->default();
```

#### Registering fields
A field can be registered using the ```field()``` method.

Apply the ```hideByDefault()``` method to NOT return this field when no explicit fields are requested in the [Jory query](https://packagist.org/packages/joskolenberg/jory).

Because all fields will mostly be used for filtering and sorting as well, convenient ```filterable()``` and ```sortable()``` methods are provided to register this in one go. You can use an optional callback to fill out any filter and sort details. 
```php
$this->field('id')->hideByDefault()->filterable()->sortable();

$this->field('name')
    ->filterable(function (Filter $filter) {
        $filter->operators(['like', '=']);
    })->sortable(function (Sort $sort) {
        $sort->default();
    });
```

#### Registering relations
A relation can be registered using the ```relation()``` method.
When relations are fetched, the JoryResource for the related model will be used to handle the relation request.

You can pass an JoryResource class as a second parameter if you don't want to use the default JoryResource for this relation.

```php
$this->relation('albums');

$this->relation('songs', AlternateSongJoryResource::class);
```
Note: mergeTo relations cannot be used by Jory due their dynamic nature. An easy workaround can be made by creating a separate belongsTo relation for each "morphable" type.  

#### Setting pagination defaults
Use the ```limitDefault()``` method to set the default limit when no limit is given.
Use the ```limitMax()``` to set the maximum number of records that can be requested at once for this resource.

```php
$this->limitDefault(25)->limitMax(100);
```

#### Defining custom filters
Often you want to be able to filter on more than just a field. You can add custom filter methods to the JoryResource using the following convention ```scope{CustomName}Filter()```:
```php
public function scopeHasSongWithTitleFilter($query, $operator, $data)
{
    $query->whereHas('songs', function ($query) use ($operator, $data) {
        $query->where('title', $operator, $data);
    });
}
```
And don't forget to register the field in the configuration:
```php
$this->filter('has_song_with_title');
```

Alternatively you can make use of Laravel's built in model scopes. When the custom filter function is available on the model the JoryResource will find it as well.

#### Defining custom sorts
Applying custom sorts is the same as custom filters except for the naming convention being ```scope{CustomName}Sort()```. This method receives a query (```Builder``` object) and order (string 'asc' or 'desc') parameter.

#### Defining custom fields
To make a model's custom attribute available in the JoryResource just add the field in the JoryResource's ```configure``` method.

#### Hooks
Sometimes you may want to hook into the process to do some additional tweaking.

The JoryResource has these methods which can be overridden to do so:
- ```beforeQueryBuild()``` Modify the query before all settings from the Jory request input are applied.
- ```afterQueryBuild()``` Modify the query after all settings from the Jory request input are applied but before it is executed.
- ```afterFetch()``` Modify the models right after they are fetched from the database but before they are turned into the result array.

The ```hasField()```, ```hasFilter()``` and ```hasSort()``` helper methods are there to help you write conditionals based on the requested data.
```php
protected function afterFetch(Collection $collection): Collection
{
    if ($this->hasSort('total_price')) {
        // Do some additional stuff...
    }
    
    return $collection;
}
``` 

### Optimization

#### Explicit select
By default all queries will be executed selecting all database fields. E.g. ```SELECT users.*```
With large datasets this could however lead to large memory usage even if only the ```id``` field is requested for each model.
Call the ```explicitSelect()``` method to select only the requested fields in the database query. In addition; call the ```select()``` method when registering a field which relies on other fields than it's own. Example:
```php
    protected function configure(): void
    {
        $this->explicitSelect();

        // Fields
        $this->field('id')->filterable()->sortable();
        $this->field('first_name')->filterable()->sortable();
        $this->field('last_name')->filterable()->sortable();
        $this->field('full_name')->select(['first_name', 'last_name']);
    }
```

#### Eager loading
When a custom attribute uses related models when calculating it's value you should always eager load this relations to prevent n+1 problems. Use the ```load()``` method when registering a field to eager load these relations only when this field is requested.
```php
    protected function configure(): void
    {
        // Fields
        $this->field('id')->filterable()->sortable();
        $this->field('total_amount')->load(['orderLines']);
    }
```


## Security
If you want to limit the exposed data (probably based on the user being logged in), override the ```authorize()``` method to filter down to what you want to be visible for the current user. This method receives a ```Builder``` object and the current user as parameters.
Alternatively you could use Laravel's built in [global scopes](https://laravel.com/docs/5.8/eloquent#global-scopes).  

## Controller usage
It is a common use case to store data using an api and wanting to retrieve data dynamically in the same call. You can add the dynamic Jory functionality to your own controllers using the ```JosKolenberg\LaravelJory\Facades\Jory``` facade.

```php
public function store(Request $request)
{
	$user = User::create([
		'name' => $request->input('name')
	]);
	
	return Jory::on($user);
}
```
The example above will use the (optional) [Jory query](https://packagist.org/packages/joskolenberg/jory) from the 'jory' parameter in the request, but if you don't want to be dynamic you could also set the [Jory query](https://packagist.org/packages/joskolenberg/jory) manually using the ```apply()``` method.

```php
return Jory::on($user)->apply([
	'fld' => ['id', 'name', 'etc']
]);
```
The facade can also be used on existing queries or a model's class name.
```php
return Jory::on(User::where('active', true));

return Jory::on(User::class);
```

## Config
To override Jory's default settings publish the configuration using:
```
php artisan jory:publish"
```
Which is just a shortcut for:
```
php artisan vendor:publish --provider="JosKolenberg\LaravelJory\JoryServiceProvider"
```

All setting in the [config](https://github.com/joskolenberg/laravel-jory/blob/master/config/jory.php) file should be pretty self explanatory.

That's it! Any suggestions or issues? Please contact me!

Happy coding!

Jos Kolenberg <jos@kolenberg.net>