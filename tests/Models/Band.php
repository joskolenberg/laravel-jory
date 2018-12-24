<?php

namespace JosKolenberg\LaravelJory\Tests\Models;

use JosKolenberg\LaravelJory\Traits\JoryTrait;

class Band extends Model
{
    use JoryTrait;

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

    public function scopeHasAlbumWithNameFilter($query, $operator, $data)
    {
        $query->whereHas('albums', function ($query) use ($operator, $data) {
            $query->where('name', $operator, $data);
        });
    }
}
