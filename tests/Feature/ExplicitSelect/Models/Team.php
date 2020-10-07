<?php

namespace JosKolenberg\LaravelJory\Tests\Feature\ExplicitSelect\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use JosKolenberg\LaravelJory\Tests\DefaultModels\User;
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

    public function firstUser()
    {
        return $this->hasOne(User::class);
    }
}
