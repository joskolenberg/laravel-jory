<?php

namespace JosKolenberg\LaravelJory\Tests\DefaultModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use JosKolenberg\LaravelJory\Tests\Factories\TagFactory;

class Tag extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected static function newFactory()
    {
        return new TagFactory();
    }

    public function bands()
    {
        return $this->morphedByMany(Band::class, 'taggable');
    }
}
