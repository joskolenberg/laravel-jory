<?php

namespace JosKolenberg\LaravelJory\Tests\DefaultModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use JosKolenberg\LaravelJory\Tests\Factories\AlbumFactory;
use JosKolenberg\LaravelJory\Tests\Factories\BandFactory;
use JosKolenberg\LaravelJory\Tests\Factories\ImageFactory;
use JosKolenberg\LaravelJory\Tests\Factories\SongFactory;
use JosKolenberg\LaravelJory\Tests\Factories\TeamFactory;

class Image extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected static function newFactory()
    {
        return new ImageFactory();
    }
}
