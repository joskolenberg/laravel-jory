<?php

namespace JosKolenberg\LaravelJory\Tests\JoryResources;

use JosKolenberg\LaravelJory\JoryResource;
use JosKolenberg\LaravelJory\Tests\Models\Person;

class PersonJoryResource extends JoryResource
{
    protected $modelClass = Person::class;

    protected function configure(): void
    {
        $this->explicitSelect();

        // Fields
        $this->field('id')->filterable()->sortable();
        $this->field('first_name')->select('people.first_name')->filterable()->sortable();
        $this->field('last_name')->select(['people.last_name'])->filterable()->sortable();
        $this->field('date_of_birth')->filterable()->sortable();
        $this->field('full_name')->select(['first_name', 'last_name'])->filterable();

        $this->filter('band.albums.songs.title');
        $this->filter('instruments.name');

        // Relations
        $this->relation('instruments');
    }

    public function scopeFullNameFilter($query, $operator, $data)
    {
        $query->where('first_name', 'like', '%'.$data.'%');
        $query->orWhere('last_name', 'like', '%'.$data.'%');
    }
}
