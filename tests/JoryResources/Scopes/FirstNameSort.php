<?php


namespace JosKolenberg\LaravelJory\Tests\JoryResources\Scopes;


use JosKolenberg\LaravelJory\Scopes\SortScope;

class FirstNameSort implements SortScope
{

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\Relation $builder
     * @param string $order
     * @return void
     */
    public function apply($builder, string $order = 'asc')
    {
        $builder->orderBy('first_name', $order);
    }
}