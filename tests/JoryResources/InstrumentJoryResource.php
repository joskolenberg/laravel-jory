<?php

namespace JosKolenberg\LaravelJory\Tests\JoryResources;

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
        $this->field('type_name')->filterable()->sortable()->hideByDefault();
    }

    public function scopeNameFilter($query, $operator, $data)
    {
        $this->applyWhere($query, 'name', $operator, $data);
        $query->has('people');
    }
}
