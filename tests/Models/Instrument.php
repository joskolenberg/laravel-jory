<?php

namespace JosKolenberg\LaravelJory\Tests\Models;

use JosKolenberg\LaravelJory\Traits\JoryTrait;
use JosKolenberg\LaravelJory\Tests\JoryBuilders\InstrumentJoryBuilder;

class Instrument extends Model
{
    use JoryTrait;

    protected $table = 'instruments';

    public function people()
    {
        return $this->belongsToMany(Person::class);
    }

    public static function getJoryBuilder()
    {
        return new InstrumentJoryBuilder();
    }
}
