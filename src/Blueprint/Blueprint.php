<?php

namespace JosKolenberg\LaravelJory\Blueprint;

use Illuminate\Contracts\Support\Responsable;

/**
 * Class Blueprint
 *
 * @package JosKolenberg\LaravelJory\Blueprint
 */
class Blueprint implements Responsable
{
    /**
     * @var null|array
     */
    protected $fields = null;

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
    protected $relations = [];

    /**
     * Add a field to the blueprint.
     *
     * @param $field
     * @return Field
     */
    public function field($field): Field
    {
        $field = new Field($field);
        if($this->fields === null){
            $this->fields = [];
        }

        $this->fields[] = $field;
        return $field;
    }

    /**
     * Add a filter to the blueprint.
     *
     * @param $field
     * @return Filter
     */
    public function filter($field): Filter
    {
        $filter = new Filter($field);
        if($this->filters === null){
            $this->filters = [];
        }

        $this->filters[] = $filter;
        return $filter;
    }

    /**
     * Add a sort to the blueprint.
     *
     * @param $field
     * @return Sort
     */
    public function sort($field): Sort
    {
        $sort = new Sort($field);
        if($this->sorts === null){
            $this->sorts = [];
        }

        $this->sorts[] = $sort;
        return $sort;
    }

    /**
     * Get the fields in the blueprint.
     *
     * @return array|null
     */
    public function getFields():? array
    {
        return $this->fields;
    }

    /**
     * Get the filters in the blueprint.
     *
     * @return array|null
     */
    public function getFilters():? array
    {
        return $this->filters;
    }

    /**
     * Get the sorts in the blueprint.
     *
     * @return array|null
     */
    public function getSorts():? array
    {
        return $this->sorts;
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
     * Convert the blueprint to an array to be shown when using OPTIONS.
     *
     * @return array
     */
    protected function toArray(): array
    {
        return [
            'fields' => $this->fieldsToArray(),
            'filters' => $this->filtersToArray(),
            'sorts' => $this->sortsToArray(),
        ];
    }

    /**
     * Turn the fields part of the blueprint into an array.
     *
     * @return array|string
     */
    protected function fieldsToArray()
    {
        if($this->fields === null){
            return 'Not defined.';
        }

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
     * Turn the filters part of the blueprint into an array.
     *
     * @return array|string
     */
    protected function filtersToArray()
    {
        if($this->filters === null){
            return 'Not defined.';
        }

        $result = [];
        foreach ($this->filters as $filter) {
            $result[$filter->getField()] = [
                'description' => $filter->getDescription(),
                'operators' => $filter->getOperators(),
            ];
        }

        return $result;

    }

    /**
     * Turn the sorts part of the blueprint into an array.
     *
     * @return array|string
     */
    protected function sortsToArray()
    {
        if($this->sorts === null){
            return 'Not defined.';
        }

        $result = [];
        foreach ($this->sorts as $sort) {
            $result[$sort->getField()] = [
                'description' => $sort->getDescription(),
            ];
        }

        return $result;

    }
}