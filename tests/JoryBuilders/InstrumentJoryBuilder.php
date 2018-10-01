<?php

namespace JosKolenberg\LaravelJory\Tests\JoryBuilders;

use Illuminate\Database\Eloquent\Builder;
use JosKolenberg\Jory\Support\Filter;
use JosKolenberg\LaravelJory\CustomJoryBuilder;

class InstrumentJoryBuilder extends CustomJoryBuilder
{
    protected function applyNameFilter(Builder $query, Filter $filter)
    {
        $this->doApplyDefaultFieldFilter($query, $filter);
        $query->has('people');
    }
}
