<?php

namespace App\Http\JoryResources;

use JosKolenberg\LaravelJory\JoryResource;
use JosKolenberg\LaravelJory\Tests\Models\SubFolder\Album;

class AlbumJoryResource extends JoryResource
{
    protected $modelClass = Album::class;

    /**
     * Configure the JoryResource.
     *
     * @return void
     */
    protected function configure(): void
    {
        // Fields
        $this->field('id')->filterable()->sortable();
        $this->field('name')->filterable()->sortable();
        $this->field('band_id')->filterable()->sortable();
        $this->field('release_date')->filterable()->sortable();

        // Custom attributes
        $this->field('cover_image');
        $this->field('tag_names_string');
        $this->field('titles_string');

        // Relations
        $this->relation('albumCover');
        $this->relation('band');
        $this->relation('cover');
        $this->relation('songs');
        $this->relation('tags');
    }
}
