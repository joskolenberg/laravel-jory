<?php

namespace App\Http\JoryResources;

use JosKolenberg\LaravelJory\JoryResource;
use JosKolenberg\LaravelJory\Tests\Feature\Console\Models\User;

class UserJoryResource extends JoryResource
{
    protected $modelClass = User::class;

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
        $this->field('email')->filterable()->sortable();
        $this->field('team_id')->filterable()->sortable();

        // Custom attributes
        $this->field('email_domain');

        // Relations
        $this->relation('notifications');
        $this->relation('team');
    }
}
