<?php


namespace JosKolenberg\LaravelJory\Tests\Unit\Base;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use JosKolenberg\LaravelJory\Helpers\FilterHelper;
use JosKolenberg\LaravelJory\Scopes\FilterScope;

class UserNameFilter implements FilterScope
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
        FilterHelper::applyWhere($builder, 'name', $operator, $data);
        $builder->whereHas('team', function($builder){
            $builder->where('name', 'Sesame Street');
        });
    }
}