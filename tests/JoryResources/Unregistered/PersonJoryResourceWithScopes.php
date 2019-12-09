<?php

namespace JosKolenberg\LaravelJory\Tests\JoryResources\Unregistered;

use JosKolenberg\LaravelJory\Config\Filter;
use JosKolenberg\LaravelJory\Config\Sort;
use JosKolenberg\LaravelJory\JoryResource;
use JosKolenberg\LaravelJory\Tests\Models\Person;
use JosKolenberg\LaravelJory\Tests\Scopes\FirstNameSort;
use JosKolenberg\LaravelJory\Tests\Scopes\FullNameFilter;
use JosKolenberg\LaravelJory\Tests\Scopes\SpecialFirstNameFilter;

class PersonJoryResourceWithScopes extends JoryResource
{
    protected $modelClass = Person::class;

    protected function configure(): void
    {
        // Fields
        $this->field('id')->filterable()->sortable();
        $this->field('first_name')->filterable(function ($filter){
            $filter->scope(new SpecialFirstNameFilter());
        })->sortable();
        $this->field('last_name')->filterable()->sortable();
        $this->field('date_of_birth')->filterable()->sortable(function(Sort $sort){
            $sort->scope(new FirstNameSort);
        });
        $this->field('full_name')->filterable(function (Filter $filter){
            $filter->scope(new FullNameFilter);
        });

        // Custom attributes
        $this->field('instruments_string')->load('instruments')->hideByDefault();
        $this->field('first_image_url')->load('firstImage')->hideByDefault();

        $this->filter('band.albums.songs.title');
        $this->filter('instruments.name');

        // Relations
        $this->relation('instruments');
        $this->relation('firstImage');
    }
}
