<?php

namespace JosKolenberg\LaravelJory\Traits;

use JosKolenberg\Jory\Support\Filter;
use JosKolenberg\Jory\Support\GroupOrFilter;
use JosKolenberg\Jory\Support\GroupAndFilter;
use JosKolenberg\Jory\Contracts\FilterInterface;
use JosKolenberg\LaravelJory\Helpers\CaseManager;

trait HandlesJoryFilters
{
    /**
     * Apply a filter (field, groupAnd or groupOr) on a query.
     *
     * @param mixed $query
     * @param FilterInterface $filter
     */
    protected function applyFilter($query, FilterInterface $filter): void
    {
        if ($filter instanceof Filter) {
            $this->applyFieldFilter($query, $filter);
        }
        if ($filter instanceof GroupAndFilter) {
            $query->where(function ($query) use ($filter) {
                foreach ($filter as $subFilter) {
                    $this->applyFilter($query, $subFilter);
                }
            });
        }
        if ($filter instanceof GroupOrFilter) {
            $query->where(function ($query) use ($filter) {
                foreach ($filter as $subFilter) {
                    $query->orWhere(function ($query) use ($subFilter) {
                        $this->applyFilter($query, $subFilter);
                    });
                }
            });
        }
    }

    /**
     * Apply a filter to a field.
     * Use custom filter method if available.
     * If not, run the default filter method..
     *
     * @param mixed $query
     * @param Filter $filter
     */
    protected function applyFieldFilter($query, Filter $filter): void
    {
        $customMethodName = $this->getCustomFilterMethodName($filter);
        if (method_exists($this, $customMethodName)) {
            $this->$customMethodName($query, $filter->getOperator(), $filter->getData());

            return;
        }

        $model = $query->getModel();
        if (method_exists($model, $customMethodName)) {
            $model->$customMethodName($query, $filter->getOperator(), $filter->getData());

            return;
        }

        // Always apply the filter on the table of the model which
        // is being queried even if a join is applied (e.g. when filtering
        // a belongsToMany relation), so we prefix the field with the table name.
        $field = $query->getModel()->getTable().'.'.(app(CaseManager::class)->isCamel() ? snake_case($filter->getField()) : $filter->getField());
        $this->applyDefaultFieldFilter($query, $field, $filter->getOperator(), $filter->getData());
    }

    /**
     * Get the custom method name to look for to apply a filter.
     *
     * @param Filter $filter
     *
     * @return string
     */
    protected function getCustomFilterMethodName(Filter $filter): string
    {
        return 'scope'.studly_case($filter->getField()).'Filter';
    }

    /**
     * Apply a filter to a field with default options.
     *
     * @param mixed $query
     * @param $field
     * @param $operator
     * @param $data
     */
    protected function applyDefaultFieldFilter($query, $field, $operator, $data): void
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
            default:
                $query->where($field, $operator ?: '=', $data);
        }
    }
}