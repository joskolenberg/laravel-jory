<?php


namespace JosKolenberg\LaravelJory\Tests\Scopes;


use JosKolenberg\LaravelJory\Scopes\SortScope;

class AlphabeticNameSort implements SortScope
{

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\Relation $builder
     * @param string $order
     * @return void
     */
    public function apply($builder, string $order = 'asc'): void
    {
        $builder->orderBy('name', $order);
    }
}