<?php

namespace JosKolenberg\LaravelJory\Tests\Models;

class Image extends Model
{
    protected $table = 'images';

    protected $casts = [
        'id' => 'integer',
        'imageable_id' => 'integer',
    ];

    /**
     * Get the owning imageable model.
     */
    public function imageable()
    {
        return $this->morphTo();
    }
}
