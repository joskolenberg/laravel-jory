<?php


namespace JosKolenberg\LaravelJory\Tests\Unit\Base;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use JosKolenberg\LaravelJory\Scopes\FilterScope;

class NumberOfUsersFilter implements FilterScope
{

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param Builder|Relation $builder
     * @param string $operator
     * @param mixed $data
     * @return void
     */
    public function apply($builder, string $operator = null, $data = null): void
    {
        $builder->has('users', $operator, $data);
    }
}