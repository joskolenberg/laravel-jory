<?php


namespace JosKolenberg\LaravelJory\Helpers;


class FilterHelper
{

    /**
     * Extend Laravel's default "where operators" with is_null, not_null etc.
     *
     * @param mixed $builder
     * @param $field
     * @param $operator
     * @param $data
     */
    public static function applyWhere($builder, $field, $operator, $data): void
    {
        switch ($operator) {
            case 'is_null':
                $builder->whereNull($field);

                return;
            case 'not_null':
                $builder->whereNotNull($field);

                return;
            case 'in':
                $builder->whereIn($field, $data);

                return;
            case 'not_in':
                $builder->whereNotIn($field, $data);

                return;
            case 'not_like':
                $builder->where($field, 'not like', $data);

                return;
            default:
                $builder->where($field, $operator ?: '=', $data);
        }
    }

}