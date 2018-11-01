<?php

namespace JosKolenberg\LaravelJory\Tests\JoryBuilders;

use JosKolenberg\LaravelJory\JoryBuilder;
use JosKolenberg\LaravelJory\Config\Sort;
use JosKolenberg\LaravelJory\Config\Filter;
use JosKolenberg\LaravelJory\Config\Config;
use JosKolenberg\LaravelJory\Tests\Models\Album;

class SongJoryBuilderWithConfig extends JoryBuilder
{
    protected function config(Config $config): void
    {
        $config->field('id')
            ->sortable();

        $config->field('title')
            ->description('The songs title.')
            ->filterable(function (Filter $filter){
                $filter->description('Filter on the title.');
            })
            ->sortable(function(Sort $sort){
                $sort->description('Order by the title.');
            });

        $config->field('album_id')
            ->hideByDefault()
            ->filterable(function(Filter $filter){
                $filter->description('Filter on the album id.')->operators(['=']);
            });

        $config->limitDefault(50)->limitMax(250);

        $config->relation('album', Album::class);
    }
}