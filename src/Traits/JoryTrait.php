<?php

namespace JosKolenberg\LaravelJory\Traits;

use JosKolenberg\Jory\Jory;
use Illuminate\Database\Eloquent\Model;
use JosKolenberg\LaravelJory\JoryBuilder;

/**
 * Trait to make a Model "Jory-queryable".
 *
 * Trait JoryTrait
 */
trait JoryTrait
{
    /**
     * Return the JoryBuilder to query on this model.
     *
     * @return JoryBuilder
     */
    public static function jory(): JoryBuilder
    {
        return static::getJoryBuilder()->onQuery((new static())->query());
    }

    /**
     * Get a new JoryBuilder instance for the model.
     * Override to apply a custom JoryBuilder class for the model.
     *
     * @return JoryBuilder
     */
    public static function getJoryBuilder(): JoryBuilder
    {
        return new JoryBuilder();
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
            $relationNames[] = camel_case($relation->getName());
        }
        $this->makeHidden($relationNames);

        // When no fields are specified, we'll use all the model's fields
        // if fields are specified, we use only these.
        $result = $this->toArray();
        if ($jory->getFields() !== null) {
            $result = array_intersect_key($result, array_flip($jory->getFields()));
        }

        // Add the relations to the result
        foreach ($jory->getRelations() as $relation) {
            $relationName = $relation->getName();
            $cameledRelationName = camel_case($relationName);

            $related = $this->$cameledRelationName;

            if ($related == null) {
                $result[$relationName] = null;
            } elseif ($related instanceof Model) {
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
