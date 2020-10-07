<?php

namespace JosKolenberg\LaravelJory\Tests\DefaultModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use JosKolenberg\LaravelJory\Tests\Factories\AlbumFactory;
use JosKolenberg\LaravelJory\Tests\Factories\BandFactory;
use JosKolenberg\LaravelJory\Tests\Factories\SongFactory;
use JosKolenberg\LaravelJory\Tests\Factories\TeamFactory;

class Song extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected static function newFactory()
    {
        return new SongFactory();
    }

    public function album()
    {
        return $this->belongsTo(Album::class);
    }
}
