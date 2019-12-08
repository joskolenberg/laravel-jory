<?php

namespace JosKolenberg\LaravelJory\Traits;

use Illuminate\Support\Str;
use JosKolenberg\Jory\Support\Sort;
use JosKolenberg\LaravelJory\JoryResource;

trait HandlesJorySorts
{
    /**
     * Apply an array of sorts on the query.
     *
     * @param $builder
     * @param JoryResource $joryResource
     */
    protected function applySorts($builder, JoryResource $joryResource): void
    {
        foreach ($joryResource->getJory()->getSorts() as $sort) {
            $this->applySort($builder, $sort, $joryResource);
        }
    }

    /**
     * Apply a single sort on a query.
     *
     * @param $builder
     * @param Sort $sort
     * @param JoryResource $joryResource
     */
    protected function applySort($builder, Sort $sort, JoryResource $joryResource): void
    {
        $configuredSort = $joryResource->getConfig()->getSort($sort);

        /**
         * First check if there is a custom scope attached
         * to the sort. If so, apply that one.
         */
        $scope = $configuredSort->getScope();
        if($scope){
            $scope->apply($builder, $sort->getOrder());
            return;
        }

        // Always apply the sort on the table of the model which
        // is being queried even if a join is applied (e.g. when filtering
        // a belongsToMany relation), so we prefix the field with the table name.
        $field = $builder->getModel()->getTable().'.'.$configuredSort->getField();
        $this->applyDefaultSort($builder, $field, $sort->getOrder());
    }

    /**
     * Apply a sort to a field with default options.
     *
     * @param $builder
     * @param string $field
     * @param string $order
     */
    protected function applyDefaultSort($builder, string $field, string $order): void
    {
        $builder->orderBy($field, $order);
    }
}