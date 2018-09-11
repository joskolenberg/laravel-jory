<?php

namespace JosKolenberg\LaravelJory\Tests\Models;


class Person extends Model
{

    public function bands()
    {
        return $this->belongsToMany(Band::class, 'band_members');
    }

}