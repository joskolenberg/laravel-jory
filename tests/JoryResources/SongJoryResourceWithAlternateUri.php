<?php

namespace JosKolenberg\LaravelJory\Tests\JoryResources;

use JosKolenberg\LaravelJory\Tests\Models\Song;

class SongJoryResourceWithAlternateUri extends SongJoryResource
{
    protected $modelClass = Song::class;

    protected $uri = 'ssoonngg';

}
