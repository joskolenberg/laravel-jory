<?php

namespace App\Http\JoryResources;

use JosKolenberg\LaravelJory\JoryResource;
use JosKolenberg\LaravelJory\Tests\Models\Band;

class AlternateBandJoryResource extends JoryResource
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
        $this->field('year_start')->filterable()->sortable();
        $this->field('year_end')->filterable()->sortable();

        // Custom attributes
        $this->field('all_albums_string')->hideByDefault();
        $this->field('first_title_string')->hideByDefault();
        $this->field('image_urls_string')->hideByDefault();
        $this->field('titles_string')->hideByDefault();

        // Relations
        $this->relation('albums');
        $this->relation('first_song');
        $this->relation('images');
        $this->relation('people');
        $this->relation('songs');
    }
}
