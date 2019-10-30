<?php

namespace App\Http\JoryResources;

use JosKolenberg\LaravelJory\JoryResource;
use JosKolenberg\LaravelJory\Tests\Models\User;

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
        $this->field('created_at')->filterable()->sortable();
        $this->field('updated_at')->filterable()->sortable();

        // Relations
        $this->relation('notifications');
    }
}
