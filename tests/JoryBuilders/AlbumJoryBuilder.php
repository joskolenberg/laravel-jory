<?php

namespace JosKolenberg\LaravelJory\Tests\JoryBuilders;

use Illuminate\Database\Eloquent\Builder;
use JosKolenberg\Jory\Support\Filter;
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
}
