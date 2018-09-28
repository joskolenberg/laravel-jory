<?php

namespace JosKolenberg\LaravelJory\Tests\Models;

use JosKolenberg\LaravelJory\Traits\JoryTrait;

class Album extends Model
{
    use JoryTrait;

    protected $table = 'albums';
}
