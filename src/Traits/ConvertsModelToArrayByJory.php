<?php

namespace JosKolenberg\LaravelJory\Traits;

use JosKolenberg\Jory\Jory;
use Illuminate\Database\Eloquent\Model;
use JosKolenberg\LaravelJory\Helpers\CaseManager;

trait ConvertsModelToArrayByJory
{
    /**
     * Convert a single model to an array based on the request in the Jory object.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param \JosKolenberg\Jory\Jory $jory
     * @return array
     */
    protected function modelToArrayByJory(Model $model, Jory $jory): array
    {
        $case = app(CaseManager::class);

        $result = [];
        foreach ($jory->getFields() as $field) {
            $result[$field] = $case->isCamel() ? $model->{snake_case($field)} : $model->$field;
        }

        // Add the relations to the result
        foreach ($jory->getRelations() as $relation) {
            $relationName = $relation->getName();
            $relationAlias = $relationName;

            // Split the relation name in Laravel's relation name and the alias, if there is one.
            $relationParts = explode(' as ', $relationName);
            if (count($relationParts) > 1) {
                $relationName = $relationParts[0];
                $relationAlias = $relationParts[1];
            }

            // Laravel's relations are in camelCase, convert if we're not in camelCase mode
            $relationName = ! $case->isCamel() ? camel_case($relationName) : $relationName;

            // Get the related records which were fetched earlier. These are stored in the model under the full relation's name including alias
            $related = $model->getJoryRelation($relation->getName());

            // Get the related JoryBuilder to convert the related records to arrays
            $relatedModel = $model->{$relationName}()->getRelated();
            $relatedJoryBuilder = $relatedModel::getJoryBuilder()->applyJory($relation->getJory());

            if ($related === null) {
                // No related model found
                $result[$relationAlias] = null;
            } elseif ($related instanceof Model) {
                // A related model is found
                $result[$relationAlias] = $relatedJoryBuilder->modelToArray($related);
            } else {
                // A related collection
                $relationResult = [];
                foreach ($related as $relatedModel) {
                    $relationResult[] = $relatedJoryBuilder->modelToArray($relatedModel);
                }
                $result[$relationAlias] = $relationResult;
            }
        }

        return $result;
    }
}