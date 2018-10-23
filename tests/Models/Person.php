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

    protected $appends = [
        'full_name',
    ];

    public function instruments()
    {
        return $this->belongsToMany(Instrument::class, 'instrument_person');
    }

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}
