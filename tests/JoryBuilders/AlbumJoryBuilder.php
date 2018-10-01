<?php

namespace JosKolenberg\LaravelJory\Tests\JoryBuilders;

use Illuminate\Database\Eloquent\Builder;
use JosKolenberg\Jory\Support\Filter;
use JosKolenberg\LaravelJory\CustomJoryBuilder;

class AlbumJoryBuilder extends CustomJoryBuilder
{
    protected function applyNumberOfSongsFilter(Builder $query, Filter $filter)
    {
        $query->has('songs', $filter->getOperator(), $filter->getValue());
    }
}
