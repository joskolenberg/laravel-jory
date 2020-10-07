<?php


namespace JosKolenberg\LaravelJory\Tests\Feature\Filter\JoryResources;

use JosKolenberg\LaravelJory\Tests\Feature\Filter\Scopes\HasUserWithNameFilter;

class TeamJoryResource extends \JosKolenberg\LaravelJory\Tests\DefaultJoryResources\TeamJoryResource
{
    protected function configure(): void
    {
        parent::configure();

        $this->filter('has_user_with_name', new HasUserWithNameFilter);
        $this->filter('users.name');
    }
}