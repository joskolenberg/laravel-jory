<?php

namespace JosKolenberg\LaravelJory\Tests\DefaultModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use JosKolenberg\LaravelJory\Tests\Factories\UserFactory;
use JosKolenberg\LaravelJory\Tests\Models\Person;

class User extends Authenticatable
{
    use Notifiable, HasFactory;

    public $timestamps = false;

    protected static function newFactory()
    {
        return new UserFactory();
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
