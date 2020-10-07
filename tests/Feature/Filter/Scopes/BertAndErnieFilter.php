<?php


namespace JosKolenberg\LaravelJory\Tests\Feature\Filter\Scopes;


use JosKolenberg\LaravelJory\Helpers\FilterHelper;
use JosKolenberg\LaravelJory\Scopes\FilterScope;

class BertAndErnieFilter implements FilterScope
{

    public function apply($builder, string $operator = null, $data = null): void
    {
        $builder->where('name', 'Bert')
            ->orWhere('name', 'Ernie');
    }
}