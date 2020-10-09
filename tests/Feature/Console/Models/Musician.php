<?php

namespace JosKolenberg\LaravelJory\Tests\Feature\Console\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Musician extends Model
{
    use HasFactory;

    public $timestamps = false;

    public function bands()
    {
        return $this->belongsToMany(Band::class, 'band_members');
    }
}
