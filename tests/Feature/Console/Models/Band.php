<?php

namespace JosKolenberg\LaravelJory\Tests\Feature\Console\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Band extends Model
{
    use HasFactory;

    public $timestamps = false;

    public function musicians()
    {
        return $this->belongsToMany(Musician::class, 'band_members');
    }
}
