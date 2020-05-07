<?php

namespace App\Http\JoryResources;

use JosKolenberg\LaravelJory\JoryResource;
use JosKolenberg\LaravelJory\Tests\Models\Band;

class BandJoryResource extends JoryResource
{
    protected $modelClass = Band::class;

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
        $this->field('year_end')->filterable()->sortable();
        $this->field('year_start')->filterable()->sortable();

        // Custom attributes
        $this->field('all_albums_string');
        $this->field('first_title_string');
        $this->field('image_urls_string');
        $this->field('titles_string');

        // Relations
        $this->relation('albums');
        $this->relation('firstSong');
        $this->relation('images');
        $this->relation('people');
        $this->relation('songs');
    }
}
