<?php


namespace JosKolenberg\LaravelJory\Attributes;


use Illuminate\Database\Eloquent\Model;

interface Attribute
{
    /**
     * Get the attribute for the model.
     *
     * @param Model $song
     * @return string
     */
    public function get(Model $song): string;
}