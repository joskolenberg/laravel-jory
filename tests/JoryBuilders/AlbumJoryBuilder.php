<?php

namespace JosKolenberg\LaravelJory\Tests\JoryBuilders;

use JosKolenberg\Jory\Support\Filter;
use JosKolenberg\Jory\Support\Sort;
use JosKolenberg\LaravelJory\CustomJoryBuilder;

class AlbumJoryBuilder extends CustomJoryBuilder
{
    protected function applyNumberOfSongsFilter($query, Filter $filter)
    {
        $query->has('songs', $filter->getOperator(), $filter->getValue());
    }

    protected function applyHasSongWithTitleFilter($query, Filter $filter)
    {
        $query->whereHas('songs', function ($query) use ($filter) {
            $query->where('title', $filter->getOperator(), $filter->getValue());
        });
    }

    protected function applyNumberOfSongsSort($query, Sort $sort)
    {
        $query->withCount('songs')->orderBy('songs_count', $sort->getOrder());
    }

    protected function applyBandNameSort($query, Sort $sort)
    {
        $query->join('bands', 'band_id', 'bands.id')
            ->orderBy('bands.name', $sort->getOrder());
    }
}
