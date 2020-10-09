<?php

namespace JosKolenberg\LaravelJory\Tests\DefaultModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use JosKolenberg\LaravelJory\Tests\Factories\ImageFactory;

class Image extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected static function newFactory()
    {
        return new ImageFactory();
    }
}
