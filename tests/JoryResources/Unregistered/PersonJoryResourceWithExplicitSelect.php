<?php

namespace JosKolenberg\LaravelJory\Tests\JoryResources\Unregistered;

use JosKolenberg\LaravelJory\Config\Filter;
use JosKolenberg\LaravelJory\JoryResource;
use JosKolenberg\LaravelJory\Tests\Models\Person;
use JosKolenberg\LaravelJory\Tests\Scopes\FullNameFilter;

class PersonJoryResourceWithExplicitSelect extends JoryResource
{
    protected $modelClass = Person::class;

    protected function configure(): void
    {
        $this->explicitSelect();

        // Fields
        $this->field('id')->select('people.id')->filterable()->sortable();
        $this->field('first_name')->filterable()->sortable();
        $this->field('last_name')->filterable()->sortable();
        $this->field('date_of_birth')->select(['date_of_birth'])->filterable()->sortable();
        $this->field('full_name')
            ->select('first_name', 'last_name')
            ->filterable(function (Filter $filter){
                $filter->scope(new FullNameFilter);
            });

        // Custom attributes
        $this->field('instruments_string')->noSelect()->load('instruments');
        $this->field('first_image_url')->noSelect()->load('firstImage');

        $this->filter('band.albums.songs.title');
        $this->filter('instruments.name');

        // Relations
        $this->relation('instruments');
        $this->relation('firstImage');
    }
}
