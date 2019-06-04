<?php

namespace JosKolenberg\LaravelJory\Tests\Models;

/**
 * Class Groupie
 *
 * This is a Model without a JoryResource associated.
 *
 * @package JosKolenberg\LaravelJory\Tests\Models
 */
class Groupie extends Model
{
    protected $table = 'groupies';

    protected $casts = [
        'id' => 'integer',
    ];

    public function person()
    {
        return $this->belongsTo(Person::class);
    }
}
