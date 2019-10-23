<?php

namespace JosKolenberg\LaravelJory\Traits;

use Illuminate\Support\Str;
use JosKolenberg\Jory\Contracts\FilterInterface;
use JosKolenberg\Jory\Support\Filter;
use JosKolenberg\Jory\Support\GroupAndFilter;
use JosKolenberg\Jory\Support\GroupOrFilter;
use JosKolenberg\LaravelJory\Helpers\FilterHelper;
use JosKolenberg\LaravelJory\JoryResource;

trait HandlesJoryFilters
{
    /**
     * Apply the main filter in the Jory query (in the joryResource).
     *
     * This methods starts at the top layer of the filter and uses
     * doApplyFilter to go through any sublayers recursively.
     *
     * @param mixed $query
     * @param JoryResource $joryResource
     */
    protected function applyFilter($query, JoryResource $joryResource): void
    {
        $this->doApplyFilter($query, $joryResource->getJory()->getFilter(), $joryResource);
    }

    /**
     * Apply a filter (field, groupAnd or groupOr) on a query.
     *
     * Although it seems like we can retrieve the filter from the JoryResource
     * using joryResource->getJory()->getFilter(), this won't work. We
     * will be using the same joryResource for the subfilters as well
     * so we do have to supply them as two different parameters.
     *
     * @param mixed $query
     * @param FilterInterface $filter
     * @param JoryResource $joryResource
     */
    protected function doApplyFilter($query, FilterInterface $filter, JoryResource $joryResource): void
    {
        if ($filter instanceof Filter) {
            $this->applyFieldFilter($query, $filter, $joryResource);
        }
        if ($filter instanceof GroupAndFilter) {
            $query->where(function ($query) use ($joryResource, $filter) {
                foreach ($filter as $subFilter) {
                    $this->doApplyFilter($query, $subFilter, $joryResource);
                }
            });
        }
        if ($filter instanceof GroupOrFilter) {
            $query->where(function ($query) use ($joryResource, $filter) {
                foreach ($filter as $subFilter) {
                    $query->orWhere(function ($query) use ($joryResource, $subFilter) {
                        $this->doApplyFilter($query, $subFilter, $joryResource);
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
     * @param JoryResource $joryResource
     */
    protected function applyFieldFilter($query, Filter $filter, JoryResource $joryResource): void
    {
        // First check if there is a custom scope attached to the filter
        $scope = $joryResource->getConfig()->getFilter($filter->getField())->getScope();
        if($scope){
            $scope->apply($query, $filter->getOperator(), $filter->getData());
            return;
        }

        $customMethodName = $this->getCustomFilterMethodName($filter);
        // Check if the JoryResource has a custom scope method for this filter
        if (method_exists($joryResource, $customMethodName)) {
            // Wrap in a where closure to encapsulate any OR clauses in custom method
            // which could lead to unexpected results.
            $query->where(function ($query) use ($joryResource, $filter, $customMethodName) {
                $joryResource->$customMethodName($query, $filter->getOperator(), $filter->getData());
            });

            return;
        }

        $model = $query->getModel();
        // Check if the Model has a custom method for this filter
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
        $relations = explode('.', Str::snake($filter->getField()));

        $field = array_pop($relations);

        $relation = Str::camel(implode('.', $relations));

        $query->whereHas($relation, function ($query) use ($filter, $field) {
            FilterHelper::applyWhere($query, $field, $filter->getOperator(), $filter->getData());
        });
    }

}