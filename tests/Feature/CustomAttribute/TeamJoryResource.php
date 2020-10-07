<?php

namespace JosKolenberg\LaravelJory\Tests\Feature\CustomAttribute;

use JosKolenberg\LaravelJory\JoryResource;

class TeamJoryResource extends JoryResource
{
    protected $modelClass = Team::class;

    /**
     * Configure the JoryResource.
     *
     * @return void
     */
    protected function configure(): void
    {
        // Fields
        $this->field('id')->filterable()->sortable();
        $this->field('name')->filterable()->sortable();

        // Custom attributes
        $this->field('users_string');
    }
}
