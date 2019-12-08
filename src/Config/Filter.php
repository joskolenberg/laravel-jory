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
    protected $name;

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
     * @var FilterScope
     */
    protected $scope = null;

    /**
     * Field constructor.
     *
     * @param string $name
     * @param FilterScope|null $scope
     */
    public function __construct(string $name, FilterScope $scope = null)
    {
        $this->name = $name;
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
     * @param FilterScope $scope
     * @return $this
     */
    public function scope(FilterScope $scope = null): Filter
    {
        $this->scope = $scope;

        return $this;
    }

    /**
     * Get the filter's name in the current case.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->case->toCurrent($this->name);
    }

    /**
     * Get the field to filter on.
     *
     * This is always the name of the configured filter
     * unless a custom FilterScope is applied.
     *
     * @return string
     */
    public function getField(): string
    {
        return $this->name;
    }

    /**
     * Get the filter's optional scope class.
     *
     * @return FilterScope|null
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
     * @return null|string
     */
    public function getDescription():? string
    {
        return $this->description;
    }
}
