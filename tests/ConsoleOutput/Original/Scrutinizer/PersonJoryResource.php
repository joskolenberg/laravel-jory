<?php

namespace App\Http\JoryResources;

use JosKolenberg\LaravelJory\JoryResource;
use JosKolenberg\LaravelJory\Tests\Models\Person;

class PersonJoryResource extends JoryResource
{
    protected $modelClass = Person::class;

    /**
     * Configure the JoryResource.
     *
     * @return void
     */
    protected function configure(): void
    {
        // Fields
        $this->field('date_of_birth')->filterable()->sortable();
        $this->field('first_name')->filterable()->sortable();
        $this->field('id')->filterable()->sortable();
        $this->field('last_name')->filterable()->sortable();

        // Custom attributes
        $this->field('full_name')->hideByDefault();

        // Relations
        $this->relation('band');
        $this->relation('groupies');
        $this->relation('instruments');
    }
}
