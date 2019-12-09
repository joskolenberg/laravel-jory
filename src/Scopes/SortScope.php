<?php

namespace JosKolenberg\LaravelJory\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

interface SortScope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param Builder|Relation $builder
     * @param string $order
     * @return void
     */
    public function apply($builder, string $order = 'asc'): void;
}
