<?php

namespace JosKolenberg\LaravelJory\Tests\Models;

use JosKolenberg\LaravelJory\AbstractJoryBuilder;
use JosKolenberg\LaravelJory\Tests\JoryBuilders\InstrumentJoryBuilder;
use JosKolenberg\LaravelJory\Traits\JoryTrait;

class Instrument extends Model
{
    use JoryTrait;

    public static function jory(): AbstractJoryBuilder
    {
        return (new InstrumentJoryBuilder())->onModel(static::class);
    }

    protected $table = 'instruments';

    public function people()
    {
        return $this->belongsToMany(Person::class);
    }
}
