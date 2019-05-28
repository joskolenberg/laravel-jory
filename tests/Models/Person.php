<?php

namespace JosKolenberg\LaravelJory\Tests\Models;

class Person extends Model
{
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
        return $this->first_name.' '.$this->last_name;
    }

    public function groupies()
    {
        return $this->hasMany(Groupie::class);
    }

    public function band()
    {
        return $this->belongsToMany(Band::class, 'band_members');
    }
}
