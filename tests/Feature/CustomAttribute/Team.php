<?php

namespace JosKolenberg\LaravelJory\Tests\Feature\CustomAttribute;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use JosKolenberg\LaravelJory\Tests\DefaultModels\User;
use JosKolenberg\LaravelJory\Tests\Factories\UserFactory;
use JosKolenberg\LaravelJory\Tests\Models\Person;

class Team extends Authenticatable
{
    use Notifiable, HasFactory;

    public $timestamps = false;

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function getUsersStringAttribute()
    {
        return $this->users->implode('name', ', ');
    }
}
