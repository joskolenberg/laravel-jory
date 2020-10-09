<?php

namespace JosKolenberg\LaravelJory\Tests\Feature\Console\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable, HasFactory;

    public $timestamps = false;

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function getEmailDomainAttribute()
    {
        return substr($this->email, 0, strrpos($this->email, '@'));
    }
}
