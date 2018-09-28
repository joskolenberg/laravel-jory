<?php

namespace JosKolenberg\LaravelJory\Tests\Models;

use JosKolenberg\LaravelJory\AbstractJoryBuilder;
use JosKolenberg\LaravelJory\Tests\JoryBuilders\AlbumJoryBuilder;
use JosKolenberg\LaravelJory\Traits\JoryTrait;

class Album extends Model
{
    use JoryTrait;

    public static function jory(): AbstractJoryBuilder
    {
        return (new AlbumJoryBuilder())->onModel(static::class);
    }

    protected $table = 'albums';

    public function songs()
    {
        return $this->hasMany(Song::class);
    }
}
