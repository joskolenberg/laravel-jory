<?php

namespace JosKolenberg\LaravelJory\Tests\Models\WithTraits;

use JosKolenberg\LaravelJory\Traits\JoryTrait;

class InstrumentWithTrait extends \JosKolenberg\LaravelJory\Tests\Models\Instrument
{
    use JoryTrait;

    protected $table = 'instruments';
}
