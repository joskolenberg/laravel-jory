<?php


namespace JosKolenberg\LaravelJory\Attributes;


use Illuminate\Database\Eloquent\Model;

interface Attribute
{
    /**
     * Get the attribute for the model.
     *
     * @param Model $model
     * @return string
     */
    public function get(Model $model): string;
}