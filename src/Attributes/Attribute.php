<?php


namespace JosKolenberg\LaravelJory\Attributes;


use Illuminate\Database\Eloquent\Model;

interface Attribute
{
    /**
     * Get the attribute for the model.
     *
     * @param Model $model
     * @return mixed
     */
    public function get(Model $model);
}