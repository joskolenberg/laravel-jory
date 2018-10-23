<?php

namespace JosKolenberg\LaravelJory\Tests\JoryBuilders;

use JosKolenberg\Jory\Support\Filter;
use JosKolenberg\LaravelJory\JoryBuilder;

class InstrumentJoryBuilder extends JoryBuilder
{
    protected function applyNameFilter($query, Filter $filter)
    {
        $this->doApplyDefaultFieldFilter($query, $filter);
        $query->has('people');
    }
}
