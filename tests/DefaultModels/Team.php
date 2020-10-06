<?php

namespace JosKolenberg\LaravelJory\Tests\DefaultModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use JosKolenberg\LaravelJory\Tests\Factories\TeamFactory;

class Team extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected static function newFactory()
    {
        return new TeamFactory();
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
