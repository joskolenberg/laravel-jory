<?php

namespace JosKolenberg\LaravelJory\Config;

use JosKolenberg\LaravelJory\Attributes\Attribute;
use JosKolenberg\LaravelJory\Helpers\CaseManager;

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
     * @var null|string
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
     * @var CaseManager
     */
    protected $case = null;

    /**
     * @var null|array
     */
    protected $select = null;

    /**
     * @var null|array
     */
    protected $load = null;

    /**
     * @var Attribute
     */
    private $getter;

    /**
     * Field constructor.
     *
     * @param string $field
     * @param Attribute|null $getter
     */
    public function __construct(string $field, Attribute $getter = null)
    {
        $this->field = $field;
        $this->getter = $getter;

        $this->case = app(CaseManager::class);
    }

    /**
     * Set the field to be hidden by default.
     *
     * @return $this
     */
    public function hideByDefault(): Field
    {
        $this->showByDefault = false;

        return $this;
    }

    /**
     * Set the fields description.
     *
     * @param string $description
     * @return $this
     */
    public function description(string $description): Field
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Set the fields to be selected in the query.
     *
     * @param mixed $fields
     * @return Field
     */
    public function select($fields): Field
    {
        $this->select = is_array($fields) ? $fields : func_get_args();

        return $this;
    }

    /**
     * Tell the query to select no fields in the query when this field is requested.
     *
     * @return Field
     */
    public function noSelect(): Field
    {
        $this->select([]);

        return $this;
    }

    /**
     * Set the relations to be loaded for this field.
     *
     * @param mixed $relations
     * @return Field
     */
    public function load($relations): Field
    {
        $relations = is_array($relations) ? $relations : func_get_args();

        $this->load = array_map('Illuminate\Support\Str::camel', $relations);

        return $this;
    }

    /**
     * Get the field (name).
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
     * @return null|string
     */
    public function getDescription():? string
    {
        return $this->description;
    }

    /**
     * Get the fields to be selected in the query.
     *
     * @return null|array
     */
    public function getSelect():? array
    {
        return $this->select;
    }

    /**
     * Get the relations to be loaded for this field.
     *
     * @return null|array
     */
    public function getEagerLoads():? array
    {
        return $this->load;
    }

    /**
     * Get the optional custom getter instance.
     *
     * @return null|Attribute
     */
    public function getGetter():? Attribute
    {
        return $this->getter;
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
     * @param callable|null $callback
     * @return $this
     */
    public function filterable($callback = null): Field
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
    public function getFilter(): ?Filter
    {
        return $this->filter;
    }

    /**
     * Mark this field to be sortable.
     *
     * @param callable|null $callback
     * @return $this
     */
    public function sortable($callback = null): Field
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
    public function getSort(): ?Sort
    {
        return $this->sort;
    }
}
