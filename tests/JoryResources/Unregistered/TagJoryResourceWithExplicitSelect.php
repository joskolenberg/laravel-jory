<?php

namespace JosKolenberg\LaravelJory\Tests\JoryResources\Unregistered;

use JosKolenberg\LaravelJory\JoryResource;
use JosKolenberg\LaravelJory\Tests\Models\Tag;

class TagJoryResourceWithExplicitSelect extends JoryResource
{
    protected $modelClass = Tag::class;

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
        $this->field('name')->filterable()->sortable();

        // Relations
        $this->relation('albums');
        $this->relation('songs');
    }
}
