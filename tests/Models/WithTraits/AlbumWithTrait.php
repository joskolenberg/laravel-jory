<?php

namespace JosKolenberg\LaravelJory\Tests\Models\WithTraits;


use JosKolenberg\LaravelJory\Traits\JoryTrait;

class AlbumWithTrait extends \JosKolenberg\LaravelJory\Tests\Models\Album
{
    use JoryTrait;

    protected $table = 'albums';
}