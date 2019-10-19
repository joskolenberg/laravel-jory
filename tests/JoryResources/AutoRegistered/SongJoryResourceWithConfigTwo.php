<?php

namespace JosKolenberg\LaravelJory\Tests\JoryResources\AutoRegistered;

use JosKolenberg\LaravelJory\JoryResource;
use JosKolenberg\LaravelJory\Tests\Models\Song;

class SongJoryResourceWithConfigTwo extends JoryResource
{
    protected $modelClass = Song::class;

    protected function configure(): void
    {
        // Fields
        $this->field('id');
        $this->field('title')->filterable()->sortable();
        $this->field('album_id');

        $this->limitDefault(null)->limitMax(null);
    }
}
