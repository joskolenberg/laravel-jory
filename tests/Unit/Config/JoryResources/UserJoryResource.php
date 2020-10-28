<?php


namespace JosKolenberg\LaravelJory\Tests\Unit\Config\JoryResources;

use JosKolenberg\LaravelJory\Config\Filter;
use JosKolenberg\LaravelJory\Config\Sort;
use JosKolenberg\LaravelJory\JoryResource;
use JosKolenberg\LaravelJory\Tests\DefaultModels\User;

class UserJoryResource extends JoryResource
{
    protected $modelClass = User::class;

    protected function configure(): void
    {
        // Fields
        $this->field('id')->filterable()->sortable();
        $this->field('name')->filterable(function (Filter $filter){
            $filter->operators(['=']);
        })->sortable(function (Sort $sort){
            $sort->default(1, 'desc');
        });

        $this->filter('custom_filter')->operators(['>', '<']);
        $this->sort('custom_sort')->default(2);

        $this->limitDefault(10);
        $this->limitMax(100);

        // Relations
        $this->relation('team');
    }
}