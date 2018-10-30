<?php

namespace JosKolenberg\LaravelJory\Blueprint;

use JosKolenberg\Jory\Jory;
use JosKolenberg\Jory\Support\GroupOrFilter;
use JosKolenberg\Jory\Support\GroupAndFilter;
use JosKolenberg\Jory\Contracts\FilterInterface;
use JosKolenberg\LaravelJory\Exceptions\LaravelJoryCallException;

/**
 * Class Validator
 *
 * Class to validate a Jory object by the settings in the Blueprint.
 *
 * @package JosKolenberg\LaravelJory\Blueprint
 */
class Validator
{
    /**
     * @var \JosKolenberg\LaravelJory\Blueprint\Blueprint
     */
    protected $blueprint;

    /**
     * @var \JosKolenberg\Jory\Jory
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
     * @param Blueprint $blueprint
     * @param Jory $jory
     * @param string $address
     */
    public function __construct(Blueprint $blueprint, Jory $jory, string $address = '')
    {
        $this->blueprint = $blueprint;
        $this->jory = $jory;
        $this->address = $address;
    }

    /**
     * Validate and update the Jory object by the settings in the Blueprint.
     *
     * @throws LaravelJoryCallException
     */
    public function validate(): void
    {
        $this->validateFields();
        $this->validateFilter();
        $this->validateSorts();
        $this->validateOffsetLimit();

        if (count($this->errors) > 0) {
            throw new LaravelJoryCallException($this->errors);
        }
    }

    /**
     * Validate the fields in the Jory object by the settings in the Blueprint.
     */
    protected function validateFields(): void
    {
        if ($this->blueprint->getFields() === null || $this->jory->getFields() === null) {
            // No fields specified, perform no validation
            return;
        }

        // There are fields in the Jory object and Blueprint, validate them.
        $availableFields = [];
        foreach ($this->blueprint->getFields() as $field) {
            $availableFields[] = $field->getField();
        }

        foreach ($this->jory->getFields() as $key => $joryField) {
            if (! in_array($joryField, $availableFields)) {
                $this->errors[] = 'Field "'.$joryField.'" not available. Did you mean "'.$this->getSuggestion($availableFields, $joryField).'"? (Location: '.$this->address.'fields.'.$key.')';
            }
        }
    }

    /**
     * Validate filter in the Jory object by the settings in the Blueprint.
     */
    protected function validateFilter(): void
    {
        if ($this->blueprint->getFilters() === null || $this->jory->getFilter() === null) {
            // No filters specified in blueprint or jory, perform no validation
            return;
        }

        $this->doValidateFilter($this->blueprint->getFilters(), $this->jory->getFilter(), $this->address.'filter');
    }

    /**
     * Validate Jory Filter object by the settings in the Blueprint.
     *
     * @param array $blueprintFilters
     * @param \JosKolenberg\Jory\Contracts\FilterInterface $joryFilter
     * @param string $address
     */
    protected function doValidateFilter(array $blueprintFilters, FilterInterface $joryFilter, string $address): void
    {
        // If it is a grouped OR filter, check subfilters recursive
        if ($joryFilter instanceof GroupOrFilter) {
            foreach ($joryFilter as $key => $subFilter) {
                $this->doValidateFilter($blueprintFilters, $subFilter, $address.'(or).'.$key);
            }

            return;
        }
        // If it is a grouped AND filter, check subfilters recursive
        if ($joryFilter instanceof GroupAndFilter) {
            foreach ($joryFilter as $key => $subFilter) {
                $this->doValidateFilter($blueprintFilters, $subFilter, $address.'(and).'.$key);
            }

            return;
        }

        // It is a filter on a field, do validation on field an operator
        foreach ($blueprintFilters as $blueprintFilter) {
            if ($blueprintFilter->getField() === $joryFilter->getField()) {
                if ($joryFilter->getOperator() !== null &&  ! in_array($joryFilter->getOperator(), $blueprintFilter->getOperators())) {
                    $this->errors[] = 'Operator "'.$joryFilter->getOperator().'" is not supported by field "'.$joryFilter->getField().'". (Location: '.$address.'.'.$joryFilter->getField().')';
                }

                return;
            }
        }

        // When we get here the field was not found in the blueprint
        $availableFields = [];
        foreach ($blueprintFilters as $bpf) {
            $availableFields[] = $bpf->getField();
        }
        $this->errors[] = 'Field "'.$joryFilter->getField().'" is not supported for filtering. Did you mean "'.$this->getSuggestion($availableFields, $joryFilter->getField()).'"? (Location: '.$address.')';
    }

    /**
     * Validate the sorts in the Jory object by the settings in the Blueprint.
     */
    protected function validateSorts(): void
    {
        if ($this->blueprint->getSorts() === null || $this->jory->getSorts() === null) {
            // No sorts specified in blueprint or jory, perform no validation
            return;
        }

        // There are fields in the Jory object and Blueprint, validate them.
        $availableFields = [];
        foreach ($this->blueprint->getSorts() as $sort) {
            $availableFields[] = $sort->getField();
        }

        foreach ($this->jory->getSorts() as $key => $jorySort) {
            if (! in_array($jorySort->getField(), $availableFields)) {
                $this->errors[] = 'Field "'.$jorySort->getField().'" is not supported for sorting. Did you mean "'.$this->getSuggestion($availableFields, $jorySort->getField()).'"? (Location: '.$this->address.'sorts.'.$key.')';
            }
        }
    }

    /**
     * Validate the offset and limit in the Jory object by the settings in the Blueprint.
     */
    protected function validateOffsetLimit(): void
    {
        // When setting an offset a limit is required in SQL
        if ($this->jory->getOffset() !== null && $this->jory->getLimit() === null && $this->blueprint->getLimitDefault() === null) {
            $this->errors[] = 'An offset cannot be set without a limit. (Location: '.$this->address.'offset)';
        }
        if($this->blueprint->getLimitMax() !== null){
            if ($this->jory->getLimit() > $this->blueprint->getLimitMax()) {
                $this->errors[] = 'The maximum limit for this resource is ' . $this->blueprint->getLimitMax() . ', please lower your limit or drop the limit parameter. (Location: '.$this->address.'limit)';
            }
        }
    }

    /**
     * Get the word in an array which looks the most like $value.
     *
     * @param array $array
     * @param string $value
     * @return string
     */
    protected function getSuggestion(array $array, string $value): string
    {
        $bestScore = -1;
        $bestMatch = '';

        foreach ($array as $item) {

            $lev = levenshtein($value, $item);

            if ($lev <= $bestScore || $bestScore < 0) {
                $bestMatch = $item;
                $bestScore = $lev;
            }
        }

        return $bestMatch;
    }
}