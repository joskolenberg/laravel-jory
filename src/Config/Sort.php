<?php

namespace JosKolenberg\LaravelJory\Config;

/**
 * Class Sort.
 *
 * Represents a sort in the config.
 */
class Sort
{
    /**
     * @var string
     */
    protected $field;

    /**
     * @var null|string
     */
    protected $description = null;

    /**
     * @var null|int
     */
    protected $defaultIndex = null;

    /**
     * @var null|int
     */
    protected $defaultOrder = 'asc';

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
        if ($this->description === null) {
            return 'Sort by the '.$this->field.' field.';
        }

        return $this->description;
    }

    /**
     * Mark this sort to be applied by default.
     *
     * @param int $index
     * @param string $order
     * @return Sort
     */
    public function default(int $index = 0, string $order = 'asc'): self
    {
        $this->defaultIndex = $index;
        $this->defaultOrder = $order;

        return $this;
    }

    /**
     * Get the index for default sorting.
     * Null means there won't be sorted on this field by default.
     *
     * @return int|null
     */
    public function getDefaultIndex(): ? int
    {
        return $this->defaultIndex;
    }

    /**
     * Get the sort order ('asc' or 'desc') if this sort needs to be applied by default.
     *
     * @return string|null
     */
    public function getDefaultOrder(): string
    {
        return $this->defaultOrder;
    }
}
