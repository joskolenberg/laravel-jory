<?php

namespace JosKolenberg\LaravelJory\Tests\Models\WithTraits;

use JosKolenberg\LaravelJory\Traits\JoryTrait;

class SongWithTrait extends \JosKolenberg\LaravelJory\Tests\Models\Song
{
    use JoryTrait;

    protected $table = 'songs';
}
