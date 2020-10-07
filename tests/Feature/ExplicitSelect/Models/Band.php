<?php

namespace JosKolenberg\LaravelJory\Tests\Feature\ExplicitSelect\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use JosKolenberg\LaravelJory\Tests\DefaultModels\Album;
use JosKolenberg\LaravelJory\Tests\DefaultModels\Image;
use JosKolenberg\LaravelJory\Tests\DefaultModels\Musician;
use JosKolenberg\LaravelJory\Tests\DefaultModels\Song;
use JosKolenberg\LaravelJory\Tests\DefaultModels\Tag;
use JosKolenberg\LaravelJory\Tests\Factories\BandFactory;
use JosKolenberg\LaravelJory\Tests\Factories\TeamFactory;

class Band extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected static function newFactory()
    {
        return new BandFactory();
    }

    public function musicians()
    {
        return $this->belongsToMany(Musician::class, 'band_members');
    }

    public function albums()
    {
        return $this->hasMany(Album::class);
    }

    public function songs()
    {
        return $this->hasManyThrough(Song::class, Album::class);
    }

    public function firstSong()
    {
        return $this->hasOneThrough(Song::class, Album::class);
    }

    public function firstImage()
    {
        return $this->morphOne(Image::class, 'imageable');
    }

    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
}
