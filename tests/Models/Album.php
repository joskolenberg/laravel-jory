<?php

namespace JosKolenberg\LaravelJory\Tests\Models;

class Album extends Model
{
    protected $table = 'albums';

    protected $casts = [
        'id' => 'integer',
        'band_id' => 'integer',
    ];

    public function songs()
    {
        return $this->hasMany(Song::class);
    }

    public function band()
    {
        return $this->belongsTo(Band::class);
    }

    public function cover()
    {
        return $this->hasOne(AlbumCover::class);
    }

    public function albumCover()
    {
        return $this->hasOne(AlbumCover::class);
    }

    public function snakeCaseAlbumCover()
    {
        return $this->hasOne(AlbumCover::class);
    }
}
