<?php

namespace JosKolenberg\LaravelJory\Blueprint;

use JosKolenberg\Jory\Jory;
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