<?php

namespace JosKolenberg\LaravelJory\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Database\Eloquent\Builder;
use JosKolenberg\Jory\Jory;
use JosKolenberg\LaravelJory\GenericJoryBuilder;
use JosKolenberg\LaravelJory\AbstractJoryBuilder;

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
     * Override to apply a custom JoryBuilder class for the model.
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

    /**
     * Export the model to an array based on a Jory object (fields and relations).
     *
     * @param Jory $jory
     * @return array
     */
    public function toArrayByJory(Jory $jory)
    {
        // We will load the relations manually so remove them from Laravel's toArray() export.
        $relationNames = [];
        foreach ($jory->getRelations() as $relation) {
            $relationNames = $relation->getName();
        }
        $this->makeHidden($relationNames);

        // When no fields are specified, we'll use all the model's fields
        // if fields are specified, we use only these.
        $result = $this->toArray();
        if ($jory->getFields() !== null) {
            $result = array_intersect_key($result, array_flip($jory->getFields()));
        }

        // Add the relations to the result
        foreach ($jory->getRelations() as $relation){
            $relationName = $relation->getName();

            $related = $this->$relationName;

            if($related == null){
                $result[$relationName] = null;
            } elseif ($related instanceof Model){
                $result[$relationName] = $related->toArrayByJory($relation->getJory());
            } else {
                $relationResult = [];
                foreach ($related as $relatedModel) {
                    $relationResult[] = $relatedModel->toArrayByJory($relation->getJory());
                }
                $result[$relationName] = $relationResult;
            }
        }

        return $result;
    }
}
