<?php

namespace JosKolenberg\LaravelJory\Tests\Models;

use JosKolenberg\LaravelJory\Traits\JoryTrait;

class AlbumCover extends Model
{
    use JoryTrait;

    protected $table = 'album_covers';

    public function album()
    {
        return $this->belongsTo(Album::class);
    }
}
