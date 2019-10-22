<?php

namespace JosKolenberg\LaravelJory\Tests\Models;

class Tag extends Model
{
    protected $table = 'tags';

    protected $casts = [
        'id' => 'integer',
        'taggable_id' => 'integer',
    ];

    public function songs()
    {
        return $this->morphedByMany(Song::class, 'taggable');
    }

    public function albums()
    {
        return $this->morphedByMany(Album::class, 'taggable');
    }

    public function getSongTitlesStringAttribute()
    {
        return implode(', ', $this->songs->pluck('title')->toArray());
    }
}
