<?php


namespace JosKolenberg\LaravelJory\Tests\DefaultJoryResources;

use JosKolenberg\LaravelJory\JoryResource;
use JosKolenberg\LaravelJory\Tests\DefaultModels\Band;

class SongJoryResource extends JoryResource
{
    protected $modelClass = Band::class;

    protected function configure(): void
    {
        // Fields
        $this->field('id')->filterable()->sortable();
        $this->field('title')->filterable()->sortable();

        // Relations
    }
}