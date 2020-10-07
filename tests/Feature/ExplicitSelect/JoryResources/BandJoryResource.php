<?php


namespace JosKolenberg\LaravelJory\Tests\Feature\ExplicitSelect\JoryResources;

use JosKolenberg\LaravelJory\JoryResource;
use JosKolenberg\LaravelJory\Tests\DefaultModels\Band;
use JosKolenberg\LaravelJory\Tests\DefaultModels\Team;
use JosKolenberg\LaravelJory\Tests\DefaultModels\User;

class BandJoryResource extends JoryResource
{
    protected $modelClass = Band::class;

    protected function configure(): void
    {
        $this->explicitSelect();

        // Fields
        $this->field('id')->filterable()->sortable();
        $this->field('name')->filterable()->sortable();

        // Relations
        $this->relation('musicians');
    }
}