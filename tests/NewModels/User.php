<?php

namespace JosKolenberg\LaravelJory\Tests\NewModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use JosKolenberg\LaravelJory\Tests\Factories\UserFactory;

class User extends Authenticatable
{
    use Notifiable, HasFactory;

    protected $guarded = ['id'];

    public $timestamps = false;

    protected static function newFactory()
    {
        return new UserFactory();
    }
}
