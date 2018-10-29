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
 * Class to validate and update a Jory object by the settings in the Blueprint.
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

        if(count($this->errors) > 0){
            throw new LaravelJoryCallException($this->errors);
        }
    }

    /**
     * Validate and update the fields in the Jory object by the settings in the Blueprint.
     */
    protected function validateFields():void
    {
        if($this->blueprint->getFields() === null) {
            // No fields specified, perform no validation
            return;
        }

        if($this->jory->getFields() === null){
            // No fields set on the jory
            // than we will update the fields with the ones to be shown by default.
            $defaultFields = [];
            foreach ($this->blueprint->getFields() as $field){
                if($field->isShownByDefault()){
                    $defaultFields[] = $field->getField();
                }
                $this->jory->setFields($defaultFields);
            }

            // No validation needed in this case, so return.
            return;
        }

        // There are fields in the Jory object and Blueprint, validate them.
        $availableFields = [];
        foreach ($this->blueprint->getFields() as $field){
            $availableFields[] = $field->getField();
        }

        foreach ($this->jory->getFields() as $joryField){
            if(!in_array($joryField, $availableFields)){
                $this->errors[] = 'Field "' . $joryField . '" not available. Did you mean "' . $this->getSuggestion($availableFields, $joryField) . '"? (Location: ' . $this->address . 'fields)';
            }
        }
    }

    /**
     * Validate filter in the Jory object by the settings in the Blueprint.
     */
    protected function validateFilter():void
    {
        if($this->blueprint->getFilters() === null || $this->jory->getFilter() === null) {
            // No filters specified in blueprint or jory, perform no validation
            return;
        }

        $this->doValidateFilter($this->blueprint->getFilters(), $this->jory->getFilter(), $this->address . 'filter');
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
        if($joryFilter instanceof GroupOrFilter){
            foreach ($joryFilter as $key => $subFilter){
                $this->doValidateFilter($blueprintFilters, $subFilter, $address . '(or).' . $key);
            }
            return;
        }
        // If it is a grouped AND filter, check subfilters recursive
        if($joryFilter instanceof GroupAndFilter){
            foreach ($joryFilter as $key => $subFilter){
                $this->doValidateFilter($blueprintFilters, $subFilter, $address . '(and).' . $key);
            }
            return;
        }

        // It is a filter on a field, do validation on field an operator
        foreach ($blueprintFilters as $blueprintFilter){
            if($blueprintFilter->getField() === $joryFilter->getField()){
                if(!in_array($joryFilter->getOperator(), $blueprintFilter->getOperators())){
                    $this->errors[] = 'Operator "' . $joryFilter->getOperator() . '" is not supported by field "' . $joryFilter->getField() . '". (Location: ' . $address . '.' . $joryFilter->getField() . ')';
                }
                return;
            }
        }

        // When we get here the field was not ound in the blueprint
        $availableFields = [];
        foreach ($blueprintFilters as $bpf){
            $availableFields[] = $bpf->getField();
        }
        $this->errors[] = 'Field "' . $joryFilter->getField() . '" is not supported for filtering. Did you mean "' . $this->getSuggestion($availableFields, $joryFilter->getField()) . '"? (Location: ' . $address . ')';
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