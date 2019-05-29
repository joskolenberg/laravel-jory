<?php

namespace JosKolenberg\LaravelJory\Config;

use Illuminate\Support\Str;
use JosKolenberg\Jory\Jory;

/**
 * Class Config.
 *
 * Holds the configuration for a JoryBuilder.
 */
class Config
{
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
     * Config constructor.
     *
     * We need to pass the related model class, so we can
     * reflect to which class the defined relations models belong.
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
     * @param $field
     * @return Filter
     */
    public function filter($field): Filter
    {
        $filter = new Filter($field);

        $this->filters[] = $filter;

        return $filter;
    }

    /**
     * Add a sort to the config.
     *
     * @param $field
     * @return Sort
     */
    public function sort($field): Sort
    {
        $sort = new Sort($field);

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
     * When no relatedClass is given, the method will find the relatedClass
     * by calling the relationMethod. If you don't want this to happen
     * you can supply the $relatedClass to prevent this.
     *
     * @param string $name
     * @param string|null $relatedClass
     * @return Relation
     */
    public function relation(string $name, string $relatedClass = null): Relation
    {
        if(!$relatedClass){
            // Get the related class for the relation
            $relationMethod = Str::camel($name);
            $relatedClass = get_class((new $this->modelClass())->{$relationMethod}()->getRelated());
        }

        // Add the relation
        $relation = new Relation($name, $relatedClass);

        $this->relations[] = $relation;

        return $relation;
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
     * Convert the config to an array to be shown when using OPTIONS.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'fields' => $this->fieldsToArray(),
            'filters' => $this->filtersToArray(),
            'sorts' => $this->sortsToArray(),
            'limit' => [
                'default' => $this->getLimitDefault(),
                'max' => $this->getLimitMax(),
            ],
            'relations' => $this->relationsToArray(),
        ];
    }

    /**
     * Turn the fields part of the config into an array.
     *
     * @return array
     */
    protected function fieldsToArray(): array
    {
        $result = [];
        foreach ($this->fields as $field) {
            $result[$field->getField()] = [
                'description' => $field->getDescription(),
                'default' => $field->isShownByDefault(),
            ];
        }

        return $result;
    }

    /**
     * Turn the filters part of the config into an array.
     *
     * @return array
     */
    protected function filtersToArray(): array
    {
        $result = [];
        foreach ($this->getFilters() as $filter) {
            $result[$filter->getField()] = [
                'description' => $filter->getDescription(),
                'operators' => $filter->getOperators(),
            ];
        }

        return $result;
    }

    /**
     * Turn the sorts part of the config into an array.
     *
     * @return array
     */
    protected function sortsToArray(): array
    {
        $result = [];
        foreach ($this->getSorts() as $sort) {
            $result[$sort->getField()] = [
                'description' => $sort->getDescription(),
                'default' => ($sort->getDefaultIndex() === null ? false : [
                    'index' => $sort->getDefaultIndex(),
                    'order' => $sort->getDefaultOrder(),
                ]),
            ];
        }

        return $result;
    }

    /**
     * Turn the relations part of the config into an array.
     *
     * @return array|string
     */
    protected function relationsToArray(): array
    {
        $result = [];
        foreach ($this->relations as $relation) {
            $result[$relation->getName()] = [
                'description' => $relation->getDescription(),
                'type' => $relation->getType(),
            ];
        }

        return $result;
    }

    public function applyToJory(Jory $jory): Jory
    {
        $this->applyFieldsToJory($jory);
        $this->applySortsToJory($jory);
        $this->applyOffsetAndLimitToJory($jory);

        return $jory;
    }

    protected function applyFieldsToJory(Jory $jory): void
    {
        if ($jory->getFields() === null) {
            // No fields set in the request, than we will update the fields
            // with the ones to be shown by default.
            $defaultFields = [];
            foreach ($this->getFields() as $field) {
                if ($field->isShownByDefault()) {
                    $defaultFields[] = $field->getField();
                }
            }
            $jory->setFields($defaultFields);
        }
    }

    protected function applySortsToJory(Jory $jory): void
    {
        // When default sorts are defined, add them to the Jory
        // When no sorts are requested, the default sorts in the builder will be applied.
        // When sorts are requested, the default sorts are applied after the requested ones.
        $defaultSorts = [];
        foreach ($this->getSorts() as $sort) {
            if ($sort->getDefaultIndex() !== null) {
                $defaultSorts[$sort->getDefaultIndex()] = new \JosKolenberg\Jory\Support\Sort($sort->getField(), $sort->getDefaultOrder());
            }
        }
        ksort($defaultSorts);
        foreach ($defaultSorts as $sort) {
            $jory->addSort($sort);
        }
    }

    protected function applyOffsetAndLimitToJory(Jory $jory): void
    {
        if (is_null($jory->getLimit()) && $this->getLimitDefault() !== null) {
            $jory->setLimit($this->getLimitDefault());
        }
    }

    public function getRelation($relationName)
    {
        foreach ($this->relations as $relation){
            if($relation->getName() === $relationName){
                return $relation;
            }
        }
    }
}
