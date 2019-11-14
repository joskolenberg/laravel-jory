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
     * @return string
     */
    public function get(Model $song): string
    {
        return $song->title . ' from the ' . $song->album->name . ' album.';
    }
}