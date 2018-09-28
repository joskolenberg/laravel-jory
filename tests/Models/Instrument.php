<?php

namespace JosKolenberg\LaravelJory\Tests\Models;

use JosKolenberg\LaravelJory\Traits\JoryTrait;

class Instrument extends Model
{
    use JoryTrait;

    protected $table = 'instruments';
}
