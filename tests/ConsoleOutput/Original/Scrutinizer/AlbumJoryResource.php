<?php

namespace App\Http\JoryResources;

use JosKolenberg\LaravelJory\JoryResource;
use JosKolenberg\LaravelJory\Tests\Models\Album;

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
        $this->field('band_id')->filterable()->sortable();
        $this->field('id')->filterable()->sortable();
        $this->field('name')->filterable()->sortable();
        $this->field('release_date')->filterable()->sortable();

        // Custom attributes
        $this->field('cover_image')->hideByDefault();

        // Relations
        $this->relation('album_cover');
        $this->relation('band');
        $this->relation('cover');
        $this->relation('snake_case_album_cover');
        $this->relation('songs');
        $this->relation('tags');
    }
}
