<?php

namespace JosKolenberg\LaravelJory\Config;

use JosKolenberg\Jory\Contracts\FilterInterface;
use JosKolenberg\Jory\Jory;
use JosKolenberg\Jory\Support\GroupAndFilter;
use JosKolenberg\Jory\Support\GroupOrFilter;
use JosKolenberg\LaravelJory\Exceptions\LaravelJoryCallException;
use JosKolenberg\LaravelJory\Helpers\ResourceNameHelper;
use SimilarText\Finder;

/**
 * Class Validator.
 *
 * Class to validate a Jory object by the settings in the Config.
 */
class Validator
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Jory
     */
    protected $jory;

    /**
     * @var string
     */
    protected $address;

    /**
     * @var array
     */
    protected $errors = [];

    /**
     * Validator constructor.
     *
     * @param Config $config
     * @param Jory $jory
     * @param string $address
     */
    public function __construct(Config $config, Jory $jory, string $address = '')
    {
        $this->config = $config;
        $this->jory = $jory;
        $this->address = $address;
    }

    /**
     * Validate and update the Jory query object by the settings in the Config.
     */
    public function validate(): void
    {
        $this->validateFields();
        $this->validateFilter();
        $this->validateSorts();
        $this->validateOffsetLimit();
        $this->validateRelations();
        $this->validateSubJories();

        if (count($this->errors) > 0) {
            throw new LaravelJoryCallException($this->errors);
        }
    }

    /**
     * Validate the fields in the Jory query object by the settings in the Config.
     */
    protected function validateFields(): void
    {
        if ($this->jory->getFields() === null) {
            // No fields specified, perform no validation.
            return;
        }

        // There are fields in the Jory object, validate them.
        // The asterisk value is always allowed to select all configured fields.
        $availableFields = ['*'];
        foreach ($this->config->getFields() as $field) {
            $availableFields[] = $field->getField();
        }

        foreach ($this->jory->getFields() as $joryField) {
            if (!in_array($joryField, $availableFields)) {
                $this->errors[] = 'Field "' . $joryField . '" is not available, ' . $this->getSuggestion($availableFields, $joryField) . ' (Location: ' . $this->address . 'fields.' . $joryField . ')';
            }
        }
    }

    /**
     * Validate filter in the Jory query object by the settings in the Config.
     */
    protected function validateFilter(): void
    {
        if ($this->jory->getFilter() === null) {
            // No filter specified, perform no validation.
            return;
        }

        $this->doValidateFilter($this->jory->getFilter(), $this->address . 'filter');
    }

    /**
     * Validate Jory Filter object by the settings in the Config.
     *
     * @param FilterInterface $joryFilter
     * @param string $address
     */
    protected function doValidateFilter(FilterInterface $joryFilter, string $address): void
    {
        // If it is a grouped OR filter, check subfilters recursive
        if ($joryFilter instanceof GroupOrFilter) {
            foreach ($joryFilter as $key => $subFilter) {
                $this->doValidateFilter($subFilter, $address . '(or).' . $key);
            }

            return;
        }
        // If it is a grouped AND filter, check subfilters recursive
        if ($joryFilter instanceof GroupAndFilter) {
            foreach ($joryFilter as $key => $subFilter) {
                $this->doValidateFilter($subFilter, $address . '(and).' . $key);
            }

            return;
        }

        // It is a filter on a field, do validation on field an operator
        $configFilter = $this->config->getFilter($joryFilter);
        if($configFilter){
            if ($joryFilter->getOperator() !== null && !in_array($joryFilter->getOperator(), $configFilter->getOperators())) {
                $this->errors[] = 'Operator "' . $joryFilter->getOperator() . '" is not available for field "' . $joryFilter->getField() . '". (Location: ' . $address . '(' . $joryFilter->getField() . '))';
            }
            return;
        }

        // When we get here the field was not found in the config
        $availableFields = [];
        foreach ($this->config->getFilters() as $bpf) {
            $availableFields[] = $bpf->getName();
        }
        $this->errors[] = 'Field "' . $joryFilter->getField() . '" is not available for filtering, ' . $this->getSuggestion($availableFields, $joryFilter->getField()) . ' (Location: ' . $address . '(' . $joryFilter->getField() . '))';
    }

    /**
     * Validate the sorts in the Jory query object by the settings in the Config.
     */
    protected function validateSorts(): void
    {
        $availableFields = [];
        foreach ($this->config->getSorts() as $sort) {
            $availableFields[] = $sort->getName();
        }

        foreach ($this->jory->getSorts() as $jorySort) {
            if (!in_array($jorySort->getField(), $availableFields)) {
                $this->errors[] = 'Field "' . $jorySort->getField() . '" is not available for sorting, ' . $this->getSuggestion($availableFields, $jorySort->getField()) . ' (Location: ' . $this->address . 'sorts.' . $jorySort->getField() . ')';
            }
        }
    }

    /**
     * Validate the offset and limit in the Jory query object by the settings in the Config.
     */
    protected function validateOffsetLimit(): void
    {
        // When setting an offset a limit is required in SQL
        if ($this->jory->getOffset() !== null && $this->jory->getLimit() === null && $this->config->getLimitDefault() === null) {
            $this->errors[] = 'An offset cannot be set without a limit. (Location: ' . $this->address . 'offset)';
        }
        if ($this->config->getLimitMax() !== null) {
            if ($this->jory->getLimit() > $this->config->getLimitMax()) {
                $this->errors[] = 'The maximum limit for this resource is ' . $this->config->getLimitMax() . ', please lower your limit or drop the limit parameter. (Location: ' . $this->address . 'limit)';
            }
        }
    }

    /**
     * Validate the relations in the Jory query object by the settings in the Config.
     */
    protected function validateRelations(): void
    {
        $availableRelations = [];
        foreach ($this->config->getRelations() as $relation) {
            $availableRelations[] = $relation->getName();
        }

        foreach ($this->jory->getRelations() as $joryRelation) {
            $relationName = ResourceNameHelper::explode($joryRelation->getName())->baseName;

            if (!in_array($relationName, $availableRelations)) {
                $this->errors[] = 'Relation "' . $relationName . '" is not available, ' . $this->getSuggestion($availableRelations, $relationName) . ' (Location: ' . $this->address . 'relations.' . $relationName . ')';
            }
        }
    }

    /**
     * Validate the "subJories" in the relations of the Jory query.
     */
    protected function validateSubJories(): void
    {
        foreach ($this->jory->getRelations() as $joryRelation) {
            $relation = $relatedModelClass = $this->config
                ->getRelation($joryRelation);

            /**
             * If the relation could not be found, there will already be
             * an error by validateRelations, no need to check further here.
             */
            if ($relatedModelClass === null) {
                break;
            }

            $relatedJoryResource = $relation->getJoryResource();

            try {
                (new self($relatedJoryResource->getConfig(), $joryRelation->getJory(), ($this->address ? $this->address . '.' : '') . $joryRelation->getName() . '.'))->validate();
            } catch (LaravelJoryCallException $e) {
                $this->errors = array_merge($this->errors, $e->getErrors());
            }
        }
    }

    /**
     * Get the 'Did you mean?' line for the best match in an array of strings.
     *
     * @param array $array
     * @param string $value
     * @return string
     */
    protected function getSuggestion(array $array, string $value): string
    {
        $bestMatch = (new Finder($value, $array))->threshold(4)->first();

        return $bestMatch ? 'did you mean "' . $bestMatch . '"?' : 'no suggestions found.';
    }
}
