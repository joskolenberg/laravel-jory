<?php

namespace JosKolenberg\LaravelJory\Tests\JoryResources;

use JosKolenberg\LaravelJory\Config\Config;
use JosKolenberg\LaravelJory\JoryBuilder;
use JosKolenberg\LaravelJory\JoryResource;
use JosKolenberg\LaravelJory\Tests\Models\Instrument;

class InstrumentJoryResource extends JoryResource
{
    protected $modelClass = Instrument::class;

    protected function configure(): void
    {
        // Fields
        $this->field('id')->filterable()->sortable();
        $this->field('name')->filterable()->sortable();
    }

    public function scopeNameFilter($query, $operator, $data)
    {
        $this->applyWhere($query, 'name', $operator, $data);
        $query->has('people');
    }
}
