<?php


namespace JosKolenberg\LaravelJory\Tests\Unit\ExplicitSelect\JoryResources;

use JosKolenberg\LaravelJory\JoryResource;
use JosKolenberg\LaravelJory\Tests\DefaultModels\Band;

class SongJoryResource extends JoryResource
{
    protected $modelClass = Band::class;

    protected function configure(): void
    {
        $this->explicitSelect();

        // Fields
        $this->field('id')->filterable()->sortable();
        $this->field('title')->filterable()->sortable();

        // Relations
    }
}