<?php

namespace JosKolenberg\LaravelJory\Tests\JoryBuilders;

use JosKolenberg\LaravelJory\JoryBuilder;

class BandJoryBuilder extends JoryBuilder
{
    protected function scopeNumberOfAlbumsInYearFilter($query, $operator, $value)
    {
        $data = $value;
        $year = $data['year'];
        $value = $data['value'];

        $query->whereHas('albums', function ($query) use ($year) {
            $query->where('release_date', '>=', $year.'-01-01');
            $query->where('release_date', '<=', $year.'-12-31');
        }, $operator, $value);
    }
}
