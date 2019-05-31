<?php


namespace JosKolenberg\LaravelJory\Helpers;


class FilterHelper
{

    /**
     * Extend Laravel's default "where operators" with is_null, not_null etc.
     *
     * @param mixed $query
     * @param $field
     * @param $operator
     * @param $data
     */
    public static function applyWhere($query, $field, $operator, $data): void
    {
        switch ($operator) {
            case 'is_null':
                $query->whereNull($field);

                return;
            case 'not_null':
                $query->whereNotNull($field);

                return;
            case 'in':
                $query->whereIn($field, $data);

                return;
            case 'not_in':
                $query->whereNotIn($field, $data);

                return;
            case 'not_like':
                $query->where($field, 'not like', $data);

                return;
            default:
                $query->where($field, $operator ?: '=', $data);
        }
    }

}