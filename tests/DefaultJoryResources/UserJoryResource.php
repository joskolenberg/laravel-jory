<?php


namespace JosKolenberg\LaravelJory\Tests\DefaultJoryResources;

use JosKolenberg\LaravelJory\JoryResource;
use JosKolenberg\LaravelJory\Tests\DefaultModels\Team;
use JosKolenberg\LaravelJory\Tests\DefaultModels\User;

class UserJoryResource extends JoryResource
{
    protected $modelClass = User::class;

    protected function configure(): void
    {
        // Fields
        $this->field('id')->filterable()->sortable();
        $this->field('name')->filterable()->sortable();
        $this->field('email')->filterable()->sortable();
        $this->field('password')->filterable()->sortable();
        $this->field('team_id')->filterable()->sortable();

        // Relations
        $this->relation('team');
    }
}