<?php

namespace JosKolenberg\LaravelJory\Traits;

use Illuminate\Support\Str;
use JosKolenberg\Jory\Support\Filter;
use JosKolenberg\Jory\Support\GroupOrFilter;
use JosKolenberg\Jory\Support\GroupAndFilter;
use JosKolenberg\Jory\Contracts\FilterInterface;
use JosKolenberg\LaravelJory\Helpers\CaseManager;
use JosKolenberg\LaravelJory\Helpers\FilterHelper;

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
        if (method_exists($this->joryResource, $customMethodName)) {
            // Wrap in a where closure to encapsulate any OR clauses in custom method
            // which could lead to unexpected results.
            $query->where(function ($query) use ($filter, $customMethodName) {
                $this->joryResource->$customMethodName($query, $filter->getOperator(), $filter->getData());
            });

            return;
        }

        $model = $query->getModel();
        if (method_exists($model, $customMethodName)) {
            // Wrap in a where closure to encapsulate any OR clauses in custom method
            // which could lead to unexpected results.
            $query->where(function ($query) use ($model, $filter, $customMethodName) {
                $model->$customMethodName($query, $filter->getOperator(), $filter->getData());
            });

            return;
        }

        /**
         * When the field contains dots, we want to query on a relation
         * with the last part of the string being the field to filter on.
         */
        if(Str::contains($filter->getField(), '.')){
            $this->applyRelationFilter($query, $filter);

            return;
        }

        /**
         * Always apply the filter on the table of the model which
         * is being queried even if a join is applied (e.g. when filtering
         * a belongsToMany relation), so we prefix the field with the table name.
         */
        $field = $query->getModel()->getTable().'.'.Str::snake($filter->getField());
        FilterHelper::applyWhere($query, $field, $filter->getOperator(), $filter->getData());
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
        return 'scope'.Str::studly($filter->getField()).'Filter';
    }

    /**
     * Apply a filter on a field in a relation
     * using relation1.relation2.etc.field notation.
     *
     * @param mixed $query
     * @param Filter $filter
     */
    protected function applyRelationFilter($query, Filter $filter)
    {
        $relations = explode('.', $filter->getField());

        $field = array_pop($relations);

        $relation = implode('.', $relations);

        $query->whereHas($relation, function ($query) use ($filter, $field) {
            FilterHelper::applyWhere($query, $field, $filter->getOperator(), $filter->getData());
        });
    }

}