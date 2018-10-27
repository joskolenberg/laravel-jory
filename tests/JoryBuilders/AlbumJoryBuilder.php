<?php

namespace JosKolenberg\LaravelJory\Tests\JoryBuilders;

use JosKolenberg\LaravelJory\JoryBuilder;

class AlbumJoryBuilder extends JoryBuilder
{
    protected function scopeNumberOfSongsFilter($query, $operator, $value)
    {
        $query->has('songs', $operator, $value);
    }

    protected function scopeHasSongWithTitleFilter($query, $operator, $value)
    {
        $query->whereHas('songs', function ($query) use ($operator, $value) {
            $query->where('title', $operator, $value);
        });
    }

    protected function scopeNumberOfSongsSort($query, string $order)
    {
        $query->withCount('songs')->orderBy('songs_count', $order);
    }

    protected function scopeBandNameSort($query, string $order)
    {
        $query->join('bands', 'band_id', 'bands.id')->orderBy('bands.name', $order);
    }
}
