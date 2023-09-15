<?php

namespace JosKolenberg\LaravelJory\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Str;
use JosKolenberg\LaravelJory\JoryResource;

trait HandlesJorySelects
{
    /**
     * Apply the select part of the query.
     *
     * @param $builder
     * @param JoryResource $joryResource
     */
    public function applySelects($builder, JoryResource $joryResource): void
    {
        if (! $joryResource->getConfig()->hasExplicitSelect()) {
            $this->applyDefaultSelect($builder);

            return;
        }

        $this->applySelectsByJory($builder, $joryResource);
    }

    /**
     * Apply the default way of selecting columns.
     *
     * @param $builder
     * @return void
     */
    public function applyDefaultSelect($builder): void
    {
        $table = $builder->getModel()->getTable();
        $builder->select($table.'.*');
    }

    /**
     * Apply the select part of the query based on the requested fields and relations.
     *
     * @param $builder
     * @param JoryResource $joryResource
     */
    public function applySelectsByJory($builder, JoryResource $joryResource): void
    {
        $fields = $this->getSelectsForRequestedFields($builder, $joryResource);
        $fields = array_merge($fields, $this->getFieldsForEagerLoading($builder, $joryResource));
        $fields = array_merge($fields, $this->getSelectsForChildRelations($builder, $joryResource));
        $fields = array_merge($fields, $this->getSelectsForParentRelation($builder));

        $fields = array_unique($fields);

        if(count($fields) === 0){
            $fields[] = $builder->getModel()->getQualifiedKeyName();
        }

        $builder->select($fields);
    }

    /**
     * Get the columns to select in the query based on the requested fields.
     *
     * @param $builder
     * @param JoryResource $joryResource
     * @return array
     */
    public function getSelectsForRequestedFields($builder, JoryResource $joryResource): array
    {
        $fields = [];

        $table = $builder->getModel()->getTable();

        foreach ($joryResource->getJory()->getFields() as $fieldName) {
            $configuredField = $joryResource->getConfig()->getField($fieldName);

            if ($configuredField->getSelect() === null) {
                $fields[] = $table . '.' . $configuredField->getOriginalField();
            } else {
                $fields = array_merge($fields, $configuredField->getSelect());
            }
        }

        return $fields;
    }

    /**
     * Get the columns to be selected in order to be able to retrieve the requested relations.
     *
     * E.g. If an Album is requested with the Songs relation we need the
     * album.id column in order to get the songs from the database.
     *
     * @param JoryResource $joryResource
     * @return array
     */
    public function getSelectsForChildRelations($builder, JoryResource $joryResource): array
    {
        $fields = [];

        $model = $builder->getModel();

        foreach ($joryResource->getJory()->getRelations() as $relation) {
            $configuredRelation = $joryResource->getConfig()->getRelation($relation);

            $relationQuery = $model->{$configuredRelation->getOriginalName()}();

            $fields = array_merge($fields, $this->getSelectsForChildRelationQuery($model, $relationQuery));
        }

        return $fields;
    }

    /**
     * Get the columns to be selected in order to perform the any eager loading which has to be done.
     *
     * E.g. If an Album is requested with the AllSongsString custom attribute which has the Songs relation
     * set to be eager loaded, we need the album.id column in order to get the songs from the database.
     *
     * @param JoryResource $joryResource
     * @return array
     */
    public function getFieldsForEagerLoading($builder, JoryResource $joryResource): array
    {
        $fields = [];

        $model = $builder->getModel();

        foreach ($joryResource->getJory()->getFields() as $fieldName) {
            $configuredField = $joryResource->getConfig()->getField($fieldName);

            if ($configuredField->getEagerLoads() !== null) {
                foreach ($configuredField->getEagerLoads() as $eagerLoad){
                    $firstRelation = Str::before($eagerLoad, '.');

                    $relationQuery = $model->{$firstRelation}();

                    $fields = array_merge($fields, $this->getSelectsForChildRelationQuery($model, $relationQuery));
                }
            }
        }


        return $fields;
    }

    /**
     * Get the columns to be selected in order to be able to bind the relation to the parent model.
     *
     * E.g. If an Album is requested with the Songs relation, we need the
     * songs.album_id column in order to get the songs from the database.
     *
     * @param $builder
     * @return array
     */
    public function getSelectsForParentRelation($builder): array
    {
        if($builder instanceof HasOne){
            return [$builder->getQualifiedForeignKeyName()];
        }

        if($builder instanceof BelongsTo){
            return [$builder->getModel()->getQualifiedKeyName()];
        }

        if($builder instanceof HasMany){
            return [$builder->getQualifiedForeignKeyName()];
        }

        // BelongsToMany, HasManyThrough, HasOneThrough, MorphToMany and MorpedByMany don't require any fields

        if($builder instanceof MorphOne){
            return [$builder->getQualifiedForeignKeyName()];
        }

        if($builder instanceof MorphMany){
            return [$builder->getQualifiedForeignKeyName()];
        }

        return [];
    }

    /**
     * Get the columns to be selected in order to be able to retrieve a requested relation.
     *
     * E.g. If an Album is requested with the Songs relation we need the
     * album.id column in order to get the songs from the database.
     *
     * In this example the $baseModel would be an album instance and
     * the $relationQuery the HasMany query to retrieve the songs.
     *
     * @param $baseModel
     * @param $relationQuery
     * @return array
     */
    public function getSelectsForChildRelationQuery($baseModel, $relationQuery): array
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

        if($relationQuery instanceof BelongsToMany){
            return [$baseModel->getQualifiedKeyName()];
        }

        if($relationQuery instanceof HasManyThrough){
            return [$baseModel->getQualifiedKeyName()];
        }

        // HasOneThrough extends HasManyThrough, so that action is already taken care of

        if($relationQuery instanceof MorphOne){
            return [$baseModel->getQualifiedKeyName()];
        }

        if($relationQuery instanceof MorphMany){
            return [$baseModel->getQualifiedKeyName()];
        }

        // MorphToMany extends BelongsToMany, so that action is already taken care of

        // MorpedByMany uses a MorphToMany under the hood, so that action is already taken care of

        return [];
    }
}