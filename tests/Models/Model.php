<?php

namespace JosKolenberg\LaravelJory\Tests\Models;

class Model extends \Illuminate\Database\Eloquent\Model
{
    protected $guarded = ['id'];
    public $timestamps = false;
}
