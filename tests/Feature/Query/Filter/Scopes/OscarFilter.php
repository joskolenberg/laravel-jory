<?php


namespace JosKolenberg\LaravelJory\Tests\Feature\Query\Filter\Scopes;


use JosKolenberg\LaravelJory\Scopes\FilterScope;

class OscarFilter implements FilterScope
{

    public function apply($builder, string $operator = null, $data = null): void
    {
        $builder->where('name', 'Oscar');
    }
}