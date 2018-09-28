<?php

namespace JosKolenberg\LaravelJory\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use JosKolenberg\LaravelJory\AbstractJoryBuilder;
use JosKolenberg\LaravelJory\GenericJoryBuilder;

/**
 * Trait to mark a Model as Jory-queryable.
 *
 * This trait must be applied to a model as a marker even if the jory method is manually added to the model.
 *
 * Trait JoryTrait
 */
trait JoryTrait
{
    /**
     * Return the JoryBuilder to query on this model.
     * A generic one by default, override this method to apply custom JoryBuilder class.
     *
     * @return AbstractJoryBuilder
     */
    public static function jory(): AbstractJoryBuilder
    {
        return (new GenericJoryBuilder())->onModel(static::class);
    }

    /**
     * Register the routes for querying this model using the data in the request's jory parameter.
     *
     * @return void
     */
    public static function joryRoutes($uri): void
    {
        Route::get($uri, function (Request $request){
            return static::jory()->applyRequest($request);
        });
    }
}
