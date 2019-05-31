<?php

namespace JosKolenberg\LaravelJory\Traits;

use Illuminate\Support\Str;
use JosKolenberg\Jory\Support\Sort;
use JosKolenberg\LaravelJory\Helpers\CaseManager;

trait HandlesJorySorts
{
    /**
     * Apply an array of sorts on the query.
     *
     * @param $query
     * @param array $sorts
     */
    protected function applySorts($query, array $sorts): void
    {
        foreach ($sorts as $sort) {
            $this->applySort($query, $sort);
        }
    }

    /**
     * Apply a single sort on a query.
     *
     * @param $query
     * @param Sort $sort
     */
    protected function applySort($query, Sort $sort): void
    {
        $customMethodName = $this->getCustomSortMethodName($sort);
        if (method_exists($this->joryResource, $customMethodName)) {
            $this->joryResource->$customMethodName($query, $sort->getOrder());

            return;
        }

        $model = $query->getModel();
        if (method_exists($model, $customMethodName)) {
            $model->$customMethodName($query, $sort->getOrder());

            return;
        }

        // Always apply the sort on the table of the model which
        // is being queried even if a join is applied (e.g. when filtering
        // a belongsToMany relation), so we prefix the field with the table name.
        $field = $query->getModel()->getTable().'.'.Str::snake($sort->getField());
        $this->applyDefaultSort($query, $field, $sort->getOrder());
    }

    /**
     * Apply a sort to a field with default options.
     *
     * @param $query
     * @param string $field
     * @param string $order
     */
    protected function applyDefaultSort($query, string $field, string $order): void
    {
        $query->orderBy($field, $order);
    }

    /**
     * Get the custom method name to look for to apply a sort.
     *
     * @param Sort $sort
     * @return string
     */
    protected function getCustomSortMethodName(Sort $sort): string
    {
        return 'scope'.Str::studly($sort->getField()).'Sort';
    }
}