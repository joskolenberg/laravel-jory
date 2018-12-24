<?php

namespace JosKolenberg\LaravelJory\Tests\Models;

use JosKolenberg\LaravelJory\Traits\JoryTrait;

class Instrument extends Model
{
    use JoryTrait;

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
