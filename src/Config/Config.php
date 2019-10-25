<?php

namespace JosKolenberg\LaravelJory\Config;

use JosKolenberg\Jory\Jory;
use JosKolenberg\LaravelJory\Helpers\ResourceNameHelper;
use JosKolenberg\LaravelJory\Scopes\FilterScope;
use JosKolenberg\LaravelJory\Scopes\SortScope;
use JosKolenberg\LaravelJory\Traits\AppliesConfigToJory;
use JosKolenberg\LaravelJory\Traits\ConvertsConfigToArray;

/**
 * Class Config.
 *
 * Holds the configuration for a JoryBuilder.
 */
class Config
{
    use AppliesConfigToJory, ConvertsConfigToArray;

    /**
     * @var string
     */
    protected $modelClass = null;

    /**
     * @var array
     */
    protected $fields = [];

    /**
     * @var array
     */
    protected $filters = [];

    /**
     * @var array
     */
    protected $sorts = [];

    /**
     * @var null|int
     */
    protected $offset = null;

    /**
     * @var null|int
     */
    protected $limit = null;

    /**
     * @var array
     */
    protected $relations = [];

    /**
     * @var null|int
     */
    protected $limitDefault = null;

    /**
     * @var null|int
     */
    protected $limitMax = null;

    /**
     * @var bool
     */
    protected $explicitSelect = false;

    /**
     * Config constructor.
     *
     * We need to pass in the related model class, so we can
     * reflect to which class the defined relations models
     * belong when they are not explicitly specified.
     *
     * @param string $modelClass
     */
    public function __construct(string $modelClass)
    {
        $this->modelClass = $modelClass;
        $this->limitDefault = config('jory.limit.default');
        $this->limitMax = config('jory.limit.max');
    }

    /**
     * Add a field to the config.
     *
     * @param $field
     * @return Field
     */
    public function field($field): Field
    {
        $field = new Field($field);

        $this->fields[] = $field;

        return $field;
    }

    /**
     * Add a filter to the config.
     *
     * @param $name
     * @param FilterScope|null $scope
     * @return Filter
     */
    public function filter($name, FilterScope $scope = null): Filter
    {
        $filter = new Filter($name, $scope);

        $this->filters[] = $filter;

        return $filter;
    }

    /**
     * Add a sort to the config.
     *
     * @param $field
     * @return Sort
     */
    public function sort($field, SortScope $scope = null): Sort
    {
        $sort = new Sort($field, $scope);

        $this->sorts[] = $sort;

        return $sort;
    }

    /**
     * Set the default value for limit parameter.
     *
     * @param null|int $default
     * @return Config
     */
    public function limitDefault(?int $default): self
    {
        $this->limitDefault = $default;

        return $this;
    }

    /**
     * Set the maximum value for limit parameter.
     *
     * @param null|int $max
     * @return Config
     */
    public function limitMax(?int $max): self
    {
        $this->limitMax = $max;

        return $this;
    }

    /**
     * Add a relation to the config.
     *
     * When no joryResource is given, the method will find the related model
     * and joryResource by calling the relationMethod. If you don't want
     * this to happen you can supply the joryResource to prevent this.
     *
     * @param string $name
     * @param string $joryResource
     * @return Relation
     */
    public function relation(string $name, string $joryResource = null): Relation
    {
        $relation = new Relation($name, $this->modelClass, $joryResource ? new $joryResource : null);

        $this->relations[] = $relation;

        return $relation;
    }

    /**
     * Set the config to use explicitSelect.
     *
     * @param bool $enabled
     */
    public function explicitSelect(bool $enabled): void
    {
        $this->explicitSelect = $enabled;
    }

    /**
     * Get the fields in the config.
     *
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * Get a config filter by it's name.
     *
     * @param $name
     * @return Field|null
     */
    public function getField($name): ?Field
    {
        foreach ($this->getFields() as $field) {
            if ($field->getField() === $name) {
                return $field;
            }
        }

        return null;
    }

    /**
     * Get the filters in the config.
     *
     * @return array
     */
    public function getFilters(): array
    {
        $filters = $this->filters;

        // Add filterable fields to the array.
        foreach ($this->fields as $field) {
            if ($field->getFilter() !== null) {
                $filters[] = $field->getFilter();
            }
        }

        return $filters;
    }

    /**
     * Get a config filter by a jory filter.
     *
     * @param \JosKolenberg\Jory\Support\Filter $joryFilter
     * @return Filter|null
     */
    public function getFilter(\JosKolenberg\Jory\Support\Filter $joryFilter): ?Filter
    {
        foreach ($this->getFilters() as $filter) {
            if ($filter->getName() === $joryFilter->getField()) {
                return $filter;
            }
        }

        return null;
    }

    /**
     * Get the sorts in the config.
     *
     * @return array
     */
    public function getSorts(): array
    {
        $sorts = $this->sorts;

        // Add sortable fields to the array.
        foreach ($this->fields as $field) {
            if ($field->getSort() !== null) {
                $sorts[] = $field->getSort();
            }
        }

        return $sorts;
    }

    /**
     * Get a config sort by a jory sort.
     *
     * @param \JosKolenberg\Jory\Support\Sort $jorySort
     * @return Sort|null
     */
    public function getSort(\JosKolenberg\Jory\Support\Sort $jorySort): ?Sort
    {
        foreach ($this->getSorts() as $sort) {
            if ($sort->getField() === $jorySort->getField()) {
                return $sort;
            }
        }
    }

    /**
     * Get the default value for limit.
     *
     * @return null|int
     */
    public function getLimitDefault(): ?int
    {
        if ($this->limitDefault !== null) {
            return $this->limitDefault;
        }

        return $this->limitMax;
    }

    /**
     * Get the maximum value for limit.
     *
     * @return null|int
     */
    public function getLimitMax(): ?int
    {
        return $this->limitMax;
    }

    /**
     * Get the relations in the config.
     *
     * @return array
     */
    public function getRelations(): array
    {
        return $this->relations;
    }

    /**
     * Get a config relation by a jory relation.
     *
     * @param \JosKolenberg\Jory\Support\Relation $joryRelation
     * @return Relation
     */
    public function getRelation(\JosKolenberg\Jory\Support\Relation $joryRelation): ?Relation
    {
        foreach ($this->relations as $relation) {
            if ($relation->getName() === ResourceNameHelper::explode($joryRelation->getName())->baseName) {
                return $relation;
            }
        }

        return null;
    }

    /**
     * Does this config use explicitSelect?
     *
     * @return bool
     */
    public function hasExplicitSelect(): bool
    {
        return $this->explicitSelect;
    }

    /**
     * Convert the config to an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->configToArray($this);
    }
    /**
     * Update a Jory query object with the defaults from this Config.
     *
     * @param Jory $jory
     * @return Jory
     */
    public function applyToJory(Jory $jory): Jory
    {
        return $this->applyConfigToJory($jory, $this);
    }
}
