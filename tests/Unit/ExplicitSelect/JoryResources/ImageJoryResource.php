<?php


namespace JosKolenberg\LaravelJory\Tests\Unit\ExplicitSelect\JoryResources;

use JosKolenberg\LaravelJory\JoryResource;
use JosKolenberg\LaravelJory\Tests\DefaultModels\Album;
use JosKolenberg\LaravelJory\Tests\DefaultModels\Image;

class ImageJoryResource extends JoryResource
{
    protected $modelClass = Image::class;

    protected function configure(): void
    {
        $this->explicitSelect();

        // Fields
        $this->field('id')->filterable()->sortable();
        $this->field('url')->filterable()->sortable();

        // Relations
    }
}