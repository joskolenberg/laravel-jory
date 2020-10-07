<?php


namespace JosKolenberg\LaravelJory\Tests\Feature\Filter\Scopes;


use JosKolenberg\LaravelJory\Helpers\FilterHelper;
use JosKolenberg\LaravelJory\Scopes\FilterScope;

class HasUserWithNameFilter implements FilterScope
{

    public function apply($builder, string $operator = null, $data = null): void
    {
        $builder->whereHas('users', function ($builder) use ($operator, $data) {
            FilterHelper::applyWhere($builder, 'name', $operator, $data);
        });
    }
}