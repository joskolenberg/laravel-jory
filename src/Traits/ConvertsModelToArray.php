<?php


namespace JosKolenberg\LaravelJory\Traits;


use Illuminate\Database\Eloquent\Model;
use JosKolenberg\LaravelJory\Helpers\ResourceNameHelper;
use JosKolenberg\LaravelJory\JoryResource;

trait ConvertsModelToArray
{
    /**
     * @var array
     */
    protected $relatedJoryResources;

    /**
     * Convert a single model to an array based on the
     * fields and relations in the Jory query.
     *
     * @param Model $model
     * @param JoryResource $joryResource
     * @return array
     */
    public function modelToArray(Model $model, JoryResource $joryResource): array
    {
        $result = $this->createModelArray($model, $joryResource);

        // Add the relations to the result
        foreach ($joryResource->getRelatedJoryResources($joryResource) as $relationName => $relatedJoryResource) {
            $relationDetails = ResourceNameHelper::explode($relationName);
            $relationAlias = $relationDetails->alias;

            // Get the related records which were fetched earlier. These are stored in the model under the full relation's name including alias
            $related = $model->joryRelations[$relationName];

            if (in_array($relationDetails->type, ['count', 'exists'])) {
                // A count query; just put the result here
                $result[$relationAlias] = $related;
                continue;
            }

            $result[$relationAlias] = $joryResource->turnRelationResultIntoArray($related, $relatedJoryResource);
        }

        return $result;
    }

    /**
     * Turn the result of a loaded relation into a result array.
     *
     * @param mixed $relatedData
     * @param JoryResource $relatedJoryResource
     * @return array|null
     */
    protected function turnRelationResultIntoArray($relatedData, JoryResource $relatedJoryResource):? array
    {
        if ($relatedData === null) {
            return null;
        }

        if ($relatedData instanceof Model) {
            // A related model is found
            return $relatedJoryResource->modelToArray($relatedData);
        }

        // Must be a related collection
        $relationResult = [];
        foreach ($relatedData as $relatedModel) {
            $relationResult[] = $relatedJoryResource->modelToArray($relatedModel);
        }

        return $relationResult;
    }

    /**
     * Get an associative array of all relations requested in the Jory query.
     *
     * The key of the array holds the name of the relation (including any
     * aliases) The values of the array are JoryResource objects which
     * in turn hold the Jory query object for the relation.
     *
     * We build this array here once so we don't have to grab for a new
     * JoryResource for each record we want to convert to an array.
     *
     * @param JoryResource $joryResource
     * @return array
     */
    protected function getRelatedJoryResources(JoryResource $joryResource): array
    {
        if (! $joryResource->relatedJoryResources) {
            $joryResource->relatedJoryResources = [];

            foreach ($joryResource->jory->getRelations() as $relation) {
                $relatedJoryResource = $joryResource->getConfig()->getRelation($relation)->getJoryResource()->fresh();

                $relatedJoryResource->setJory($relation->getJory());

                $joryResource->relatedJoryResources[$relation->getName()] = $relatedJoryResource;
            }
        }

        return $joryResource->relatedJoryResources;
    }

    /**
     * Export this model's attributes to an array. A custom attribute class
     * has precedence so we'll check on that first. Otherwise we will use
     * Eloquent's attributesToArray so we get the casting which is set
     * in the model's casts array. When the value is not present
     * (because it's not visible for the serialisation)
     * we will call for the property directly.
     *
     * @param Model $model
     * @param JoryResource $joryResource
     * @return array
     */
    protected function createModelArray(Model $model, JoryResource $joryResource): array
    {
        $jory = $joryResource->getJory();

        $result = [];

        $raw = $model->attributesToArray();

        foreach ($jory->getFields() as $field) {
            $configuredField = $joryResource->getConfig()->getField($field);

            /**
             * Check if there's a custom attribute class configured.
             */
            if($configuredField->getGetter() !== null){
                $result[$field] = $configuredField->getGetter()->get($model);

                continue;
            }

            /**
             * No custom attribute class is present, get the attribute
             * from the casted array or directly from the model.
             */
            $result[$field] = array_key_exists($configuredField->getOriginalField(), $raw)
                ? $raw[$configuredField->getOriginalField()]
                : $model->{$configuredField->getOriginalField()};
        }

        return $result;
    }
}