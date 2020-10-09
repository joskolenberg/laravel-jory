<?php

namespace JosKolenberg\LaravelJory\Tests\Feature\Query\Fields\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use JosKolenberg\LaravelJory\Tests\DefaultModels\User;

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
