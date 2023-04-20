<?php

namespace JosKolenberg\LaravelJory\Tests\Models;

class Person extends Model
{
    protected $table = 'people';

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y/m/d');
    }

    protected $hidden = [
        'pivot',
    ];

    protected $casts = [
        'id' => 'integer',
        'date_of_birth' => 'datetime',
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

    public function firstImage()
    {
        return $this->morphOne(Image::class, 'imageable');
    }

    public function getInstrumentsStringAttribute()
    {
        return implode(', ', $this->instruments->pluck('name')->toArray());
    }

    public function getFirstImageUrlAttribute()
    {
        return $this->firstImage->url;
    }
}
