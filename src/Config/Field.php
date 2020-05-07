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
     * Set the fields to be selected in the query.
     *
     * @param string|array $fields
     * @return Field
     */
    public function select(...$fields): Field
    {
        $this->select = is_array($fields[0]) ? $fields[0] : $fields;

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
        if (is_string($relations)) {
            $relations = func_get_args();
        }

        $this->load = $relations;

        return $this;
    }

    /**
     * Get the field (name) in the current case.
     *
     * @return string
     */
    public function getField(): string
    {
        return $this->case->toCurrent($this->field);
    }

    /**
     * Get the field (name) as configured.
     *
     * @return string
     */
    public function getOriginalField(): string
    {
        return $this->field;
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
