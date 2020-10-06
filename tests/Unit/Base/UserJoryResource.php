<?php


namespace JosKolenberg\LaravelJory\Tests\Unit\Base;


use JosKolenberg\LaravelJory\Config\Filter;
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
            $filter->scope(new UserNameFilter);
        })->sortable();
    }
}