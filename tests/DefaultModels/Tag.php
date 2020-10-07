<?php

namespace JosKolenberg\LaravelJory\Tests\DefaultModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use JosKolenberg\LaravelJory\Tests\Factories\AlbumFactory;
use JosKolenberg\LaravelJory\Tests\Factories\BandFactory;
use JosKolenberg\LaravelJory\Tests\Factories\SongFactory;
use JosKolenberg\LaravelJory\Tests\Factories\TagFactory;
use JosKolenberg\LaravelJory\Tests\Factories\TeamFactory;

class Tag extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected static function newFactory()
    {
        return new TagFactory();
    }

    public function bands()
    {
        return $this->morphedByMany(Band::class, 'taggable');
    }
}
