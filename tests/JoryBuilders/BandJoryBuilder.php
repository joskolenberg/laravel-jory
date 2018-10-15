<?php

namespace JosKolenberg\LaravelJory\Tests\JoryBuilders;


use JosKolenberg\Jory\Support\Filter;
use JosKolenberg\LaravelJory\CustomJoryBuilder;

class BandJoryBuilder extends CustomJoryBuilder
{

    protected function applyNumberOfAlbumsInYearFilter($query, Filter $filter)
    {
        $data = $filter->getValue();
        $year = $data['year'];
        $value = $data['value'];

        $query->whereHas('albums', function($query) use ($year){
            $query->where('release_date', '>=', $year . '-01-01');
            $query->where('release_date', '<=', $year . '-12-31');
        }, $filter->getOperator(), $value);
    }

}