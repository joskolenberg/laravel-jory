<?php

namespace JosKolenberg\LaravelJory\Tests\Models\WithTraits;

use JosKolenberg\LaravelJory\Traits\JoryTrait;

class BandWithTrait extends \JosKolenberg\LaravelJory\Tests\Models\Band
{
    use JoryTrait;

    protected $table = 'bands';
}
