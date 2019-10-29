<?php

namespace JosKolenberg\LaravelJory\Tests\Models;

use JosKolenberg\LaravelJory\Tests\Models\SubFolder\Album;

class Band extends Model
{
    protected $table = 'bands';

    protected $hidden = [
        'pivot',
    ];

    protected $casts = [
        'id' => 'integer',
        'year_start' => 'integer',
        'year_end' => 'integer',
    ];

    public function people()
    {
        return $this->belongsToMany(Person::class, 'band_members');
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
        return $this->hasOneThrough(Song::class, Album::class)->orderBy('songs.id');
    }

    public function getAllAlbumsStringAttribute()
    {
        $result = '';

        $first = true;
        foreach ($this->albums as $album) {
            if ($first) {
                $first = false;
            } else {
                $result .= ', ';
            }
            $result .= $album->name;
        }

        return $result;
    }

    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function getTitlesStringAttribute()
    {
        return implode(', ', $this->songs->pluck('title')->toArray());
    }

    public function getFirstTitleStringAttribute()
    {
        return $this->firstSong->title;
    }

    public function getImageUrlsStringAttribute()
    {
        return implode(', ', $this->images->pluck('url')->toArray());
    }
}
