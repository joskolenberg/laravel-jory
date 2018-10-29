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
     * Add a field to the blueprint.
     *
     * @param $field
     * @return Field
     */
    public function field($field)
    {
        $field = new Field($field);
        if($this->fields === null){
            $this->fields = [];
        }

        $this->fields[] = $field;
        return $field;
    }

    /**
     * Get the fields in the blueprint.
     *
     * @return array|null
     */
    public function getFields()
    {
        return $this->fields;
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
}