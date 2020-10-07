<?php


namespace JosKolenberg\LaravelJory\Tests\Feature\ExplicitSelect\JoryResources;

use JosKolenberg\LaravelJory\JoryResource;
use JosKolenberg\LaravelJory\Tests\Feature\ExplicitSelect\Models\Team;

class TeamJoryResource extends JoryResource
{
    protected $modelClass = Team::class;

    protected function configure(): void
    {
        $this->explicitSelect();

        // Fields
        $this->field('id')->filterable()->sortable();
        $this->field('name')->filterable()->sortable();
        $this->field('first_user_field')->noSelect()->load('firstUser');
        $this->field('users_string')->noSelect()->load('users');

        // Relations
        $this->relation('users');
        $this->relation('firstUser');
    }
}