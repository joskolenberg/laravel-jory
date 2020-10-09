<?php

namespace JosKolenberg\LaravelJory\Tests\DefaultModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use JosKolenberg\LaravelJory\Tests\Factories\BandFactory;

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
}
