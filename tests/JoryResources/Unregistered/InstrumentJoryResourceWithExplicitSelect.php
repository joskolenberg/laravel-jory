<?php

namespace JosKolenberg\LaravelJory\Tests\JoryResources\Unregistered;

use JosKolenberg\LaravelJory\Config\Filter;
use JosKolenberg\LaravelJory\JoryResource;
use JosKolenberg\LaravelJory\Tests\Models\Instrument;
use JosKolenberg\LaravelJory\Tests\Scopes\NumberOfAlbumsInYearFilter;

class InstrumentJoryResourceWithExplicitSelect extends JoryResource
{
    protected $modelClass = Instrument::class;

    protected function configure(): void
    {
        $this->explicitSelect();

        // Fields
        $this->field('id')->filterable()->sortable();
        $this->field('name')->filterable(function (Filter $filter){
            $filter->scope(new NumberOfAlbumsInYearFilter);
        })->sortable();
        $this->field('type_name')->filterable()->sortable()->hideByDefault();
    }

    public function scopeNameFilter($query, $operator, $data)
    {
        $this->applyWhere($query, 'name', $operator, $data);
        $query->has('people');
    }
}
