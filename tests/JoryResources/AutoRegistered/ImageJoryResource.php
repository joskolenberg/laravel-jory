<?php

namespace JosKolenberg\LaravelJory\Tests\JoryResources\AutoRegistered;

use JosKolenberg\LaravelJory\JoryResource;
use JosKolenberg\LaravelJory\Tests\Models\Image;

class ImageJoryResource extends JoryResource
{
    protected $modelClass = Image::class;

    /**
     * Configure the JoryResource.
     *
     * @return void
     */
    protected function configure(): void
    {
        // Fields
        $this->field('id')->filterable()->sortable();
        $this->field('url')->filterable()->sortable();
        $this->field('imageable_id')->filterable()->sortable();
        $this->field('imageable_type')->filterable()->sortable();
    }
}
