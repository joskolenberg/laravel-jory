<?php

namespace JosKolenberg\LaravelJory\Tests\JoryResources;

use JosKolenberg\LaravelJory\JoryBuilder;
use JosKolenberg\LaravelJory\Config\Config;
use JosKolenberg\LaravelJory\JoryResource;
use JosKolenberg\LaravelJory\Tests\Models\Song;

class SongJoryResource extends JoryResource
{
    protected $modelClass = Song::class;

    /**
     * Configure the JoryBuilder.
     *
     */
    protected function configure(): void
    {
        // Fields
        $this->field('id')->filterable()->sortable();
        $this->field('title')->filterable()->sortable();
        $this->field('album_id')->filterable()->sortable();

        // Custom filters
        $this->filter('album_name');

        // Relations
        $this->relation('album');
    }
}