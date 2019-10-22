<?php

namespace JosKolenberg\LaravelJory\Tests\JoryResources\Unregistered;

use JosKolenberg\LaravelJory\JoryResource;
use JosKolenberg\LaravelJory\Tests\Models\Image;

class ImageJoryResourceWithExplicitSelect extends JoryResource
{
    protected $modelClass = Image::class;

    /**
     * Configure the JoryResource.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->explicitSelect();

        // Fields
        $this->field('id')->filterable()->sortable();
        $this->field('url')->filterable()->sortable();
        $this->field('imageable_id')->filterable()->sortable();
        $this->field('imageable_type')->filterable()->sortable();
    }
}
