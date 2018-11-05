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

    public function scopeAlbumNameFilter($query, $operator, $data)
    {
        $query->whereHas('album', function ($query) use ($operator, $data) {
            $query->where('name', $operator, $data);
        });
    }

    public function scopeAlbumNameSort($query, string $order)
    {
        $query->join('albums', 'songs.album_id', 'albums.id')->orderBy('albums.name', $order);
    }
}
