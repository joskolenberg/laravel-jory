<?php

namespace JosKolenberg\LaravelJory\Tests\JoryBuilders;

use JosKolenberg\LaravelJory\Config\Config;
use JosKolenberg\LaravelJory\JoryBuilder;

class InstrumentJoryBuilder extends JoryBuilder
{
    protected function config(Config $config): void
    {
        // Fields
        $config->field('id')->filterable()->sortable();
        $config->field('name')->filterable()->sortable();
    }

    protected function scopeNameFilter($query, $operator, $data)
    {
        $this->applyWhere($query, 'name', $operator, $data);
        $query->has('people');
    }
}
