<?php

namespace JosKolenberg\LaravelJory\Tests\Feature\Console\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
}
