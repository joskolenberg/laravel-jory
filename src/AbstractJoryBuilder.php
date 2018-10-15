<?php

namespace JosKolenberg\LaravelJory;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use JosKolenberg\Jory\Contracts\FilterInterface;
use JosKolenberg\Jory\Jory;
use JosKolenberg\Jory\Parsers\ArrayParser;
use JosKolenberg\Jory\Parsers\JsonParser;
use JosKolenberg\Jory\Support\Filter;
use JosKolenberg\Jory\Support\GroupAndFilter;
use JosKolenberg\Jory\Support\GroupOrFilter;
use JosKolenberg\Jory\Support\Relation;
use JosKolenberg\LaravelJory\Parsers\RequestParser;

/**
 * Class to query models based on Jory data.
 *
 * Class GenericJoryBuilder
 */
abstract class AbstractJoryBuilder implements Responsable
{
    /**
     * @var Builder
     */
    protected $builder;

    /**
     * @var Jory
     */
    protected $jory;

    /**
     * GenericJoryBuilder constructor.
     */
    public function __construct()
    {
        // Set to empty jory by default in case none is applied.
        $this->jory = new Jory();
    }

    /**
     * Set a builder instance to build the query upon.
     *
     * @param Builder $builder
     *
     * @return GenericJoryBuilder
     */
    public function onQuery(Builder $builder): self
    {
        $this->builder = $builder;

        return $this;
    }

    /**
     * Apply an array with Jory data.
     *
     * @param array $array
     *
     * @return GenericJoryBuilder
     */
    public function applyArray(array $array): self
    {
        return $this->applyJory((new ArrayParser($array))->getJory());
    }

    /**
     * Apply a Json string with Jory data.
     *
     * @param string $json
     *
     * @throws \JosKolenberg\Jory\Exceptions\JoryException
     *
     * @return GenericJoryBuilder
     */
    public function applyJson(string $json): self
    {
        return $this->applyJory((new JsonParser($json))->getJory());
    }

    /**
     * Apply a request with Jory data.
     *
     * @param Request $request
     *
     * @return GenericJoryBuilder
     */
    public function applyRequest(Request $request): self
    {
        return $this->applyJory((new RequestParser($request))->getJory());
    }

    /**
     * Apply a Jory object.
     *
     * @param Jory $jory
     *
     * @return GenericJoryBuilder
     */
    public function applyJory(Jory $jory): self
    {
        $this->jory = $jory;

        return $this;
    }

    /**
     * Get a collection of Models based on the baseQuery and Jory data.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function get(): Collection
    {
        $collection = $this->getQuery()->get();

        $this->loadRelations($collection, $this->jory->getRelations());

        return $collection;
    }

    /**
     * Get the Laravel QueryBuilder based on the baseQuery and Jory data.
     *
     * @return Builder
     */
    public function getQuery(): Builder
    {
        return $this->buildQuery();
    }

    /**
     * Build a new query based on the baseQuery and Jory data.
     *
     * @return Builder
     */
    protected function buildQuery(): Builder
    {
        $query = clone $this->builder;

        // Apply filters if there are any
        if ($this->jory->getFilter()) {
            $this->applyFilter($query, $this->jory->getFilter());
        }

        return $query;
    }

    /**
     * Apply a filter (field, groupAnd or groupOr) on a query.
     *
     * @param mixed           $query
     * @param FilterInterface $filter
     */
    protected function applyFilter($query, FilterInterface $filter): void
    {
        if ($filter instanceof Filter) {
            $this->applyFieldFilter($query, $filter);
        }
        if ($filter instanceof GroupAndFilter) {
            $query->where(function ($query) use ($filter) {
                foreach ($filter as $subFilter) {
                    $this->applyFilter($query, $subFilter);
                }
            });
        }
        if ($filter instanceof GroupOrFilter) {
            $query->where(function ($query) use ($filter) {
                foreach ($filter as $subFilter) {
                    $query->orWhere(function ($query) use ($subFilter) {
                        $this->applyFilter($query, $subFilter);
                    });
                }
            });
        }
    }

    /**
     * Apply a filter to a field.
     *
     * @param mixed  $query
     * @param Filter $filter
     */
    protected function applyFieldFilter($query, Filter $filter): void
    {
        // Run this through an extra function to allow child classes to override
        // this method and still be able to run the default filter function
        $this->doApplyDefaultFieldFilter($query, $filter);
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function toResponse($request)
    {
        return response($this->get());
    }

    /**
     * Do apply a filter to a field with default options.
     *
     * Prefixed with 'do' to prevent classing if a custom filter named 'default_field' should exist.
     *
     * @param mixed  $query
     * @param Filter $filter
     */
    protected function doApplyDefaultFieldFilter($query, Filter $filter): void
    {
        switch ($filter->getOperator()) {
            case 'null':
                $query->whereNull($filter->getField());

                return;
            case 'not_null':
                $query->whereNotNull($filter->getField());

                return;
            case 'in':
                $query->whereIn($filter->getField(), $filter->getValue());

                return;
            case 'not_in':
                $query->whereNotIn($filter->getField(), $filter->getValue());

                return;
            default:
                $query->where($filter->getField(), $filter->getOperator() ?: '=', $filter->getValue());
        }
    }

    /**
     * Apply the jory data on an existing query.
     *
     * @param $query
     */
    protected function applyOnQuery($query): void
    {
        // Apply filters if there are any
        if ($this->jory->getFilter()) {
            $this->applyFilter($query, $this->jory->getFilter());
        }
    }

    /**
     * Load the given relations on the given model(s).
     *
     * @param Collection $models
     * @param array      $relations
     */
    protected function loadRelations(Collection $models, array $relations): void
    {
        foreach ($relations as $relation) {
            $this->loadRelation($models, $relation);
        }
    }

    /**
     * Load the given relation on a collection of models.
     *
     * @param Collection $collection
     * @param Relation   $relation
     */
    protected function loadRelation(Collection $collection, Relation $relation): void
    {
        if ($collection->isEmpty()) {
            return;
        }

        $relationName = $relation->getRelation();

        $collection->load([$relationName => function ($query) use ($relation) {
            // Retrieve the model which will be queried to get the appropriate JoryBuilder
            $relatedModel = $query->getRelated();
            $joryBuilder = $relatedModel::getJoryBuilder();

            // Apply the data in the subjory (for filtering) on the query
            $joryBuilder->applyJory($relation->getJory());
            $joryBuilder->applyOnQuery($query);
        }]);

        // Put all retrieved related models in single collection to load subrelations in a single call
        $allRelated = new Collection();
        foreach ($collection as $model) {
            $related = $model->$relationName;
            if ($related instanceof Model) {
                $allRelated->push($related);
            } else {
                $allRelated = $allRelated->merge($related);
            }
        }

        // Load the subrelations
        $this->loadRelations($allRelated, $relation->getJory()->getRelations());
    }
}
