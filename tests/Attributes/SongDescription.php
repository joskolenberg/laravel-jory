<?php


namespace JosKolenberg\LaravelJory\Tests\Attributes;


use Illuminate\Database\Eloquent\Model;
use JosKolenberg\LaravelJory\Attributes\Attribute;

class SongDescription implements Attribute
{

    /**
     * Get the attribute for the model.
     *
     * @param Model $song
     * @return mixed
     */
    public function get(Model $song)
    {
        return $song->title . ' from the ' . $song->album->name . ' album.';
    }
}