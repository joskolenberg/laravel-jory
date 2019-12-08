<?php

namespace JosKolenberg\LaravelJory\Tests\JoryResources\Unregistered;

use JosKolenberg\LaravelJory\Config\Sort;
use JosKolenberg\LaravelJory\Config\Filter;
use JosKolenberg\LaravelJory\JoryResource;
use JosKolenberg\LaravelJory\Tests\Models\Song;

class SongJoryResourceWithConfig extends JoryResource
{
    protected $modelClass = Song::class;

    protected function configure(): void
    {
        $this->field('id')->sortable();

        $this->field('title')->description('The songs title.')->filterable(function (Filter $filter) {
            $filter->description('Filter on the title.');
        })->sortable(function (Sort $sort) {
            $sort->description('Order by the title.');
        });

        $this->field('album_id')->hideByDefault()->filterable(function (Filter $filter) {
            $filter->description('Filter on the album id.')->operators(['=']);
        });

        $this->limitDefault(50)->limitMax(250);

        $this->relation('album');
        $this->relation('testRelationWithoutJoryResource');
    }
}
