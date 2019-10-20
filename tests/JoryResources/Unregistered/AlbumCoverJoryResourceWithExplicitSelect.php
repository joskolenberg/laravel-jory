<?php

namespace JosKolenberg\LaravelJory\Tests\JoryResources\Unregistered;

use JosKolenberg\LaravelJory\JoryResource;
use JosKolenberg\LaravelJory\Tests\Models\AlbumCover;

class AlbumCoverJoryResourceWithExplicitSelect extends JoryResource
{

    protected $modelClass = AlbumCover::class;

    protected function configure(): void
    {
        $this->explicitSelect();

        // Fields
        $this->field('id')->filterable()->sortable();
        $this->field('image')->filterable()->sortable();
        $this->field('album_id')->filterable()->sortable();

        // Custom sorts
        $this->sort('album_name');

        // Relations
        $this->relation('album');
    }
}
