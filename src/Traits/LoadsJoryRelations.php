<?php

namespace JosKolenberg\LaravelJory\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use JosKolenberg\Jory\Support\Relation;
use JosKolenberg\LaravelJory\Helpers\ResourceNameHelper;
use JosKolenberg\LaravelJory\JoryBuilder;
use JosKolenberg\LaravelJory\JoryResource;

trait LoadsJoryRelations
{
    /**
     * Load the given relations on the given model(s).
     *
     * @param Collection $models
     * @param JoryResource $joryResource
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
    }

    /**
     * Load the given relation, relation:count or relation:first on a collection of models.
     *
     * @param Collection $collection
     * @param Relation $relation
     * @param JoryResource $joryResource
     */
    protected function loadRelation(Collection $collection, Relation $relation, JoryResource $joryResource): void
    {
        if ($collection->isEmpty()) {
            return;
        }

        switch (ResourceNameHelper::explode($relation->getName())->type){
            case 'count':
                $this->loadCountRelation($collection, $relation, $joryResource);
                break;
            case 'exists':
                $this->loadCountRelation($collection, $relation, $joryResource, true);
                break;
            case 'first':
                $this->loadFirstRelation($collection, $relation, $joryResource);
                break;
            default:
                $this->loadStandardRelation($collection, $relation, $joryResource);
        }
    }

    /**
     * Load a count relation on a collection of models.
     *
     * @param Collection $collection
     * @param Relation $relation
     * @param JoryResource $joryResource
     * @param bool $exists
     * @return void
     */
    protected function loadCountRelation(Collection $collection, Relation $relation, JoryResource $joryResource, $exists = false): void
    {
        $configuredRelation = $joryResource->getConfig()->getRelation($relation);

        $relatedJoryResource = $this->getJoryResourceForRelation($relation, $joryResource);
        $relatedJoryBuilder = $this->getJoryBuilderForResource($relatedJoryResource);

        foreach ($collection as $model) {
            $relationQuery = $relatedJoryBuilder->applyOnCountQuery($model->{$configuredRelation->getOriginalName()}());

            $result = $exists ? $relationQuery->exists() : $relationQuery->count();

            // We store the count under the full relation name including alias
            $this->storeRelationOnModel($model, $relation->getName(), $result);
        }
    }

    /**
     * Load the first item of a relation on a collection of models.
     *
     * @param Collection $collection
     * @param Relation $relation
     * @param JoryResource $joryResource
     * @return void
     */
    protected function loadFirstRelation(Collection $collection, Relation $relation, JoryResource $joryResource): void
    {
        $configuredRelation = $joryResource->getConfig()->getRelation($relation);

        $relatedJoryResource = $this->getJoryResourceForRelation($relation, $joryResource);
        $relatedJoryBuilder = $this->getJoryBuilderForResource($relatedJoryResource);

        foreach ($collection as $model) {
            // We store the count under the full relation name including alias
            $this->storeRelationOnModel($model, $relation->getName(), $relatedJoryBuilder->applyOnQuery($model->{$configuredRelation->getOriginalName()}())->first());
        }
    }

    /**
     * Load a standard relation on a collection of models.
     *
     * @param Collection $collection
     * @param Relation $relation
     * @param JoryResource $joryResource
     * @return void
     */
    protected function loadStandardRelation(Collection $collection, Relation $relation, JoryResource $joryResource): void
    {
        $configuredRelation = $joryResource->getConfig()->getRelation($relation);
        $relationName = $configuredRelation->getOriginalName();

        $relatedJoryResource = $this->getJoryResourceForRelation($relation, $joryResource);
        $relatedJoryBuilder = $this->getJoryBuilderForResource($relatedJoryResource);

        $collection->load([
            $relationName => function ($builder) use ($relatedJoryBuilder, $relation) {
                // Apply the data in the subjory (filtering/sorting/...) on the query
                $relatedJoryBuilder->applyOnQuery($builder);
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
        $relatedJoryBuilder->loadRelations($allRelated, $relatedJoryResource);
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
     * @param Collection $collection
     * @param JoryResource $joryResource
     */
    protected function applyEagerLoads(Collection $collection, JoryResource $joryResource): void
    {
        $eagerLoads = [];

        foreach ($joryResource->getJory()->getFields() as $fieldName) {
            $configuredField = $joryResource->getConfig()->getField($fieldName);

            if ($configuredField->getEagerLoads() !== null) {
                $eagerLoads = array_merge($eagerLoads, $configuredField->getEagerLoads());
            }
        }

        $collection->load($eagerLoads);
    }

    /**
     * Get a JoryBuilder from the container by JoryResource.
     *
     * @param JoryResource $joryResource
     * @return mixed
     */
    protected function getJoryBuilderForResource(JoryResource $joryResource)
    {
        return app()->makeWith(JoryBuilder::class, ['joryResource' => $joryResource]);
    }

    /**
     * Get a JoryResource based on a relation and parent JoryResource.
     *
     * @param Relation $relation
     * @param JoryResource $joryResource
     * @return JoryResource
     */
    protected function getJoryResourceForRelation(Relation $relation, JoryResource $joryResource): JoryResource
    {
        // Build the JoryResource to be applied on the relation query.
        $relatedJoryResource = $joryResource
            ->getConfig()
            ->getRelation($relation)
            ->getJoryResource()
            ->fresh();

        $relatedJoryResource->setJory($relation->getJory());

        return $relatedJoryResource;
    }
}