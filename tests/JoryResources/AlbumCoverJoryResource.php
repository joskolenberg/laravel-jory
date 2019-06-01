<?php

namespace JosKolenberg\LaravelJory\Tests\JoryResources;

use JosKolenberg\LaravelJory\JoryBuilder;
use JosKolenberg\LaravelJory\Config\Config;
use JosKolenberg\LaravelJory\JoryResource;
use JosKolenberg\LaravelJory\Tests\Models\AlbumCover;

class AlbumCoverJoryResource extends JoryResource
{

    protected $modelClass = AlbumCover::class;

    /**
     * Configure the JoryBuilder.
     *
     */
    protected function configure(): void
    {
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
