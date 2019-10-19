<?php

namespace JosKolenberg\LaravelJory\Tests\Models;

class Person extends Model
{
    protected $table = 'people';

    protected $dateFormat = 'Y/m/d';

    protected $hidden = [
        'pivot',
    ];

    protected $casts = [
        'id' => 'integer',
    ];

    protected $appends = [
        'full_name',
    ];

    protected $dates = [
        'date_of_birth'
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

    public function firstImage()
    {
        return $this->morphOne(Image::class, 'imageable');
    }
}
