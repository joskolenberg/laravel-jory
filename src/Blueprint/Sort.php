<?php

namespace JosKolenberg\LaravelJory\Blueprint;

/**
 * Class Sort
 *
 * Represents a sort in the blueprint.
 *
 * @package JosKolenberg\LaravelJory\Blueprint
 */
class Sort
{
    /**
     * @var string
     */
    protected $field;

    /**
     * @var null
     */
    protected $description = null;

    /**
     * Sort constructor.
     *
     * @param string $field
     */
    public function __construct(string $field)
    {
        $this->field = $field;
    }

    /**
     * Set the sort's description.
     *
     * @param string $description
     * @return Sort
     */
    public function description(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Get the field.
     *
     * @return string
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * Get the description.
     *
     * @return string
     */
    public function getDescription(): string
    {
        if($this->description === null){
            return "Sort by the " . $this->field . " field.";
        }
        return $this->description;
    }
}