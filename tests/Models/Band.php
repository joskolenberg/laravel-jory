<?php

namespace JosKolenberg\LaravelJory\Tests\Models;

class Band extends Model
{

    public function members()
    {
        return $this->belongsToMany(Person::class, 'band_members');
    }

}