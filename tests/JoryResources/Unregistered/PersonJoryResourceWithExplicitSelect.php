<?php

namespace JosKolenberg\LaravelJory\Tests\JoryResources\Unregistered;

use JosKolenberg\LaravelJory\JoryResource;
use JosKolenberg\LaravelJory\Tests\Models\Person;

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
        $this->field('full_name')->select(['first_name', 'last_name'])->filterable();
        // Custom attributes
        $this->field('instruments_string')->noSelect()->load('instruments')->hideByDefault();

        $this->filter('band.albums.songs.title');
        $this->filter('instruments.name');

        // Relations
        $this->relation('instruments');
        $this->relation('first_image');
    }

    public function scopeFullNameFilter($query, $operator, $data)
    {
        $query->where('first_name', 'like', '%'.$data.'%');
        $query->orWhere('last_name', 'like', '%'.$data.'%');
    }
}
