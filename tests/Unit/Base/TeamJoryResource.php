<?php


namespace JosKolenberg\LaravelJory\Tests\Unit\Base;


use JosKolenberg\LaravelJory\JoryResource;
use JosKolenberg\LaravelJory\Tests\DefaultModels\Team;

class TeamJoryResource extends JoryResource
{
    protected $modelClass = Team::class;

    protected function configure(): void
    {
        // Fields
        $this->field('id')->filterable()->sortable();
        $this->field('name')->filterable()->sortable();

        // Custom filters
        $this->filter('number_of_users', new NumberOfUsersFilter);
    }
}