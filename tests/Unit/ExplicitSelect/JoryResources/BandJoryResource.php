<?php


namespace JosKolenberg\LaravelJory\Tests\Unit\ExplicitSelect\JoryResources;

use JosKolenberg\LaravelJory\JoryResource;
use JosKolenberg\LaravelJory\Tests\Unit\ExplicitSelect\Models\Band;

class BandJoryResource extends JoryResource
{
    protected $modelClass = Band::class;

    protected function configure(): void
    {
        $this->explicitSelect();

        // Fields
        $this->field('id')->filterable()->sortable();
        $this->field('name')->filterable()->sortable();
        $this->field('musicians_string')->noSelect()->load('musicians');
        $this->field('songs_string')->noSelect()->load('songs');
        $this->field('first_title_string')->noSelect()->load('firstSong');
        $this->field('first_image_url')->noSelect()->load('firstImage');
        $this->field('image_urls_string')->noSelect()->load('images');
        $this->field('tags_string')->noSelect()->load('tags');

        // Relations
        $this->relation('musicians');
        $this->relation('songs');
        $this->relation('firstSong');
        $this->relation('firstImage');
        $this->relation('images');
        $this->relation('tags');
    }
}