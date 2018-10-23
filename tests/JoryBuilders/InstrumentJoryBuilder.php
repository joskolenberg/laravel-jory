<?php

namespace JosKolenberg\LaravelJory\Tests\JoryBuilders;

use JosKolenberg\Jory\Support\Filter;
use JosKolenberg\LaravelJory\JoryBuilder;

class InstrumentJoryBuilder extends JoryBuilder
{
    protected function scopeNameFilter($query, $operator, $value)
    {
        $this->doApplyDefaultFieldFilter($query, 'name', $operator, $value);
        $query->has('people');
    }
}
