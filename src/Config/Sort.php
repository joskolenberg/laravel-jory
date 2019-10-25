<?php

namespace JosKolenberg\LaravelJory\Config;

use JosKolenberg\LaravelJory\Helpers\CaseManager;
use JosKolenberg\LaravelJory\Scopes\SortScope;

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
    protected $name;

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
     * @var SortScope
     */
    protected $scope = null;

    /**
     * Sort constructor.
     *
     * @param string $name
     * @param SortScope $scope
     */
    public function __construct(string $name, SortScope $scope = null)
    {
        $this->name = $name;
        $this->scope = $scope;

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
     * Set the sort's scope class.
     *
     * @param SortScope $scope
     * @return $this
     */
    public function scope(SortScope $scope = null): Sort
    {
        $this->scope = $scope;

        return $this;
    }

    /**
     * Get the field.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->case->toCurrent($this->name);
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

    /**
     * Get the sort's optional scope class.
     *
     * @return SortScope|null
     */
    public function getScope():? SortScope
    {
        return $this->scope;
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
