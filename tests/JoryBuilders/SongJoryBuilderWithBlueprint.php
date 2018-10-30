<?php

namespace JosKolenberg\LaravelJory\Tests\JoryBuilders;

use JosKolenberg\LaravelJory\JoryBuilder;
use JosKolenberg\LaravelJory\Blueprint\Sort;
use JosKolenberg\LaravelJory\Blueprint\Filter;
use JosKolenberg\LaravelJory\Tests\Models\Album;
use JosKolenberg\LaravelJory\Blueprint\Blueprint;

class SongJoryBuilderWithBlueprint extends JoryBuilder
{
    protected function blueprint(Blueprint $blueprint): void
    {
        $blueprint->field('id')
            ->sortable();

        $blueprint->field('title')
            ->description('The songs title.')
            ->filterable(function (Filter $filter){
                $filter->description('Filter on the title.');
            })
            ->sortable(function(Sort $sort){
                $sort->description('Order by the title.');
            });

        $blueprint->field('album_id')
            ->hideByDefault()
            ->filterable(function(Filter $filter){
                $filter->description('Filter on the album id.')->operators(['=']);
            });

        $blueprint->limitDefault(50)->limitMax(250);

        $blueprint->relation('album', Album::class);
    }
}