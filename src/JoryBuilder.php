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
        $collection = $this->buildQuery()->get();

        $collection = $this->afterFetch($collection, $this->jory);

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
            $model = $this->buildQuery()->first();
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
     * Build a new query based on the baseQuery and Jory data.
     *
     * @return Builder
     */
    protected function buildQuery(): Builder
    {
        $query = clone $this->builder;

        $this->applyOnQuery($query);

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
        $model = $query->getModel();
        if (method_exists($model, $customMethodName)) {
            $model->$customMethodName($query, $filter->getOperator(), $filter->getValue());

            return;
        }
        if (method_exists($this, $customMethodName)) {
            $this->$customMethodName($query, $filter->getOperator(), $filter->getValue());

            return;
        }

        $this->applyDefaultFieldFilter($query, $filter->getField(), $filter->getOperator(), $filter->getValue());
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
        return 'scope'.studly_case($filter->getField()).'Filter';
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
     * @param $field
     * @param $operator
     * @param $value
     */
    protected function applyDefaultFieldFilter($query, $field, $operator, $value): void
    {
        switch ($operator) {
            case 'null':
                $query->whereNull($field);

                return;
            case 'not_null':
                $query->whereNotNull($field);

                return;
            case 'in':
                $query->whereIn($field, $value);

                return;
            case 'not_in':
                $query->whereNotIn($field, $value);

                return;
            default:
                $query->where($field, $operator ?: '=', $value);
        }
    }

    /**
     * Apply the jory data on an existing query.
     *
     * @param $query
     */
    public function applyOnQuery($query): void
    {
        $this->beforeQueryBuild($query, $this->jory);

        // Apply filters if there are any
        if ($this->jory->getFilter()) {
            $this->applyFilter($query, $this->jory->getFilter());
        }
        $this->applySorts($query, $this->jory->getSorts());
        $this->applyOffsetAndLimit($query, $this->jory->getOffset(), $this->jory->getLimit());

        $this->afterQueryBuild($query, $this->jory);
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
        $model = $query->getModel();
        if (method_exists($model, $customMethodName)) {
            $model->$customMethodName($query, $sort->getOrder());

            return;
        }
        if (method_exists($this, $customMethodName)) {
            $this->$customMethodName($query, $sort->getOrder());

            return;
        }

        $this->applyDefaultSort($query, $sort->getField(), $sort->getOrder());
    }

    /**
     * Do apply a sort to a field with default options.
     *
     * Prefixed with 'do' to prevent clashing if a custom filter named 'default_field' should exist.
     *
     * @param $query
     * @param string $field
     * @param string $order
     */
    protected function applyDefaultSort($query, string $field, string $order): void
    {
        $query->orderBy($field, $order);
    }

    /**
     * Get the custom method name to look for to apply a sort.
     *
     * @param Sort $filter
     * @return string
     */
    protected function getCustomSortMethodName(Sort $filter): string
    {
        return 'scope'.studly_case($filter->getField()).'Sort';
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
        if ($offset != null && ! $limit != null) {
            throw new LaravelJoryException('An offset cannot be set without a limit.');
        }
        if ($offset !== null) {
            // Check on null, so even 0 will be applied.
            // In case a default is set in beforeQueryBuild()
            // this can be overruled by the request this way.
            $query->offset($offset);
        }
        if ($limit !== null) {
            $query->limit($limit);
        }
    }

    /**
     * Set this JoryBuilder to return only a single record.
     *
     * @return \JosKolenberg\LaravelJory\JoryBuilder
     */
    public function first(): self
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
    public function onModel(Model $model): self
    {
        $this->model = $model;
        $this->first();

        return $this;
    }

    /**
     * Hook into the query before all settings in Jory object are applied.
     *
     * Usage:
     *  - Filtering: Any filters set will be applied on the query.
     *  - Sorting: Any sorting applied here will have precedence over the ones requested.
     *  - Offset/Limit: An applied offset or limit will be overruled by the requested (only when requested).
     *  - Fields: All columns in the table will always be fetched even if not all fields are requested.
     *      (the fields are filtered later to have all fields available for any custom attributes relying on them)
     *      So altering the fields is discouraged unless you got a good reason to do so.
     *  - Relations: Relations are loaded using that model's JoryBuilder, so no use altering the query for that.
     *
     * @param $query
     * @param \JosKolenberg\Jory\Jory $jory
     */
    protected function beforeQueryBuild($query, Jory $jory)
    {

    }

    /**
     * Hook into the query after all settings in Jory object
     * are applied and just before the query is executed.
     *
     * Usage:
     *  - Filtering: Any filters set will be applied on the query.
     *  - Sorting: Any sorting applied here will be applied as last, so the requested sorting will
     *      have precedence over this one.
     *  - Offset/Limit: An offset or limit applied here will overrule the ones requested.
     *  - Fields: All columns in the table will always be fetched even if not all fields are requested.
     *      (the fields are filtered later to have all fields available for any custom attributes relying on them)
     *      So altering the fields is discouraged unless you got a good reason to do so.
     *  - Relations: Relations are loaded using that model's JoryBuilder, so no use altering the query for that.
     *
     * @param $query
     * @param \JosKolenberg\Jory\Jory $jory
     */
    protected function afterQueryBuild($query, Jory $jory)
    {

    }

    /**
     * Hook into the collection right after it is fetched.
     *
     * Here you can modify the collection before it is turned into an array.
     * E.g. 1. you could eager load some relations when you have some
     *      calculated values in custom attributes using relations.
     *      # if $jory->hasField('total_price') $collection->load('invoices');
     *      (any relations requested by the client could override these)
     *
     * E.g. 2. you could sort the collection in a way which is hard using queries
     *      but easier done using a collection. (Does not work with pagination).
     *
     * @param \Illuminate\Database\Eloquent\Collection $collection
     * @param \JosKolenberg\Jory\Jory $jory
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function afterFetch(Collection $collection, Jory $jory): Collection
    {
        return $collection;
    }
}
