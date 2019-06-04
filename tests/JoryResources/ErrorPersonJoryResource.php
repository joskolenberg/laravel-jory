<?php

namespace JosKolenberg\LaravelJory\Tests\JoryResources;

use JosKolenberg\LaravelJory\Tests\Models\ErrorPerson;

class ErrorPersonJoryResource extends PersonJoryResource
{
    protected $modelClass = ErrorPerson::class;

    protected function configure(): void
    {
        parent::configure();

        // Existing relation, but Groupie has no associated JoryResource
        $this->relation('groupies');
    }

}
