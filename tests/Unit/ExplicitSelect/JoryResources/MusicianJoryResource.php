<?php


namespace JosKolenberg\LaravelJory\Tests\Unit\ExplicitSelect\JoryResources;

use JosKolenberg\LaravelJory\JoryResource;
use JosKolenberg\LaravelJory\Tests\DefaultModels\Band;
use JosKolenberg\LaravelJory\Tests\DefaultModels\Musician;
use JosKolenberg\LaravelJory\Tests\DefaultModels\Team;
use JosKolenberg\LaravelJory\Tests\DefaultModels\User;

class MusicianJoryResource extends JoryResource
{
    protected $modelClass = Musician::class;

    protected function configure(): void
    {
        $this->explicitSelect();

        // Fields
        $this->field('id')->filterable()->sortable();
        $this->field('name')->filterable()->sortable();

        // Relations
        $this->relation('bands');
    }
}