<?php

namespace JosKolenberg\LaravelJory\Blueprint;

/**
 * Class Field
 *
 * Represents a filter in the blueprint.
 *
 * @package JosKolenberg\LaravelJory\Blueprint
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
     * @var null
     */
    protected $description = null;

    /**
     * Field constructor.
     *
     * @param string $field
     */
    public function __construct(string $field)
    {
        $this->field = $field;
        $this->operators = config('jory.filters.operators');
    }

    /**
     * Set the filter's description.
     *
     * @param string $description
     * @return Filter
     */
    public function description(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Set the filter's available operators.
     *
     * @param array $operators
     * @return Filter
     */
    public function operators(array $operators): self
    {
        $this->operators = $operators;
        return $this;
    }

    /**
     * Get the filet's field.
     *
     * @return string
     */
    public function getField(): string
    {
        return $this->field;
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
        if($this->description === null){
            return "Not defined.";
        }
        return $this->description;
    }
}