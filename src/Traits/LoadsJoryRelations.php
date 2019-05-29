<?php

namespace JosKolenberg\LaravelJory\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use JosKolenberg\Jory\Support\Relation;
use Illuminate\Database\Eloquent\Collection;
use JosKolenberg\LaravelJory\JoryBuilder;
use JosKolenberg\LaravelJory\JoryResource;
use JosKolenberg\LaravelJory\Register\JoryResourcesRegister;

trait LoadsJoryRelations
{

    /**
     * Load the given relations on the given model(s).
     *
     * @param Collection $models
     * @param array $relations
     * @throws \JosKolenberg\LaravelJory\Exceptions\LaravelJoryException
     * @throws \JosKolenberg\Jory\Exceptions\JoryException
     */
    protected function loadRelations(Collection $models, array $relations): void
    {
        foreach ($relations as $relation) {
            $this->loadRelation($models, $relation);
        }

        // We clear Eloquent's relations, so any filtering on relations
        // doesn't affect any custom attributes which rely on relations.
        $models->each(function ($model) {
            $model->setRelations([]);
        });

        // Hook into the afterFetch() method for custom tweaking in subclasses.
        $this->joryResource->afterFetch($models);
    }

    /**
     * Load the given relation on a collection of models.
     *
     * @param Collection $collection
     * @param Relation $relation
     * @throws \JosKolenberg\LaravelJory\Exceptions\LaravelJoryException
     * @throws \JosKolenberg\Jory\Exceptions\JoryException
     */
    protected function loadRelation(Collection $collection, Relation $relation): void
    {
        if ($collection->isEmpty()) {
            return;
        }

        $relationName = $relation->getName();

        // Remove the alias part if the relation has one
        $relationParts = explode(' as ', $relationName);
        if (count($relationParts) > 1) {
            $relationName = Str::snake($relationParts[0]);
        }

        // Retrieve the model which will be queried to get the appropriate JoryBuilder
        $relatedModelClass = $this->joryResource
            ->getConfig()
            ->getRelation($relationName)
            ->getModelClass();
        $relatedJoryResource = app(JoryResourcesRegister::class)
            ->getByModelClass($relatedModelClass)
            ->fresh();
        $relatedJoryResource->setJory($relation->getJory());
        $joryBuilder = app()->makeWith(JoryBuilder::class, ['joryResource' => $relatedJoryResource]);

        // Laravel's relations are in camelCase, convert if in case we're not in camelCase mode
        $relationName = $this->case->toCamel($relationName);

        $collection->load([
            $relationName => function ($query) use ($joryBuilder, $relation) {
                // Apply the data in the subjory (filtering/sorting/...) on the query
                $joryBuilder->applyOnQuery($query);
            },
        ]);

        // Put all retrieved related models in single collection to load subrelations in a single call
        $allRelated = new Collection();
        foreach ($collection as $model) {
            $related = $model->$relationName;

            // We store the related records under the full relation name including alias
            $this->storeRelationOnModel($model, $relation->getName(), $related);

            if ($related === null) {
                continue;
            }

            if ($related instanceof Model) {
                $allRelated->push($related);
            } else {
                foreach ($related as $item) {
                    $allRelated->push($item);
                }
            }
        }

        // Load the subrelations
        $joryBuilder->loadRelations($allRelated, $relation->getJory()->getRelations());
    }

    /**
     * Store a joryrelation on the model.
     *
     * @param Model $model
     * @param string $relationName
     * @param $data
     */
    protected function storeRelationOnModel(Model $model, string $relationName, $data): void
    {
        $relations = $model->joryRelations;

        if($relations){
            $relations[$relationName] = $data;
        }else{
            $relations = [$relationName => $data];
        }

        $model->joryRelations = $relations;
    }
}