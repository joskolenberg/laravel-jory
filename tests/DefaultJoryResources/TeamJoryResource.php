<?php


namespace JosKolenberg\LaravelJory\Tests\DefaultJoryResources;

use JosKolenberg\LaravelJory\JoryResource;
use JosKolenberg\LaravelJory\Tests\DefaultModels\Team;
use JosKolenberg\LaravelJory\Tests\DefaultModels\User;

class TeamJoryResource extends JoryResource
{
    protected $modelClass = Team::class;

    protected function configure(): void
    {
        // Fields
        $this->field('id')->filterable()->sortable();
        $this->field('name')->filterable()->sortable();

        // Relations
        $this->relation('users');
    }
}