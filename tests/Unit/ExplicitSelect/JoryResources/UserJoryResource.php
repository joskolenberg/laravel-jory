<?php


namespace JosKolenberg\LaravelJory\Tests\Unit\ExplicitSelect\JoryResources;

use JosKolenberg\LaravelJory\JoryResource;
use JosKolenberg\LaravelJory\Tests\DefaultModels\User;

class UserJoryResource extends JoryResource
{
    protected $modelClass = User::class;

    protected function configure(): void
    {
        $this->explicitSelect();

        // Fields
        $this->field('id')->filterable()->sortable();
        $this->field('name')->filterable()->sortable();
        $this->field('email')->filterable()->sortable();
        $this->field('team_id')->filterable()->sortable();

        $this->field('description')->select('name', 'email');

        $this->field('team_name')->noSelect()->load('team');

        // Relations
        $this->relation('team');
    }
}