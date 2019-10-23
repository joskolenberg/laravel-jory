<?php

namespace JosKolenberg\LaravelJory\Tests\JoryResources\Scopes;

use JosKolenberg\LaravelJory\Scopes\FilterScope;

class SpecialFirstNameFilter implements FilterScope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\Relation $builder
     * @param string $operator
     * @param mixed $data
     * @return void
     */
    public function apply($builder, string $operator = null, $data = null)
    {
        $builder->where('first_name', '=', 'John');
    }
}