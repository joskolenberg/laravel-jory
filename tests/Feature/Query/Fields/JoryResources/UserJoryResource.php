<?php

namespace JosKolenberg\LaravelJory\Tests\Feature\Query\Fields\JoryResources;

use JosKolenberg\LaravelJory\Tests\Feature\Query\Fields\Models\User;

class UserJoryResource extends \JosKolenberg\LaravelJory\Tests\DefaultJoryResources\UserJoryResource
{
    protected $modelClass = User::class;

    protected function configure(): void
    {
        parent::configure();

        $this->field('custom_value')->noSelect();
    }
}