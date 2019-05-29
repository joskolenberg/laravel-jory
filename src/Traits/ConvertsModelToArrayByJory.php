<?php

namespace JosKolenberg\LaravelJory\Traits;

use Illuminate\Support\Str;
use JosKolenberg\Jory\Jory;
use Illuminate\Database\Eloquent\Model;
use JosKolenberg\LaravelJory\Helpers\CaseManager;
use JosKolenberg\LaravelJory\JoryBuilder;
use JosKolenberg\LaravelJory\JoryResource;
use JosKolenberg\LaravelJory\Register\JoryResourcesRegister;

trait ConvertsModelToArrayByJory
{
    /**
     * Convert a single model to an array based on the request in the Jory object.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param \JosKolenberg\Jory\Jory $jory
     * @return array
     */
    protected function modelToArrayByJory(Model $model, JoryResource $joryResource): array
    {
        $jory = $joryResource->getJory();

        $case = app(CaseManager::class);

        $result = [];
        foreach ($jory->getFields() as $field) {
            $result[$field] = $case->isCamel() ? $model->{Str::snake($field)} : $model->$field;
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

            // Get the related records which were fetched earlier. These are stored in the model under the full relation's name including alias
            $related = $model->joryRelations[$relation->getName()];

            // Get the related JoryBuilder to convert the related records to arrays
            $relatedModelClass = $joryResource->getConfig()->getRelation($relationName)->getModelClass();
            $relatedJoryResource = app(JoryResourcesRegister::class)
                ->getByModelClass($relatedModelClass)
                ->fresh();
            $relatedJoryResource->setJory($relation->getJory());
            $relatedJoryBuilder = app()->makeWith(JoryBuilder::class, ['joryResource' => $relatedJoryResource]);

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