<?php


namespace JosKolenberg\LaravelJory\Tests\Scopes;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use JosKolenberg\LaravelJory\Scopes\FilterScope;

class NumberOfAlbumsInYearFilter implements FilterScope
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
        $year = $data['year'];
        $value = $data['value'];

        $builder->whereHas('albums', function ($builder) use ($year) {
            $builder->where('release_date', '>=', $year.'-01-01');
            $builder->where('release_date', '<=', $year.'-12-31');
        }, $operator, $value);
    }
}