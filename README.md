![Run Tests](https://github.com/joskolenberg/laravel-jory/workflows/Run%20Tests/badge.svg)
[![Total Downloads](https://poser.pugx.org/joskolenberg/laravel-jory/downloads)](https://packagist.org/packages/joskolenberg/laravel-jory)
[![Latest Stable Version](https://poser.pugx.org/joskolenberg/laravel-jory/v/stable)](https://packagist.org/packages/joskolenberg/laravel-jory)
[![License](https://poser.pugx.org/joskolenberg/laravel-jory/license)](https://packagist.org/packages/joskolenberg/laravel-jory)

# Laravel-Jory: Flexible Eloquent API Resources
[Complete documentation](https://laravel-jory.kolenberg.net/docs)


## Concept Overview
Laravel Jory creates a dynamic API for your Laravel application to serve the data from your Eloquent models.
JoryResources are comparable to Laravel's built-in Resource classes but you only write (or [generate](https://laravel-jory.kolenberg.net/docs/2.0/generator)) a JoryResource once for each model. Next, your data can be queried in a flexible way by passing a [Jory Query](https://laravel-jory.kolenberg.net/docs/2.0/fetching_introduction) to the [Jory Endpoints](https://laravel-jory.kolenberg.net/docs/2.0/endpoints).


Jory is designed to be simple enough to master within minutes but flexible enough to fit 95% of your data-fetching use-cases. It brings Eloquent Query Builder's most-used features directly to your frontend.  


<a name="supported-functions"></a>
## Supported Functions
### Querying
- [Selecting fields](https://laravel-jory.kolenberg.net/docs/2.0/query_fields) (database fields & custom attributes)
- [Filtering](https://laravel-jory.kolenberg.net/docs/2.0/query_filters) (including nested ```and``` and ```or``` clauses and custom filters)
- [Sorting](https://laravel-jory.kolenberg.net/docs/2.0/query_sorts) (including custom sorts)
- [Relations](https://laravel-jory.kolenberg.net/docs/2.0/query_relations)
- [Offset & Limit](https://laravel-jory.kolenberg.net/docs/2.0/query_offset_and_limit)

### Endpoints
- Fetch a [single record](https://laravel-jory.kolenberg.net/docs/2.0/endpoints#first) (like Laravel's ```first()```)
- Fetch a [single record by id](https://laravel-jory.kolenberg.net/docs/2.0/endpoints#find) (like Laravel's ```find()```)
- Fetch [multiple records](https://laravel-jory.kolenberg.net/docs/2.0/endpoints#get) (like Laravel's ```get()```)
- Fetch [multiple resources at once](https://laravel-jory.kolenberg.net/docs/2.0/endpoints#multiple)

### Aggregates
- [Count](https://laravel-jory.kolenberg.net/docs/2.0/endpoints#aggregates)
- [Exists](https://laravel-jory.kolenberg.net/docs/2.0/endpoints#aggregates)

### Metadata
- [Total records](https://laravel-jory.kolenberg.net/docs/2.0/metadata#total) (for pagination)
- [Query count](https://laravel-jory.kolenberg.net/docs/2.0/metadata#query-count)


For more information take a look at the [docs](https://laravel-jory.kolenberg.net/docs).


Happy coding!

Jos Kolenberg <jos@kolenbergsoftwareontwikkeling.nl>