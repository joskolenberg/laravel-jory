<?php

namespace JosKolenberg\LaravelJory\Tests\Models;

use JosKolenberg\LaravelJory\Traits\JoryTrait;

class Person extends Model
{
    use JoryTrait;

    protected $table = 'people';

    protected $hidden = [
        'pivot',
    ];

    protected $casts = [
        'id' => 'integer',
    ];

    public function instruments()
    {
        return $this->belongsToMany(Instrument::class, 'instrument_person');
    }
}
