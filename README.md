[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/joskolenberg/laravel-jory/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/joskolenberg/laravel-jory/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/joskolenberg/laravel-jory/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/joskolenberg/laravel-jory/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/joskolenberg/laravel-jory/badges/build.png?b=master)](https://scrutinizer-ci.com/g/joskolenberg/laravel-jory/build-status/master)
[![Total Downloads](https://poser.pugx.org/joskolenberg/laravel-jory/downloads)](https://packagist.org/packages/joskolenberg/laravel-jory)
[![Latest Stable Version](https://poser.pugx.org/joskolenberg/laravel-jory/v/stable)](https://packagist.org/packages/joskolenberg/laravel-jory)
[![License](https://poser.pugx.org/joskolenberg/laravel-jory/license)](https://packagist.org/packages/joskolenberg/laravel-jory)

# Laravel-Jory: Flexible Eloquent API Resources

## Concept Overview
Laravel Jory creates a dynamic API for your Laravel application to serve the data from your Eloquent models.
JoryResources are comparable to Laravel's built-in Resource classes but you only write (or [generate](/{{route}}/{{version}}/generator)) a JoryResource once for each model. Next, your data can be queried in a flexible way by passing a [Jory Query](/{{route}}/{{version}}/query_introduction) to the [Jory Endpoints](/{{route}}/{{version}}/endpoints).


Jory is designed to be simple enough to master within minutes but flexible enough to fit 95% of your data-fetching use-cases. It brings Eloquent Query Builder's most-used features directly to your frontend.  


<a name="supported-functions"></a>
## Supported Functions
### Querying
- [Selecting fields](/{{route}}/{{version}}/query_fields) (database fields & custom attributes)
- [Filtering](/{{route}}/{{version}}/query_filters) (including nested ```and``` and ```or``` clauses and custom filters)
- [Sorting](/{{route}}/{{version}}/query_sorts) (including custom sorts)
- [Relations](/{{route}}/{{version}}/query_relations)
- [Offset & Limit](/{{route}}/{{version}}/query_offset_and_limit)

### Endpoints
- Fetch a [single record](/{{route}}/{{version}}/endpoints#first) (like Laravel's ```first()```)
- Fetch a [single record by id](/{{route}}/{{version}}/endpoints#find) (like Laravel's ```find()```)
- Fetch [multiple records](/{{route}}/{{version}}/endpoints#get) (like Laravel's ```get()```)
- Fetch [multiple resources at once](/{{route}}/{{version}}/endpoints#multiple)

### Aggregates
- [Count](/{{route}}/{{version}}/endpoints#aggregates)
- [Exists](/{{route}}/{{version}}/endpoints#aggregates)

### Metadata
- [Total records](/{{route}}/{{version}}/metadata#total) (for pagination)
- [Query count](/{{route}}/{{version}}/metadata#query-count)


For more information take a look at the [Docs](https://laravel-jory.kolenberg.net/docs).


Happy coding!

Jos Kolenberg <jos@kolenberg.net>