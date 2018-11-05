<?php

namespace JosKolenberg\LaravelJory\Tests\JoryBuilders;

use JosKolenberg\LaravelJory\JoryBuilder;

class InstrumentJoryBuilder extends JoryBuilder
{
    protected function scopeNameFilter($query, $operator, $data)
    {
        $this->applyDefaultFieldFilter($query, 'name', $operator, $data);
        $query->has('people');
    }
}
