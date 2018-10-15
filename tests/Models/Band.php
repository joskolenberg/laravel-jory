<?php

namespace JosKolenberg\LaravelJory\Tests\Models;

use JosKolenberg\LaravelJory\Tests\JoryBuilders\BandJoryBuilder;
use JosKolenberg\LaravelJory\Traits\JoryTrait;

class Band extends Model
{
    use JoryTrait;

    protected $table = 'bands';

    public static function getJoryBuilder()
    {
        return new BandJoryBuilder();
    }

    public function people()
    {
        return $this->belongsToMany(Person::class, 'band_members');
    }

    public function albums()
    {
        return $this->hasMany(Album::class);
    }

    public function songs()
    {
        return $this->hasManyThrough(Song::class, Album::class);
    }
}
