<?php

namespace JosKolenberg\LaravelJory\Tests\Models\SubFolder;

use JosKolenberg\LaravelJory\Tests\Models\AlbumCover;
use JosKolenberg\LaravelJory\Tests\Models\Band;
use JosKolenberg\LaravelJory\Tests\Models\Model;
use JosKolenberg\LaravelJory\Tests\Models\Song;
use JosKolenberg\LaravelJory\Tests\Models\Tag;

class Album extends Model
{
    protected $table = 'albums';

    protected $casts = [
        'id' => 'integer',
        'band_id' => 'integer',
        'release_date' => 'datetime',
    ];

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function songs()
    {
        return $this->hasMany(Song::class);
    }

    public function customSongs1()
    {
        return $this->songs();
    }

    public function customSongs2()
    {
        return $this->songs();
    }

    public function customSongs3    ()
    {
        return $this->songs();
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

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function getCoverImageAttribute()
    {
        return $this->cover->image;
    }

    public function getTitlesStringAttribute()
    {
        return implode(', ', $this->songs->pluck('title')->toArray());
    }

    public function getTagNamesStringAttribute()
    {
        return implode(', ', $this->tags->pluck('name')->toArray());
    }
}
