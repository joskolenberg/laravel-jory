<?php

namespace JosKolenberg\LaravelJory\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use JosKolenberg\LaravelJory\AbstractJoryBuilder;
use JosKolenberg\LaravelJory\Exceptions\LaravelJoryException;
use JosKolenberg\LaravelJory\GenericJoryBuilder;

/**
 * Trait to mark a Model as Jory-queryable.
 *
 * Trait JoryTrait
 */
trait JoryTrait
{
    /**
     * Return the JoryBuilder to query on this model.
     *
     * @throws LaravelJoryException
     *
     * @return AbstractJoryBuilder
     */
    public static function jory(): AbstractJoryBuilder
    {
        return static::getJoryBuilder()->onQuery(static::getJoryBaseQuery());
    }

    /**
     * Register the routes for querying this model using the data in the request's jory parameter.
     *
     * @return void
     */
    public static function joryRoutes($uri): void
    {
        Route::get($uri, function (Request $request) {
            return static::jory()->applyRequest($request);
        });
    }

    /**
     * Get a new JoryBuilder instance for the model.
     * A generic one by default, override in to apply a custom JoryBuilder class.
     *
     * @throws LaravelJoryException
     *
     * @return GenericJoryBuilder
     */
    public static function getJoryBuilder(): AbstractJoryBuilder
    {
        return new GenericJoryBuilder();
    }

    /**
     * Get the base query to build upon with a jorybuilder.
     *
     * @return Builder
     */
    protected static function getJoryBaseQuery(): Builder
    {
        return (new static())->query();
    }
}
