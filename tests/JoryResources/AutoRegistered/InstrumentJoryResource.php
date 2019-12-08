<?php

namespace JosKolenberg\LaravelJory\Tests\JoryResources\AutoRegistered;

use JosKolenberg\LaravelJory\Config\Filter;
use JosKolenberg\LaravelJory\JoryResource;
use JosKolenberg\LaravelJory\Tests\Models\Instrument;
use JosKolenberg\LaravelJory\Tests\Scopes\NameFilter;

class InstrumentJoryResource extends JoryResource
{
    protected $modelClass = Instrument::class;

    protected function configure(): void
    {
        // Fields
        $this->field('id')->filterable()->sortable();
        $this->field('name')->filterable(function (Filter $filter){
            $filter->scope(new NameFilter);
        })->sortable();
        $this->field('type_name')->filterable()->sortable()->hideByDefault();
    }
}
