<?php

namespace JosKolenberg\LaravelJory;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use JosKolenberg\Jory\Contracts\FilterInterface;
use JosKolenberg\Jory\Jory;
use JosKolenberg\Jory\Parsers\ArrayParser;
use JosKolenberg\Jory\Parsers\JsonParser;
use JosKolenberg\Jory\Support\GroupAndFilter;
use JosKolenberg\Jory\Support\GroupOrFilter;
use JosKolenberg\LaravelJory\Parsers\RequestParser;
use JosKolenberg\Jory\Support\Filter;

abstract class QueryBuilder
{

    protected $jory;

    public function __construct(Jory $jory)
    {
        $this->jory = $jory;
    }

    public static function array(array $array)
    {
        return new static((new ArrayParser($array))->getJory());
    }

    public static function json(string $json)
    {
        return new static((new JsonParser($json))->getJory());
    }

    public static function request(Request $request)
    {
        return new static((new RequestParser($request))->getJory());
    }

    public function query()
    {
        return $this->buildQuery();
    }

    public function get()
    {
        return $this->query()->get();
    }

    protected function buildQuery()
    {
        $query = clone $this->getBaseQuery();

        $this->applyFilter($query, $this->jory->getFilter());

        return $query;
    }

    protected function applyFilter(Builder $query, FilterInterface $filter)
    {
        if ($filter instanceof Filter) {
            $customMethod = 'apply' . studly_case($filter->getField()) . 'Filter';
            $method = method_exists($this, $customMethod) ? $customMethod : 'doApplyDefaultFilter';
            $this->$method($query, $filter);
        }
        if($filter instanceof GroupAndFilter){
            $query->where(function ($query) use ($filter){
                foreach ($filter as $subFilter){
                    $this->applyFilter($query, $subFilter);
                }
            });
        }
        if($filter instanceof GroupOrFilter){
            $query->where(function ($query) use ($filter){
                foreach ($filter as $subFilter){
                    $query->orWhere(function ($query) use($subFilter){
                        $this->applyFilter($query, $subFilter);
                    });
                }
            });
        }
    }

    protected function doApplyDefaultFilter(Builder $query, Filter $filter)
    {
        $query->where($filter->getField(), $filter->getOperator(), $filter->getValue());
    }

    abstract protected function getBaseQuery();
}