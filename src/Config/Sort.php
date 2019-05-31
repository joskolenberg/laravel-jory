<?php

namespace JosKolenberg\LaravelJory\Config;

use Illuminate\Support\Str;
use JosKolenberg\LaravelJory\Helpers\CaseManager;

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
     * @var string
     */
    protected $defaultOrder = 'asc';

    /**
     * @var CaseManager
     */
    protected $case = null;

    /**
     * Sort constructor.
     *
     * @param string $field
     */
    public function __construct(string $field)
    {
        $this->field = $field;

        $this->case = app(CaseManager::class);
    }

    /**
     * Set the sort's description.
     *
     * @param string $description
     * @return $this
     */
    public function description(string $description): Sort
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
        return $this->case->toCurrent($this->field);
    }

    /**
     * Get the description.
     *
     * @return string
     */
    public function getDescription(): string
    {
        if ($this->description === null) {
            return 'Sort by the '.$this->getField().' field.';
        }

        return $this->description;
    }

    /**
     * Mark this sort to be applied by default.
     *
     * @param int $index
     * @param string $order
     * @return $this
     */
    public function default(int $index = 0, string $order = 'asc'): Sort
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
