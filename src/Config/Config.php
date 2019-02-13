<?php

namespace JosKolenberg\LaravelJory\Config;

use Illuminate\Contracts\Support\Responsable;
use JosKolenberg\LaravelJory\Register\JoryBuildersRegister;

/**
 * Class Config.
 *
 * Holds the configuration for a JoryBuilder.
 */
class Config implements Responsable
{
    /**
     * @var string
     */
    protected $modelClass = null;

    /**
     * @var null|array
     */
    protected $fields = [];

    /**
     * @var array
     */
    protected $filters = null;

    /**
     * @var array
     */
    protected $sorts = null;

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
    protected $relations = null;

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
        if ($this->filters === null) {
            $this->filters = [];
        }

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
        if ($this->sorts === null) {
            $this->sorts = [];
        }

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
     * @param string $name
     * @return Relation
     */
    public function relation(string $name): Relation
    {
        // Names can be given in camel or snake case
        $name = snake_case($name);

        // Get the model class for the current builder
        $registration = app()->make(JoryBuildersRegister::class)->getRegistrationByModelClass($this->modelClass);
        $baseModelClass = $registration->getModelClass();

        // Get the related class for the relation
        $relationMethod = camel_case($name);
        $relatedClass = get_class((new $baseModelClass())->{$relationMethod}()->getRelated());

        // Add the relation
        $relation = new Relation($name, $relatedClass);
        if ($this->relations === null) {
            $this->relations = [];
        }
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
     * @return array|null
     */
    public function getFilters(): ?array
    {
        $filters = $this->filters;

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
     * @return array|null
     */
    public function getSorts(): ?array
    {
        $sorts = $this->sorts;

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
     * @return array|null
     */
    public function getRelations(): ?array
    {
        return $this->relations;
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function toResponse($request)
    {
        return response($this->toArray());
    }

    /**
     * Convert the config to an array to be shown when using OPTIONS.
     *
     * @return array
     */
    protected function toArray(): array
    {
        return [
            'fields' => $this->fieldsToArray(),
            'filters' => $this->filtersToArray(),
            'sorts' => $this->sortsToArray(),
            'limit' => [
                'default' => ($this->getLimitDefault() === null ? 'Unlimited.' : $this->getLimitDefault()),
                'max' => ($this->getLimitMax() === null ? 'Unlimited.' : $this->getLimitMax()),
            ],
            'relations' => $this->relationsToArray(),
        ];
    }

    /**
     * Turn the fields part of the config into an array.
     *
     * @return array|string
     */
    protected function fieldsToArray()
    {
        $result = [];
        foreach ($this->fields as $field) {
            $result[$field->getField()] = [
                'description' => $field->getDescription(),
                'show_by_default' => $field->isShownByDefault(),
            ];
        }

        return $result;
    }

    /**
     * Turn the filters part of the config into an array.
     *
     * @return array|string
     */
    protected function filtersToArray()
    {
        if ($this->getFilters() === null) {
            return 'Not defined.';
        }

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
     * @return array|string
     */
    protected function sortsToArray()
    {
        if ($this->getSorts() === null) {
            return 'Not defined.';
        }

        $result = [];
        foreach ($this->getSorts() as $sort) {
            $result[$sort->getField()] = [
                'description' => $sort->getDescription(),
                'default' => ($sort->getDefaultIndex() === null ? false : 'index '.$sort->getDefaultIndex().', '.$sort->getDefaultOrder()),
            ];
        }

        return $result;
    }

    /**
     * Turn the relations part of the config into an array.
     *
     * @return array|string
     */
    protected function relationsToArray()
    {
        if ($this->relations === null) {
            return 'Not defined.';
        }

        $result = [];
        foreach ($this->relations as $relation) {
            $result[$relation->getName()] = [
                'description' => $relation->getDescription(),
                'type' => $relation->getType(),
            ];
        }

        return $result;
    }
}
