<?php

namespace JosKolenberg\LaravelJory\Tests\Models;

class Song extends Model
{
    protected $table = 'songs';

    protected $casts = [
        'id' => 'integer',
        'album_id' => 'integer',
    ];

    public function album()
    {
        return $this->belongsTo(Album::class);
    }

    public function getAlbumNameAttribute()
    {
        return $this->album->name;
    }

    public function testRelationWithoutJoryResource()
    {
        return $this->hasMany(ModelWithoutJoryResource::class);
    }
}
