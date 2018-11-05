<?php

namespace JosKolenberg\LaravelJory\Config;

/**
 * Class Field.
 *
 * Represents a field in the config.
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
     * @var null|Filter
     */
    protected $filter = null;

    /**
     * @var null|Sort
     */
    protected $sort = null;

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
     * @return \JosKolenberg\LaravelJory\Config\Field
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
        if ($this->description === null) {
            return "The ".$this->field." field.";
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

    /**
     * Mark this field to be filterable.
     *
     * @param null $callback
     * @return Field
     */
    public function filterable($callback = null): self
    {
        $this->filter = new Filter($this->field);

        if (is_callable($callback)) {
            call_user_func($callback, $this->filter);
        }

        return $this;
    }

    /**
     * Get the filter.
     *
     * @return Filter|null
     */
    public function getFilter(): ? Filter
    {
        return $this->filter;
    }

    /**
     * Mark this field to be sortable.
     *
     * @param null $callback
     * @return Field
     */
    public function sortable($callback = null): self
    {
        $this->sort = new Sort($this->field);

        if (is_callable($callback)) {
            call_user_func($callback, $this->sort);
        }

        return $this;
    }

    /**
     * Get the sort.
     *
     * @return Sort|null
     */
    public function getSort(): ? Sort
    {
        return $this->sort;
    }
}