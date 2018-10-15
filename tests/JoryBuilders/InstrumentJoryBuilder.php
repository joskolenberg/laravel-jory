<?php

namespace JosKolenberg\LaravelJory\Tests\JoryBuilders;

use JosKolenberg\Jory\Support\Filter;
use JosKolenberg\LaravelJory\CustomJoryBuilder;

class InstrumentJoryBuilder extends CustomJoryBuilder
{
    protected function applyNameFilter($query, Filter $filter)
    {
        $this->doApplyDefaultFieldFilter($query, $filter);
        $query->has('people');
    }
}
