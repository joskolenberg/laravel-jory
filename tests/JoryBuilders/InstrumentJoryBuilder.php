<?php

namespace JosKolenberg\LaravelJory\Tests\JoryBuilders;

use JosKolenberg\LaravelJory\JoryBuilder;

class InstrumentJoryBuilder extends JoryBuilder
{
    protected function scopeNameFilter($query, $operator, $value)
    {
        $this->applyDefaultFieldFilter($query, 'name', $operator, $value);
        $query->has('people');
    }
}
