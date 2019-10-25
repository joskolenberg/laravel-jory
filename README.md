[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/joskolenberg/laravel-jory/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/joskolenberg/laravel-jory/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/joskolenberg/laravel-jory/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/joskolenberg/laravel-jory/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/joskolenberg/laravel-jory/badges/build.png?b=master)](https://scrutinizer-ci.com/g/joskolenberg/laravel-jory/build-status/master)
[![Total Downloads](https://poser.pugx.org/joskolenberg/laravel-jory/downloads)](https://packagist.org/packages/joskolenberg/laravel-jory)
[![Latest Stable Version](https://poser.pugx.org/joskolenberg/laravel-jory/v/stable)](https://packagist.org/packages/joskolenberg/laravel-jory)
[![License](https://poser.pugx.org/joskolenberg/laravel-jory/license)](https://packagist.org/packages/joskolenberg/laravel-jory)

# Laravel-Jory; Flexible Laravel Resources
This package creates a highly flexible Json api for your Laravel application and serves the data from your database to your (javascript) frontend safe and easily.

JoryResources are comparable to Laravel's Resources, but can be queried in a flexible way by passing a [Jory query](https://packagist.org/packages/joskolenberg/jory) from the frontend.

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
For a quick start you can call the ```jory:generate --all``` command to create a JoryResource for each of your models.
```
php artisan jory:generate --all
```
If you'd like to be more explicit you can use the ```jory:generate``` command and pick a single model from a list instead.
```
php artisan jory:generate
```
All these JoryResources will be pre-configured by using reflection on your model classes.

## Routes
The package will register the following routes for all JoryResources:
- ```GET``` ```/jory/{resource}``` Get an [array of items](#resource-collection) for this resource based on the [Jory query](https://packagist.org/packages/joskolenberg/jory) in the 'jory' parameter.
- ```GET``` ```/jory/{resource}/{id}``` Get a [single record](#single-resource) and apply the 'jory' parameter. 
- ```GET``` ```/jory/{resource}/count``` Get the [record count](#resource-count) for the resource based on the filters in the 'jory' parameter.
- ```GET``` ```/jory``` Get [multiple](#multiple-resources) unrelated resources in one call.

The ```resource``` name is derived from the model's class name in kebabcase but can be overridden by setting the ```$uri``` property in your JoryResource.

#### Resource collection
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
This endpoint accepts a json string with key value pairs, the key being the resource name and the value being the [Jory query](https://packagist.org/packages/joskolenberg/jory).
Possible key values:
- ```'{resource}'``` get a list for this resource.
- ```'{resource}:{id}``` get a resource by id.
- ```'{resource}:count``` get a resource count.
- All keys can be post-fixed with ``` as {alias}``` to return the result on a different key, which allows to fetch a single resource multiple times.

For example, the following keys would all be valid: ```band```, ```band:2```, ```band:count```, ```band:2 as beatles```, ```band:count as number_of_bands```

Example call fetching:
- All bands having a name containing 'le'. ```band```: ```{"filter":{"field":"name","operator":"like","data":"%le%"}}```
- All bands without a year_end returned as active_bands. ```band as active_bands```: ```{"filter":{"field":"year_end","operator":"is_null"}}```
- The total number of bands as band_count. ```band:count as band_count```: ```{}``` (We apply no filtering here so just send an empty object)
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
            },
            "band:count as band_count": {}
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
        "band_count": 143,
    }
}
```

## JoryResources
Use the JoryResource classes to configure your Jory api.

### Registering
JoryResources are automatically discovered as long as they are in the default App\Http\JoryResources namespace.
Alter the jory [config](#config) file to change this behaviour, or use the ```register()``` method on the Jory facade to register manually.

### Linked model
The ```modelClass``` attribute needs a reference to the related model. Normally there would be only one JoryResource for each of your models (however multiple is possible).
```php
protected $modelClass = AlbumCover::class;
```

### Uri
By default your model can be called in the jory api using the kebabcased model name (e.g. ```/jory/album-cover```), you can set your own using the $uri attribute. The uri must be unique across all your JoryResources.
```php
protected $uri = 'albumcover';
```

### Configuring
To configure your JoryResource use the ```configure``` method, use this method to register all fields, filters, sorts and relations you want to expose.

Notes:
- All configuration is explicit, so anything that isn't registered here is NOT available in the api.
- All multiword configuration is done using snake_case (even if the api is set to camelcase in the config file).

```php
class BandJoryResource extends JoryResource
{
    protected function configure(): void
    {
        // Apply config
    }
}
```

Note: It's advised to use the ```jory:generate``` command to create and pre-configure your JoryResources.



#### Registering fields
A field can be a model's database column or custom attribute which can be requested and can be registered using the ```field()``` method.

Apply the ```hideByDefault()``` method to NOT return this field when no explicit fields are requested in the [Jory query](https://packagist.org/packages/joskolenberg/jory).
```php
protected function configure(): void
{
    $this->field('id');
    $this->field('name');
    $this->field('number_of_songs');
    $this->field('custom_attribute_with_heavy_calculations')->hideByDefault();
    ...
}
```


#### Registering filters
A filter option can be registered using the ```filter()``` method.
```php
protected function configure(): void
{
    ...

    $this->filter('id');
    $this->filter('name');
    $this->filter('number_of_songs');
    ...
}
```
By default there will be queried on a database column matching the name of the filter, but custom filters can be created by adding a FilterScope as a second parameter. This FilterScope class must implement the ```JosKolenberg\LaravelJory\Scopes\FilterScope``` interface.

FilterScope: 
```php
use JosKolenberg\LaravelJory\Scopes\FilterScope;

class HasSongWithTitleFilter implements FilterScope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param Builder|Relation $builder
     * @param string $operator
     * @param mixed $data
     * @return void
     */
    public function apply($builder, string $operator = null, $data = null)
    {
        $builder->whereHas('songs', function ($builder) use ($operator, $data) {
            $builder->where('title', $operator, $data);
        });
    }
}
```
JoryResource configuration:
```php
protected function configure(): void
{
    ...

    $this->filter('id');
    $this->filter('name');
    $this->filter('number_of_songs');
    $this->filter('has_song_with_title', new HasSongWithTitleFilter);
    ...
}
```

By default all operators are available to the api, use the ```operators``` method if you only want to offer a limited set of operators:
The available operators are: '=', '!=', '<>', '>', '>=', '<', '<=', '<=>', 'like', 'not_like', 'is_null', 'not_null', 'in' and 'not_in'.

```php
protected function configure(): void
{
    ...

    $this->filter('id');
    $this->filter('name');
    $this->filter('number_of_songs')->operators(['>', '>=', '=', '<=', '<']);
    $this->filter('has_song_with_title', new HasSongWithTitleFilter)->operators(['like', '=']);
    ...
}
```

#### Registering sorts
A sort option can be registered using the ```sort()``` method.

Apply the ```default()``` method to apply this sort when no sort to the query parameter is given in the request.
```php
protected function configure(): void
{
    ...

    $this->sort('id');
    $this->sort('name')->default();
    ...
}
```
By default sorted will be applied on the database column matching the name of the sort, but custom sorts can be created by adding a SortScope as a second parameter. This SortScope class must implement the ```JosKolenberg\LaravelJory\Scopes\SortScope``` interface.

SortScope: 
```php
use JosKolenberg\LaravelJory\Scopes\SortScope;

class BandNameSort implements SortScope
{

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param Builder|Relation $builder
     * @param string $order
     * @return void
     */
    public function apply($builder, string $order = 'asc')
    {
        $builder->join('bands', 'band_id', 'bands.id')->orderBy('bands.name', $order);
    }
}
```
JoryResource configuration:
```php
protected function configure(): void
{
    ...

    $this->sort('id');
    $this->sort('name')->default();
    $this->sort('band_name', new BandNameSort);
    ...
}
```

#### Shorthand Field, Filter and Sort registering
Because all fields will most likely be used for filtering and sorting as well, convenient ```filterable()``` and ```sortable()``` methods are provided to register this all in one go. You can use an optional callback to fill out any filter and sort details. 
```php
protected function configure(): void
{
    $this->field('id')->filterable()->sortable();

    $this->field('name')
        ->filterable()
        ->sortable(function (Sort $sort) {
             $sort->default();
         });

    $this->field('number_of_songs')
        ->filterable(function (Filter $filter) {
            $filter->operators(['>', '>=', '=', '<=', '<']);
        })->sortable();

    $this->field('custom_attribute_with_heavy_calculations')->hideByDefault();
    
    $this->filter('has_song_with_title', new HasSongWithTitleFilter)->operators(['like', '=']);

    $this->sort('band_name', new BandNameSort);
}
```

#### Registering relations
A relation can be registered using the ```relation()``` method.
When relations are fetched, the JoryResource for the related model will be used to handle the relation query, so any model you want to query using a relation must have it's own JoryResource registered.

You can pass a JoryResource class as a second parameter if you don't want to use the registered JoryResource for this relation.

```php
$this->relation('albums');

$this->relation('songs', AlternateSongJoryResource::class);
```
When calling a Jory api, relations can be requested as a count and/or with an alias as well.

Example: Requesting a band with:
- Their name
- Their total number of albums
- Their 3 latest albums
- Their total number of songs
- Their number of songs with a top 1 ranking
```javascript
axios.get('jory/band/1', {
    params: {
        jory: {
            fields: ["name"],
            relations: {
                "albums:count": {},
                "albums as latest_albums" : {
                    fields: ["name"],
                    sorts: ["-release_date"],
                    limit: 3
                },
                "songs:count as song_count": {},
                "songs:count as hit_song_count": {
                    filter: {
                        field: "highest_ranking",
                        operator: "=",
                        data: 1,
                    },
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
        "name": "Rolling Stones",
        "albums:count": 15,
        "latest_albums": [
            {
                "name": "Exile on main st.",
                "release_date": "1972-05-12"
            },
            {
                "name": "Sticky Fingers",
                "release_date": "1971-04-23"
            },
            {
                "name": "Let it bleed",
                "release_date": "1969-12-05"
            }
        ],
        "song_count": 143,
        "hit_song_count": 31
    }
}
```


A note on relations: mergeTo relations cannot be used by Jory due their dynamic nature. However, an easy workaround can be made by creating a separate belongsTo relation for each "morphable" type.  

#### Setting pagination defaults
Use the ```limitDefault()``` method to set the default limit when no limit is given.
Use the ```limitMax()``` to set the maximum number of records that can be requested at once for this resource.

```php
$this->limitDefault(25)->limitMax(100);
```

## Optimization

### Explicit select
By default all queries will be executed selecting all database fields. E.g. ```SELECT users.*```
With large datasets this could however lead to large memory usage even if only the ```id``` field is requested for a model.
Call the ```explicitSelect()``` method to select only the requested fields in the database query.

In addition; call the ```select()``` method when registering a field which relies on other fields than it's own. Or use ```noSelect()``` for custom attributes which don't use any fields from the current model to prevent the name of the custum attribute to be added to the select list in the query. 

Example:
```php
    protected function configure(): void
    {
        $this->explicitSelect();

        // Fields
        $this->field('id')->filterable()->sortable();
        $this->field('first_name')->filterable()->sortable();
        $this->field('last_name')->filterable()->sortable();
        $this->field('full_name')->select(['first_name', 'last_name']);
        $this->field('total_revenue')->noSelect();
    }
```
Note; the ```explicitSelect``` option is smart enough to detect any primary or foreign keys which need to be added to the query for any requested relations to be loaded.


### Eager loading
When a custom attribute uses related models when calculating it's value you should always eager load these relations to prevent n+1 problems. Use the ```load()``` method when registering a field to eager load these relations when this field is requested.
```php
    protected function configure(): void
    {
        // Fields
        $this->field('id')->filterable()->sortable();
        $this->field('total_amount')->load(['orderLines']);
    }
```


## Security
It's important to know that once you've generated or written a JoryResource, all the configured fields in that JoryResource are publicly available. Use the methods below to secure your data. 

### Limiting fields
If you don't want to expose some of the fields in your Jory api, just don't register them. Since you have to be explicit about what is available any unregistered fields will not be open for the api.

### Require authentication
Add the ```auth``` middleware to the jory routes by adding it in the jory [config](#config) file to require a user to be logged in to consume the api.

jory.php
```php
    ...

    'routes' => [

        'enabled' => true,

        'path' => '/jory',

        'middleware' => [
            'api',
            'auth',
            \JosKolenberg\LaravelJory\Http\Middleware\SetJoryHandler::class
        ],

    ],
    ...
```

### Scoping the query
If you want to limit the available database records (probably based on the user being logged in), override the ```authorize()``` method in the JoryResource to filter down to what you want to be visible for the current user. This method receives a ```Builder``` object and the current user as parameters.
Alternatively you could use Laravel's built in [global scopes](https://laravel.com/docs/5.8/eloquent#global-scopes).
```php
public function authorize($builder, $user = null): void
{
    if(!$user->isSuperAdmin()){
        $builder->where('user_id', '=', $user->id);
    }
}
```  

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
The example above will use the (optional) [Jory query](https://packagist.org/packages/joskolenberg/jory) from the 'jory' parameter in the request, and return the data accordingly.

The facade can also be used on existing queries or a model's class name.
```php
return Jory::on(User::where('active', true));

return Jory::on(User::class);
```

## Config
To override Jory's default settings publish the configuration using:
```
php artisan vendor:publish --provider="JosKolenberg\LaravelJory\JoryServiceProvider"
```

The config file has options to:
- Change the keys on which data should be requested and returned
- Set some global default settings for all your JoryResources like available filter operators and limit settings.
- Set the api to work in camelCase instead of snake_case. (This setting can be also be overridden by the api consumer by adding a ```case``` key to request)
- Configure autodetection for your JoryResource classes.
- Configure the Jory api route settings.
- Configure the autogenerator for JoryResources.

All setting in the [config](https://github.com/joskolenberg/laravel-jory/blob/master/config/jory.php) file should be pretty self explanatory.

## Various

### Error messages
Whenever a request is made using invalid data the client receives a 422 status code along with helpful error messages like:
```
Field "first_nam" is not available, did you mean "first_name"? (Location: fields.first_nam)
```
```
Field "releaseDaate" is not available for filtering, did you mean "releaseDate"? (Location: albums.filter(or).1(releaseDaate))
```
```
Jory string is no valid json.
```

### Base64 support
The Jory api may be consumed with base64 encoded json strings as well to provide prettier urls. As a bonus this can also resolve some edge-case problems when using reserved keywords in your json string combined with webserver restrictions.
```
GET /jory/band?jory=eyJmaWx0ZXIiOnsiZiI6Im51bWJlcl9vZl9hbGJ1bXNfaW5feWVhciIsIm8iOiI9IiwiZCI6eyJ5ZWFyIjoxOTcwLCJ2YWx1ZSI6MX19LCJybHQiOnsiYWxidW1zIjp7ImZsdCI6eyJmIjoiaGFzX3Nvbmdfd2l0aF90aXRsZSIsIm8iOiJsaWtlIiwiZCI6IiVsb3ZlJSJ9LCJybHQiOnsiY292ZXIiOnt9fX0sInNvbmdzIjp7ImZsdCI6eyJmIjoidGl0bGUiLCJvIjoibGlrZSIsImQiOiIlYWMlIn0sInJsdCI6eyJhbGJ1bSI6eyJybHQiOnsiYmFuZCI6e319fX0sImZsZCI6WyJpZCIsInRpdGxlIl19LCJwZW9wbGUiOnsiZmxkIjpbImlkIiwibGFzdF9uYW1lIl0sInJsdCI6eyJpbnN0cnVtZW50cyI6e319fX19
```
equals
```
GET /jory/band?jory={"filter":{"f":"number_of_albums_in_year","o":"=","d":{"year":1970,"value":1}},"rlt":{"albums":{"flt":{"f":"has_song_with_title","o":"like","d":"%love%"},"rlt":{"cover":{}}},"songs":{"flt":{"f":"title","o":"like","d":"%ac%"},"rlt":{"album":{"rlt":{"band":{}}}},"fld":["id","title"]},"people":{"fld":["id","last_name"],"rlt":{"instruments":{}}}}}
```
Note: Adding base64 encoding does not provide any security. For real security use the other [security](#security) options instead.


That's it! Any suggestions or issues? Please contact me!

Happy coding!

Jos Kolenberg <jos@kolenberg.net>