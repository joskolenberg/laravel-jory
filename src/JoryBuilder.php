<?php

namespace JosKolenberg\LaravelJory;

use JosKolenberg\Jory\Jory;
use Illuminate\Http\Request;
use JosKolenberg\Jory\Support\Sort;
use JosKolenberg\Jory\Support\Filter;
use Illuminate\Database\Eloquent\Model;
use JosKolenberg\Jory\Support\Relation;
use Illuminate\Database\Eloquent\Builder;
use JosKolenberg\Jory\Parsers\JsonParser;
use JosKolenberg\Jory\Parsers\ArrayParser;
use Illuminate\Database\Eloquent\Collection;
use JosKolenberg\Jory\Support\GroupOrFilter;
use Illuminate\Contracts\Support\Responsable;
use JosKolenberg\Jory\Support\GroupAndFilter;
use JosKolenberg\Jory\Contracts\FilterInterface;
use JosKolenberg\LaravelJory\Parsers\RequestParser;
use JosKolenberg\LaravelJory\Routes\BuildsJoryRoutes;
use JosKolenberg\LaravelJory\Exceptions\LaravelJoryException;

/**
 * Class to query models based on Jory data.
 *
 * Class JoryBuilder
 */
class JoryBuilder implements Responsable
{
    use BuildsJoryRoutes;

    /**
     * @var Builder
     */
    protected $builder;

    /**
     * @var Jory
     */
    protected $jory;

    /**
     * @var bool
     */
    protected $first = false;

    /**
     * @var Model|null
     */
    protected $model = null;

    /**
     * JoryBuilder constructor.
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
     * @return JoryBuilder
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
     * @return JoryBuilder
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
     * @return JoryBuilder
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
     * @return JoryBuilder
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
     * @return JoryBuilder
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
     * Get the first Model based on the baseQuery and Jory data.
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function getFirst(): ? Model
    {
        $model = $this->model;

        if (! $model) {
            $model = $this->getQuery()->first();
        }

        if (! $model) {
            return null;
        }

        $this->loadRelations(new Collection([$model]), $this->jory->getRelations());

        return $model;
    }

    /**
     * Get the result array.
     *
     * @return array|null
     */
    public function toArray(): ? array
    {
        if ($this->first) {
            $model = $this->getFirst();
            if (! $model) {
                return null;
            }

            return $model->toArrayByJory($this->jory);
        }

        $models = $this->get();

        $result = [];
        foreach ($models as $model) {
            $result[] = $model->toArrayByJory($this->jory);
        }

        return $result;
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

        $this->applySorts($query, $this->jory->getSorts());
        $this->applyOffsetAndLimit($query, $this->jory->getOffset(), $this->jory->getLimit());

        return $query;
    }

    /**
     * Apply a filter (field, groupAnd or groupOr) on a query.
     *
     * @param mixed $query
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
     * Use custom filter method if available.
     * If not, run the default filter method..
     *
     * @param Builder $query
     * @param Filter $filter
     */
    protected function applyFieldFilter($query, Filter $filter): void
    {
        $customMethodName = $this->getCustomFilterMethodName($filter);
        if (method_exists($this, $customMethodName)) {
            $this->$customMethodName($query, $filter);

            return;
        }

        $this->doApplyDefaultFieldFilter($query, $filter);
    }

    /**
     * Get the custom method name to look for to apply a filter.
     *
     * @param Filter $filter
     *
     * @return string
     */
    protected function getCustomFilterMethodName(Filter $filter)
    {
        return 'apply'.studly_case($filter->getField()).'Filter';
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
        return response($this->toArray());
    }

    /**
     * Do apply a filter to a field with default options.
     *
     * Prefixed with 'do' to prevent clashing if a custom filter named 'default_field' should exist.
     *
     * @param mixed $query
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
        $this->applySorts($query, $this->jory->getSorts());
        $this->applyOffsetAndLimit($query, $this->jory->getOffset(), $this->jory->getLimit());
    }

    /**
     * Load the given relations on the given model(s).
     *
     * @param Collection $models
     * @param array $relations
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
     * @param Relation $relation
     */
    protected function loadRelation(Collection $collection, Relation $relation): void
    {
        if ($collection->isEmpty()) {
            return;
        }

        $relationName = $relation->getName();

        $collection->load([
            $relationName => function ($query) use ($relation) {
                // Retrieve the model which will be queried to get the appropriate JoryBuilder
                $relatedModel = $query->getRelated();
                $joryBuilder = $relatedModel::getJoryBuilder();

                // Apply the data in the subjory (filtering/sorting/...) on the query
                $joryBuilder->applyJory($relation->getJory());
                $joryBuilder->applyOnQuery($query);
            },
        ]);

        // Put all retrieved related models in single collection to load subrelations in a single call
        $allRelated = new Collection();
        foreach ($collection as $model) {
            $related = $model->$relationName;

            if ($related == null) {
                continue;
            }

            if ($related instanceof Model) {
                $allRelated->push($related);
            } else {
                $allRelated = $allRelated->merge($related);
            }
        }

        // Load the subrelations
        $this->loadRelations($allRelated, $relation->getJory()->getRelations());
    }

    /**
     * Apply an array of sorts on the query.
     *
     * @param $query
     * @param array $sorts
     */
    protected function applySorts($query, array $sorts): void
    {
        foreach ($sorts as $sort) {
            $this->applySort($query, $sort);
        }
    }

    /**
     * Apply a single sort on a query.
     *
     * @param $query
     * @param Sort $sort
     */
    protected function applySort($query, Sort $sort): void
    {
        $customMethodName = $this->getCustomSortMethodName($sort);
        if (method_exists($this, $customMethodName)) {
            $this->$customMethodName($query, $sort);

            return;
        }

        $this->doApplyDefaultSort($query, $sort);
    }

    /**
     * Do apply a sort to a field with default options.
     *
     * Prefixed with 'do' to prevent clashing if a custom filter named 'default_field' should exist.
     *
     * @param $query
     * @param Sort $sort
     */
    protected function doApplyDefaultSort($query, Sort $sort): void
    {
        $query->orderBy($sort->getField(), $sort->getOrder());
    }

    /**
     * Get the custom method name to look for to apply a sort.
     *
     * @param Sort $filter
     * @return string
     */
    protected function getCustomSortMethodName(Sort $filter): string
    {
        return 'apply'.studly_case($filter->getField()).'Sort';
    }

    /**
     * Apply an offset and limit on the query.
     *
     * @param $query
     * @param int|null $offset
     * @param int|null $limit
     * @throws LaravelJoryException
     */
    protected function applyOffsetAndLimit($query, int $offset = null, int $limit = null): void
    {
        // When setting an offset a limit is required in SQL
        if ($offset && ! $limit) {
            throw new LaravelJoryException('An offset cannot be set without a limit.');
        }
        if ($offset) {
            $query->offset($offset);
        }
        if ($limit) {
            $query->limit($limit);
        }
    }

    /**
     * Set this JoryBuilder to return only a single record.
     *
     * @return \JosKolenberg\LaravelJory\JoryBuilder
     */
    public function first(): JoryBuilder
    {
        $this->first = true;

        return $this;
    }

    /**
     * Set the model to query on.
     * This model will we the base result for this builder
     * with fields an relations as applied in the Jory.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return \JosKolenberg\LaravelJory\JoryBuilder
     */
    public function onModel(Model $model): JoryBuilder
    {
        $this->model = $model;
        $this->first();

        return $this;
    }
}
