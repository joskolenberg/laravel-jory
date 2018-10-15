<?php

namespace JosKolenberg\LaravelJory;

use Illuminate\Database\Eloquent\Builder;
use JosKolenberg\Jory\Support\Filter;

/**
 * Generic class to query models based on Jory data.
 * Extend this class to add custom functionality to process jory queries.
 *
 * Class GenericJoryBuilder
 */
abstract class CustomJoryBuilder extends AbstractJoryBuilder
{
    /**
     * Run the custom filter method if it is available.
     * If not, run the standard filter method in the parent.
     *
     * @param Builder $query
     * @param Filter  $filter
     */
    protected function applyFieldFilter($query, Filter $filter): void
    {
        $customMethodName = $this->getCustomFilterMethodName($filter);
        if (method_exists($this, $customMethodName)) {
            $this->$customMethodName($query, $filter);

            return;
        }

        parent::applyFieldFilter($query, $filter);
    }

    /**
     * Get the custom method name to look for to apply a filter.
     *
     * @param Filter $filter
     *
     * @return string
     */
    protected function getCustomFilterMethodName(Filter $filter)
    {
        return 'apply'.studly_case($filter->getField()).'Filter';
    }
}
