<?php

namespace JosKolenberg\LaravelJory\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use JosKolenberg\LaravelJory\Config\Field;
use JosKolenberg\LaravelJory\Config\Relation;
use JosKolenberg\LaravelJory\Helpers\ResourceNameHelper;
use JosKolenberg\LaravelJory\JoryResource;

trait HandlesJorySelects
{
    /**
     * Apply the select part of the query.
     *
     * @param $query
     * @param \JosKolenberg\LaravelJory\JoryResource $joryResource
     */
    protected function applySelects($query, JoryResource $joryResource): void
    {
        if (! $joryResource->getConfig()->hasExplicitSelect()) {
            $this->applyDefaultSelect($query);

            return;
        }

        $this->applySelectsByJory($query, $joryResource);
    }

    /**
     * Apply the default way of selecting columns.
     *
     * @param $query
     */
    protected function applyDefaultSelect($query)
    {
        $table = $query->getModel()->getTable();
        $query->select($table.'.*');
    }

    /**
     * Apply the select part of the query based on the requested fields.
     *
     * @param $query
     * @param \JosKolenberg\LaravelJory\JoryResource $joryResource
     */
    protected function applySelectsByJory($query, JoryResource $joryResource)
    {
        $fields = $this->getSelectsForRequestedFields($query, $joryResource);
        $fields = array_merge($fields, $this->getFieldsForEagerLoading($query, $joryResource));
        $fields = array_merge($fields, $this->getSelectsForChildRelations($query, $joryResource));
        $fields = array_merge($fields, $this->getSelectsForParentRelation($query));
        $query->select(array_unique($fields));
    }

    /**
     * @param $query
     * @param JoryResource $joryResource
     * @return array
     */
    protected function getSelectsForRequestedFields($query, JoryResource $joryResource)
    {
        $fields = [];

        $table = $query->getModel()->getTable();

        $configuredFields = $joryResource->getConfig()->getFields();
        foreach ($joryResource->getJory()->getFields() as $fieldName) {
            $configuredField = Arr::first($configuredFields, function (Field $configuredField) use ($fieldName) {
                return $configuredField->getField() === $fieldName;
            });

            if ($configuredField->getSelect() === null) {
                $fields[] = $table . '.' . Str::snake($fieldName);
            } else {
                $fields = array_merge($fields, $configuredField->getSelect());
            }
        }

        return $fields;
    }

    /**
     * @param JoryResource $joryResource
     * @return array
     */
    protected function getSelectsForChildRelations($query, JoryResource $joryResource)
    {
        $fields = [];

        $model = $query->getModel();

        foreach ($joryResource->getJory()->getRelations() as $relation) {
            $relationName = ResourceNameHelper::explode($relation->getName())->baseName;

            $relationQuery = $model->{$relationName}();

            $fields = array_merge($fields, $this->getSelectsForRelationQuery($model, $relationQuery));
        }

        return $fields;
    }

    /**
     * @param JoryResource $joryResource
     * @return array
     */
    protected function getFieldsForEagerLoading($query, JoryResource $joryResource)
    {
        $fields = [];

        $model = $query->getModel();

        $configuredFields = $joryResource->getConfig()->getFields();
        foreach ($joryResource->getJory()->getFields() as $fieldName) {
            $configuredField = Arr::first($configuredFields, function (Field $configuredField) use ($fieldName) {
                return $configuredField->getField() === $fieldName;
            });

            if ($configuredField->getEagerLoads() !== null) {
                foreach ($configuredField->getEagerLoads() as $eagerLoad){
                    $firstRelation = Str::before($eagerLoad, '.');

                    $relationQuery = $model->{$firstRelation}();

                    $fields = array_merge($fields, $this->getSelectsForRelationQuery($model, $relationQuery));
                }
            }
        }


        return $fields;
    }

    /**
     * @param $query
     * @return array
     */
    protected function getSelectsForParentRelation($query)
    {
        $fields = [];

        $model = $query->getModel();

        if($query instanceof HasOne){
            return [$query->getQualifiedForeignKeyName()];
        }

        if($query instanceof BelongsTo){
            return [$model->getQualifiedKeyName()];
        }

        if($query instanceof HasMany){
            return [$query->getQualifiedForeignKeyName()];
        }

        return $fields;
    }

    public function getSelectsForRelationQuery($baseModel, $relationQuery)
    {
        if($relationQuery instanceof HasOne){
            return [$baseModel->getQualifiedKeyName()];
        }

        if($relationQuery instanceof BelongsTo){
            return [$relationQuery->getQualifiedForeignKeyName()];
        }

        if($relationQuery instanceof HasMany){
            return [$baseModel->getQualifiedKeyName()];
        }

        return [];
    }
}