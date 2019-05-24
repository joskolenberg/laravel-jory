<?php

namespace JosKolenberg\LaravelJory\Tests\Models;

class Instrument extends Model
{
    protected $table = 'instruments';

    protected $hidden = [
        'pivot',
    ];

    protected $casts = [
        'id' => 'integer',
    ];

    public function people()
    {
        return $this->belongsToMany(Person::class);
    }
}
