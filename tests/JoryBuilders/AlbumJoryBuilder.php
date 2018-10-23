<?php

namespace JosKolenberg\LaravelJory\Tests\JoryBuilders;

use JosKolenberg\Jory\Support\Sort;
use JosKolenberg\Jory\Support\Filter;
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

    protected function applyNumberOfSongsSort($query, Sort $sort)
    {
        $query->withCount('songs')->orderBy('songs_count', $sort->getOrder());
    }

    protected function applyBandNameSort($query, Sort $sort)
    {
        $query->join('bands', 'band_id', 'bands.id')->orderBy('bands.name', $sort->getOrder());
    }
}
