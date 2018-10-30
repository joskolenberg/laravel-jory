<?php

namespace JosKolenberg\LaravelJory\Blueprint;

/**
 * Class Field
 *
 * Represents a field in the blueprint.
 *
 * @package JosKolenberg\LaravelJory\Blueprint
 */
class Field
{
    /**
     * @var string
     */
    protected $field;

    /**
     * @var bool
     */
    protected $showByDefault = true;

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
    }

    /**
     * Set the field to be hidden by default.
     *
     * @return Field
     */
    public function hideByDefault(): self
    {
        $this->showByDefault = false;
        return $this;
    }

    /**
     * Set the fields description.
     *
     * @param string $description
     * @return \JosKolenberg\LaravelJory\Blueprint\Field
     */
    public function description(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Get the field (name).
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
            return "The " . $this->field . " field.";
        }
        return $this->description;
    }

    /**
     * Tell if this field should be shown by default.
     *
     * @return bool
     */
    public function isShownByDefault(): bool
    {
        return $this->showByDefault;
    }

}