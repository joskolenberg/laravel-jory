<?php

namespace JosKolenberg\LaravelJory\Tests\JoryResources\Unregistered;

use JosKolenberg\LaravelJory\Tests\JoryResources\AutoRegistered\SongJoryResource;
use JosKolenberg\LaravelJory\Tests\Models\Song;

class SongJoryResourceWithAlternateUri extends SongJoryResource
{
    protected $modelClass = Song::class;

    protected $uri = 'ssoonngg';

}
