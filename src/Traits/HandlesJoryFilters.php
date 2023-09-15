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
     * @param mixed $builder
     * @param JoryResource $joryResource
     */
    public function applyFilter($builder, JoryResource $joryResource): void
    {
        $this->doApplyFilter($builder, $joryResource->getJory()->getFilter(), $joryResource);
    }

    /**
     * Apply a filter (field, groupAnd or groupOr) on a query.
     *
     * Although it seems like we can retrieve the filter from the JoryResource
     * using joryResource->getJory()->getFilter(), this won't work. We
     * will be using the same joryResource for the subfilters as well
     * so we do have to supply them as two different parameters.
     *
     * @param mixed $builder
     * @param FilterInterface $filter
     * @param JoryResource $joryResource
     */
    public function doApplyFilter($builder, FilterInterface $filter, JoryResource $joryResource): void
    {
        if ($filter instanceof Filter) {
            $this->applyFieldFilter($builder, $filter, $joryResource);
        }
        if ($filter instanceof GroupAndFilter) {
            $builder->where(function ($builder) use ($joryResource, $filter) {
                foreach ($filter as $subFilter) {
                    $this->doApplyFilter($builder, $subFilter, $joryResource);
                }
            });
        }
        if ($filter instanceof GroupOrFilter) {
            $builder->where(function ($builder) use ($joryResource, $filter) {
                foreach ($filter as $subFilter) {
                    $builder->orWhere(function ($builder) use ($joryResource, $subFilter) {
                        $this->doApplyFilter($builder, $subFilter, $joryResource);
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
     * @param mixed $builder
     * @param Filter $filter
     * @param JoryResource $joryResource
     */
    public function applyFieldFilter($builder, Filter $filter, JoryResource $joryResource): void
    {
        $configuredFilter = $joryResource->getConfig()->getFilter($filter);

        /**
         * First check if there is a custom scope attached
         * to the filter. If so, apply that one.
         */
        $scope = $configuredFilter->getScope();
        if($scope){
            // Wrap in a where closure to encapsulate any OR clauses in custom method
            // which could lead to unexpected results.
            $builder->where(function ($builder) use ($joryResource, $filter, $scope) {
                $scope->apply($builder, $filter->getOperator(), $filter->getData());
            });
            return;
        }

        /**
         * When the field contains dots, we want to query on a relation
         * with the last part of the string being the field to filter on.
         */
        if(Str::contains($filter->getField(), '.')){
            $this->applyRelationFilter($builder, $filter, $configuredFilter);

            return;
        }

        /**
         * Always apply the filter on the table of the model which
         * is being queried even if a join is applied (e.g. when filtering
         * a belongsToMany relation), so we prefix the field with the table name.
         */
        $field = $builder->getModel()->getTable().'.'.$configuredFilter->getField();
        FilterHelper::applyWhere($builder, $field, $filter->getOperator(), $filter->getData());
    }

    /**
     * Apply a filter on a field in a relation
     * using relation1.relation2.etc.field notation.
     *
     * @param mixed $builder
     * @param Filter $filter
     * @return void
     */
    public function applyRelationFilter($builder, Filter $filter, \JosKolenberg\LaravelJory\Config\Filter $configuredFilter): void
    {
        $relations = explode('.', $configuredFilter->getField());

        $field = array_pop($relations);

        $relation = implode('.', $relations);

        $builder->whereHas($relation, function ($builder) use ($filter, $field) {
            FilterHelper::applyWhere($builder, $field, $filter->getOperator(), $filter->getData());
        });
    }

}