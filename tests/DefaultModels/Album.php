<?php

namespace JosKolenberg\LaravelJory\Tests\DefaultModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use JosKolenberg\LaravelJory\Tests\Factories\AlbumFactory;

class Album extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected static function newFactory()
    {
        return new AlbumFactory();
    }

    public function band()
    {
        return $this->belongsTo(Band::class);
    }

    public function songs()
    {
        return $this->hasMany(Song::class);
    }
}
