<?php

namespace JosKolenberg\LaravelJory\Config;

use JosKolenberg\LaravelJory\Helpers\CaseManager;
use JosKolenberg\LaravelJory\Scopes\FilterScope;

/**
 * Class Filter.
 *
 * Represents a filter in the config.
 */
class Filter
{
    /**
     * @var string
     */
    protected $field;

    /**
     * @var array
     */
    protected $operators = [];

    /**
     * @var null|string
     */
    protected $description = null;

    /**
     * @var CaseManager
     */
    protected $case = null;

    /**
     * @var \JosKolenberg\LaravelJory\Scopes\FilterScope
     */
    protected $scope = null;

    /**
     * Field constructor.
     *
     * @param string $field
     * @param \JosKolenberg\LaravelJory\Scopes\FilterScope|null $scope
     */
    public function __construct(string $field, FilterScope $scope = null)
    {
        $this->field = $field;
        $this->scope = $scope;
        $this->operators = config('jory.filters.operators');

        $this->case = app(CaseManager::class);
    }

    /**
     * Set the filter's description.
     *
     * @param string $description
     * @return $this
     */
    public function description(string $description): Filter
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Set the filter's available operators.
     *
     * @param array $operators
     * @return $this
     */
    public function operators(array $operators): Filter
    {
        $this->operators = $operators;

        return $this;
    }

    /**
     * Set the filter's scope class.
     *
     * @param \JosKolenberg\LaravelJory\Scopes\FilterScope $scope
     * @return $this
     */
    public function scope(FilterScope $scope = null): Filter
    {
        $this->scope = $scope;

        return $this;
    }

    /**
     * Get the filter's field.
     *
     * @return string
     */
    public function getField(): string
    {
        return $this->case->toCurrent($this->field);
    }

    /**
     * Get the filter's optional scope class.
     *
     * @return \JosKolenberg\LaravelJory\Scopes\FilterScope|null
     */
    public function getScope():? FilterScope
    {
        return $this->scope;
    }

    /**
     * Get the available operators.
     *
     * @return array
     */
    public function getOperators(): array
    {
        return $this->operators;
    }

    /**
     * Get the description.
     *
     * @return string
     */
    public function getDescription(): string
    {
        if ($this->description === null) {
            return 'Filter on the ' . $this->getField() . ' field.';
        }

        return $this->description;
    }
}
