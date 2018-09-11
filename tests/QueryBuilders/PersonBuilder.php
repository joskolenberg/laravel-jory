<?php
/**
 * Created by PhpStorm.
 * User: joskolenberg
 * Date: 05-09-18
 * Time: 22:23
 */

namespace JosKolenberg\LaravelJory\Tests\QueryBuilders;

use JosKolenberg\LaravelJory\QueryBuilder;
use JosKolenberg\Jory\Support\Filter;
use JosKolenberg\LaravelJory\Tests\Models\Person;

class PersonBuilder extends QueryBuilder
{

    protected function getBaseQuery()
    {
        return Person::query();
    }

    protected function applyCustomFieldFilter($query, Filter $filter)
    {
        $query->where('modified_field_name', $filter->getOperator(), $filter->getValue());
    }

    protected function applyInBeatlesFilter($query, Filter $filter)
    {
        $operator = $filter->getValue() ? '=' : '<>';
        $query->whereHas('bands', function ($query) use ($operator) {
            $query->where('name', $operator, 'Beatles');
        });
    }

//    protected function applyHasBandWithNumberOfBandmembersFilter($query, Filter $filter)
//    {
//        $query->has('bands.people', $filter->getOperator(), $filter->getValue());
//        $query->has('bands.people', $filter->getOperator(), $filter->getValue());
//        $operator = $filter->getValue() ? '=' : '<>';
//        $query->whereHas('bands', function ($query) use ($filter) {
//            $query->has('people', '=', 3);
//        });
//    }

}