<?php

namespace JosKolenberg\LaravelJory\Tests\Models;

use JosKolenberg\LaravelJory\Traits\JoryTrait;

class Band extends Model
{
    use JoryTrait;

    protected $table = 'bands';
}
