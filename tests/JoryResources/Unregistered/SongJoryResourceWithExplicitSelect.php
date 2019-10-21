<?php

namespace JosKolenberg\LaravelJory\Tests\JoryResources\Unregistered;

use JosKolenberg\LaravelJory\JoryResource;
use JosKolenberg\LaravelJory\Tests\Models\Song;

class SongJoryResourceWithExplicitSelect extends JoryResource
{
    protected $modelClass = Song::class;

    protected function configure(): void
    {
        $this->explicitSelect();

        // Fields
        $this->field('id')->filterable()->sortable();
        $this->field('title')->filterable()->sortable();
        $this->field('album_id')->filterable()->sortable();

        // Custom attributes
        $this->field('album_name')->noSelect()->load('album')->hideByDefault();

        // Custom filters
        $this->filter('album_name');

        // Relations
        $this->relation('album');
    }
}
