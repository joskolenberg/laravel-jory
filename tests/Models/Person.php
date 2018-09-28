<?php

namespace JosKolenberg\LaravelJory\Tests\Models;

use JosKolenberg\LaravelJory\Traits\JoryTrait;

class Person extends Model
{
    use JoryTrait;

    protected $table = 'people';
}
