<?php

namespace JosKolenberg\LaravelJory\Config;

use JosKolenberg\Jory\Jory;
use JosKolenberg\Jory\Support\GroupOrFilter;
use JosKolenberg\Jory\Support\GroupAndFilter;
use JosKolenberg\Jory\Contracts\FilterInterface;
use JosKolenberg\LaravelJory\Exceptions\LaravelJoryCallException;

/**
 * Class Validator.
 *
 * Class to validate a Jory object by the settings in the Config.
 */
class Validator
{
    /**
     * @var \JosKolenberg\LaravelJory\Config\Config
     */
    protected $config;

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
     * Validate and update the Jory object by the settings in the Config.
     *
     * @throws LaravelJoryCallException
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
     * Validate the fields in the Jory object by the settings in the Config.
     */
    protected function validateFields(): void
    {
        if ($this->config->getFields() === null || $this->jory->getFields() === null) {
            // No fields specified, perform no validation
            return;
        }

        // There are fields in the Jory object and Config, validate them.
        $availableFields = [];
        foreach ($this->config->getFields() as $field) {
            $availableFields[] = $field->getField();
        }

        foreach ($this->jory->getFields() as $joryField) {
            if (! in_array($joryField, $availableFields)) {
                $this->errors[] = 'Field "'.$joryField.'" not available. Did you mean "'.$this->getSuggestion($availableFields, $joryField).'"? (Location: '.$this->address.'fields.'.$joryField.')';
            }
        }
    }

    /**
     * Validate filter in the Jory object by the settings in the Config.
     */
    protected function validateFilter(): void
    {
        if ($this->config->getFilters() === null || $this->jory->getFilter() === null) {
            // No filters specified in config or jory, perform no validation
            return;
        }

        $this->doValidateFilter($this->config->getFilters(), $this->jory->getFilter(), $this->address.'filter');
    }

    /**
     * Validate Jory Filter object by the settings in the Config.
     *
     * @param array $configFilters
     * @param \JosKolenberg\Jory\Contracts\FilterInterface $joryFilter
     * @param string $address
     */
    protected function doValidateFilter(array $configFilters, FilterInterface $joryFilter, string $address): void
    {
        // If it is a grouped OR filter, check subfilters recursive
        if ($joryFilter instanceof GroupOrFilter) {
            foreach ($joryFilter as $key => $subFilter) {
                $this->doValidateFilter($configFilters, $subFilter, $address.'(or).'.$key);
            }

            return;
        }
        // If it is a grouped AND filter, check subfilters recursive
        if ($joryFilter instanceof GroupAndFilter) {
            foreach ($joryFilter as $key => $subFilter) {
                $this->doValidateFilter($configFilters, $subFilter, $address.'(and).'.$key);
            }

            return;
        }

        // It is a filter on a field, do validation on field an operator
        foreach ($configFilters as $configFilter) {
            if ($configFilter->getField() === $joryFilter->getField()) {
                if ($joryFilter->getOperator() !== null && ! in_array($joryFilter->getOperator(), $configFilter->getOperators())) {
                    $this->errors[] = 'Operator "'.$joryFilter->getOperator().'" is not available for field "'.$joryFilter->getField().'". (Location: '.$address.'('.$joryFilter->getField().'))';
                }

                return;
            }
        }

        // When we get here the field was not found in the config
        $availableFields = [];
        foreach ($configFilters as $bpf) {
            $availableFields[] = $bpf->getField();
        }
        $this->errors[] = 'Field "'.$joryFilter->getField().'" is not available for filtering. Did you mean "'.$this->getSuggestion($availableFields, $joryFilter->getField()).'"? (Location: '.$address.'('.$joryFilter->getField().'))';
    }

    /**
     * Validate the sorts in the Jory object by the settings in the Config.
     */
    protected function validateSorts(): void
    {
        if ($this->config->getSorts() === null || $this->jory->getSorts() === null) {
            // No sorts specified in config or jory, perform no validation
            return;
        }

        // There are fields in the Jory object and Config, validate them.
        $availableFields = [];
        foreach ($this->config->getSorts() as $sort) {
            $availableFields[] = $sort->getField();
        }

        foreach ($this->jory->getSorts() as $jorySort) {
            if (! in_array($jorySort->getField(), $availableFields)) {
                $this->errors[] = 'Field "'.$jorySort->getField().'" is not available for sorting. Did you mean "'.$this->getSuggestion($availableFields, $jorySort->getField()).'"? (Location: '.$this->address.'sorts.'.$jorySort->getField().')';
            }
        }
    }

    /**
     * Validate the offset and limit in the Jory object by the settings in the Config.
     */
    protected function validateOffsetLimit(): void
    {
        // When setting an offset a limit is required in SQL
        if ($this->jory->getOffset() !== null && $this->jory->getLimit() === null && $this->config->getLimitDefault() === null) {
            $this->errors[] = 'An offset cannot be set without a limit. (Location: '.$this->address.'offset)';
        }
        if ($this->config->getLimitMax() !== null) {
            if ($this->jory->getLimit() > $this->config->getLimitMax()) {
                $this->errors[] = 'The maximum limit for this resource is '.$this->config->getLimitMax().', please lower your limit or drop the limit parameter. (Location: '.$this->address.'limit)';
            }
        }
    }

    /**
     * Validate the relations in the Jory object by the settings in the Config.
     */
    protected function validateRelations(): void
    {
        if ($this->config->getRelations() === null) {
            // No relations specified in config, perform no validation
            return;
        }

        $availableRelations = [];
        foreach ($this->config->getRelations() as $relation) {
            $availableRelations[] = $relation->getName();
        }

        foreach ($this->jory->getRelations() as $joryRelation) {
            if (! in_array($joryRelation->getName(), $availableRelations)) {
                $this->errors[] = 'Relation "'.$joryRelation->getName().'" is not available. Did you mean "'.$this->getSuggestion($availableRelations, $joryRelation->getName()).'"? (Location: '.$this->address.'relations.'.$joryRelation->getName().')';
            }
        }
    }

    protected function validateSubJories(): void
    {
        if ($this->config->getRelations() === null) {
            // No relations specified in config, perform no validation
            return;
        }

        foreach ($this->jory->getRelations() as $joryRelation) {
            $relatedConfig = null;
            foreach ($this->config->getRelations() as $configRelation) {
                if ($joryRelation->getName() === $configRelation->getName()) {
                    $relatedModelClass = $configRelation->getModelClass();
                    $relatedConfig = $relatedModelClass::getJoryBuilder()->getConfig();
                }
            }
            if ($relatedConfig === null) {
                break;
            }

            try {
                (new self($relatedConfig, $joryRelation->getJory(), ($this->address ? $this->address.'.' : '').$joryRelation->getName().'.'))->validate();
            } catch (LaravelJoryCallException $e) {
                $this->errors = array_merge($this->errors, $e->getErrors());
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