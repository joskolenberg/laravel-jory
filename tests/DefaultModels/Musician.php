<?php

namespace JosKolenberg\LaravelJory\Tests\DefaultModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use JosKolenberg\LaravelJory\Tests\Factories\BandFactory;
use JosKolenberg\LaravelJory\Tests\Factories\MusicianFactory;
use JosKolenberg\LaravelJory\Tests\Factories\TeamFactory;

class Musician extends Model
{
    use HasFactory;

    protected $table = 'musicians';

    public $timestamps = false;

    protected static function newFactory()
    {
        return new MusicianFactory();
    }

    public function bands()
    {
        return $this->belongsToMany(Band::class, 'band_members');
    }
}
