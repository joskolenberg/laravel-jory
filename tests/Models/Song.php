<?php

namespace JosKolenberg\LaravelJory\Tests\Models;

use JosKolenberg\LaravelJory\Traits\JoryTrait;

class Song extends Model
{
    use JoryTrait;

    protected $table = 'songs';

    protected $casts = [
        'id' => 'integer',
        'album_id' => 'integer',
    ];

    public function album()
    {
        return $this->belongsTo(Album::class);
    }

    public function scopeAlbumNameFilter($query, $operator, $value)
    {
        $query->whereHas('album', function ($query) use ($operator, $value) {
            $query->where('name', $operator, $value);
        });
    }
}
