<?php

namespace JosKolenberg\LaravelJory\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use JosKolenberg\Jory\Support\Relation;
use JosKolenberg\LaravelJory\Config\Field;
use JosKolenberg\LaravelJory\Helpers\ResourceNameHelper;
use JosKolenberg\LaravelJory\JoryBuilder;
use JosKolenberg\LaravelJory\JoryResource;

trait LoadsJoryRelations
{
    /**
     * Load the given relations on the given model(s).
     *
     * @param Collection $models
     * @param \JosKolenberg\LaravelJory\JoryResource $joryResource
     * @throws \JosKolenberg\Jory\Exceptions\JoryException
     */
    protected function loadRelations(Collection $models, JoryResource $joryResource): void
    {
        foreach ($joryResource->getJory()->getRelations() as $relation) {
            $this->loadRelation($models, $relation, $joryResource);
        }

        // We clear Eloquent's relations, so any filtering on relations
        // doesn't affect any custom attributes which rely on relations.
        $models->each(function ($model) {
            $model->setRelations([]);
        });

        // Load any relations which need to be eager loaded
        $this->applyEagerLoads($models, $this->joryResource);

        // Hook into the afterFetch() method for custom tweaking in subclasses.
        $joryResource->afterFetch($models);
    }

    /**
     * Load the given relation on a collection of models.
     *
     * @param Collection $collection
     * @param Relation $relation
     * @param \JosKolenberg\LaravelJory\JoryResource $joryResource
     * @throws \JosKolenberg\Jory\Exceptions\JoryException
     */
    protected function loadRelation(Collection $collection, Relation $relation, JoryResource $joryResource): void
    {
        if ($collection->isEmpty()) {
            return;
        }

        $relationName = ResourceNameHelper::explode($relation->getName())->baseName;

        // Build the JoryResource to be applied on the relation query.
        $relatedJoryResource = $joryResource
            ->getConfig()
            ->getRelation($relationName)
            ->getJoryResource()
            ->fresh();

        $relatedJoryResource->setJory($relation->getJory());

        // Create a JoryBuilder which can alter the relation query with the data in the Jory query and JoryResource
        $joryBuilder = app()->makeWith(JoryBuilder::class, ['joryResource' => $relatedJoryResource]);

        // Laravel's relations are in camelCase, convert if in case we're not in camelCase mode
        $relationName = Str::camel($relationName);

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
        $joryBuilder->loadRelations($allRelated, $relatedJoryResource);
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

    /**
     * Load any relations to be eager loaded.
     *
     * @param \Illuminate\Database\Eloquent\Collection $collection
     * @param \JosKolenberg\LaravelJory\JoryResource $joryResource
     */
    protected function applyEagerLoads(Collection $collection, JoryResource $joryResource): void
    {
        $eagerLoads = [];

        $configuredFields = $joryResource->getConfig()->getFields();

        foreach ($joryResource->getJory()->getFields() as $fieldName) {
            $configuredField = Arr::first($configuredFields, function (Field $configuredField) use ($fieldName) {
                return $configuredField->getField() === $fieldName;
            });

            if ($configuredField->getEagerLoads() !== null) {
                $eagerLoads = array_merge($eagerLoads, $configuredField->getEagerLoads());
            }
        }

        $collection->load($eagerLoads);
    }
}