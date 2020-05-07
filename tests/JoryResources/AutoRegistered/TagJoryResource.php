<?php

namespace JosKolenberg\LaravelJory\Tests\JoryResources\AutoRegistered;

use JosKolenberg\LaravelJory\JoryResource;
use JosKolenberg\LaravelJory\Tests\Models\Tag;

class TagJoryResource extends JoryResource
{
    protected $modelClass = Tag::class;

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

        $this->field('song_titles_string')->load('songs');

        // Relations
        $this->relation('albums');
        $this->relation('songs');
    }
}
