<?php


namespace JosKolenberg\LaravelJory\Tests\DefaultJoryResources;

use JosKolenberg\LaravelJory\JoryResource;
use JosKolenberg\LaravelJory\Tests\DefaultModels\Album;
use JosKolenberg\LaravelJory\Tests\DefaultModels\Image;

class ImageJoryResource extends JoryResource
{
    protected $modelClass = Image::class;

    protected function configure(): void
    {
        // Fields
        $this->field('id')->filterable()->sortable();
        $this->field('url')->filterable()->sortable();

        // Relations
    }
}