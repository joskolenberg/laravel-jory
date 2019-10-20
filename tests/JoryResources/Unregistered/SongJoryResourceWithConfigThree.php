<?php

namespace JosKolenberg\LaravelJory\Tests\JoryResources\Unregistered;

use JosKolenberg\LaravelJory\JoryResource;
use JosKolenberg\LaravelJory\Tests\Models\Song;

class SongJoryResourceWithConfigThree extends JoryResource
{
    protected $modelClass = Song::class;

    protected function configure(): void
    {
        // Fields
        $this->field('id');
        $this->field('title');
        $this->field('album_id');

        $this->limitDefault(null)->limitMax(10);

        $this->sort('title')->default(2, 'desc');
        $this->sort('album_name')->default(1, 'asc');
    }
}
