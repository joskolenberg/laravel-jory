<?php

namespace JosKolenberg\LaravelJory\Tests\DefaultModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use JosKolenberg\LaravelJory\Tests\Factories\SongFactory;

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
