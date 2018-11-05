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
use JosKolenberg\LaravelJory\Config\Config;
use Illuminate\Database\Eloquent\Collection;
use JosKolenberg\Jory\Support\GroupOrFilter;
use Illuminate\Contracts\Support\Responsable;
use JosKolenberg\Jory\Support\GroupAndFilter;
use JosKolenberg\LaravelJory\Config\Validator;
use JosKolenberg\Jory\Exceptions\JoryException;
use JosKolenberg\Jory\Contracts\FilterInterface;
use JosKolenberg\LaravelJory\Parsers\RequestParser;
use JosKolenberg\Jory\Contracts\JoryParserInterface;
use JosKolenberg\LaravelJory\Routes\BuildsJoryRoutes;
use JosKolenberg\LaravelJory\Exceptions\LaravelJoryException;
use JosKolenberg\LaravelJory\Exceptions\LaravelJoryCallException;

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
     * @var null|Jory
     */
    protected $jory = null;

    /**
     * @var bool
     */
    protected $first = false;

    /**
     * @var Model|null
     */
    protected $model = null;

    /**
     * @var bool
     */
    protected $count = false;

    /**
     * @var null|JoryParserInterface
     */
    protected $joryParser = null;

    /**
     * @var Config|null
     */
    protected $config = null;

    /**
     * JoryBuilder constructor.
     */
    public function __construct()
    {
        // Create the config based on the settings in config()
        $this->config = new Config();
        $this->config($this->config);
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
        $this->joryParser = new ArrayParser($array);

        return $this;
    }

    /**
     * Apply a Json string with Jory data.
     *
     * @param string $json
     *
     * @return JoryBuilder
     */
    public function applyJson(string $json): self
    {
        $this->joryParser = new JsonParser($json);

        return $this;
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
        $this->joryParser = new RequestParser($request);

        return $this;
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
     * @throws LaravelJoryException
     * @throws LaravelJoryCallException
     */
    public function get(): Collection
    {
        $collection = $this->buildQuery()->get();

        $jory = $this->getJory();
        $collection = $this->afterFetch($collection, $jory);

        $this->loadRelations($collection, $jory->getRelations());

        return $collection;
    }

    /**
     * Get the first Model based on the baseQuery and Jory data.
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     * @throws LaravelJoryCallException
     * @throws LaravelJoryException
     */
    public function getFirst(): ?Model
    {
        $model = $this->model;

        if (! $model) {
            $model = $this->buildQuery()->first();
        }

        if (! $model) {
            return null;
        }

        $this->loadRelations(new Collection([$model]), $this->getJory()->getRelations());

        return $model;
    }

    /**
     * Count the records based on the filters in the Jory object.
     *
     * @return int
     * @throws LaravelJoryException
     */
    public function getCount(): int
    {
        $query = clone $this->builder;

        $jory = $this->getJory();

        $this->beforeQueryBuild($query, $jory, true);

        // Apply filters if there are any
        if ($jory->getFilter()) {
            $this->applyFilter($query, $jory->getFilter());
        }

        $this->afterQueryBuild($query, $jory, true);

        return $query->count();
    }

    /**
     * Get the result array.
     *
     * @return array|null
     * @throws LaravelJoryException
     * @throws LaravelJoryCallException
     * @throws JoryException
     */
    public function toArray(): ?array
    {
        $jory = $this->getJoryForArrayExport();

        if ($this->first) {
            $model = $this->getFirst();
            if (! $model) {
                return null;
            }

            return $model->toArrayByJory($jory);
        }

        $models = $this->get();

        $result = [];
        foreach ($models as $model) {
            $result[] = $model->toArrayByJory($jory);
        }

        return $result;
    }

    /**
     * Build a new query based on the baseQuery and Jory data.
     *
     * @return Builder
     * @throws LaravelJoryCallException
     * @throws LaravelJoryException
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
        if (method_exists($this, $customMethodName)) {
            $this->$customMethodName($query, $filter->getOperator(), $filter->getData());

            return;
        }

        $model = $query->getModel();
        if (method_exists($model, $customMethodName)) {
            $model->$customMethodName($query, $filter->getOperator(), $filter->getData());

            return;
        }

        // Always apply the filter on the table of the model which
        // is being queried even if a join is applied (e.g. when filtering
        // a belongsToMany relation), so we prefix the field with the table name.
        $field = $query->getModel()->getTable().'.'.$filter->getField();
        $this->applyDefaultFieldFilter($query, $field, $filter->getOperator(), $filter->getData());
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
     * @throws LaravelJoryException
     */
    public function toResponse($request)
    {
        try {
            $this->validate();
            $data = $this->count ? $this->getCount() : $this->toArray();
        } catch (JoryException $e) {
            return response([
                'errors' => [
                    $e->getMessage(),
                ],
            ], 422);
        } catch (LaravelJoryCallException $e) {
            $responseKey = $this->getErrorResponseKey();
            $response = $responseKey === null ? $e->getErrors() : [$responseKey => $e->getErrors()];

            return response($response, 422);
        }

        $responseKey = $this->getDataResponseKey();
        $response = $responseKey === null ? $data : [$responseKey => $data];

        return response($response);
    }

    /**
     * Do apply a filter to a field with default options.
     *
     * Prefixed with 'do' to prevent clashing if a custom filter named 'default_field' should exist.
     *
     * @param mixed $query
     * @param $field
     * @param $operator
     * @param $data
     */
    protected function applyDefaultFieldFilter($query, $field, $operator, $data): void
    {
        switch ($operator) {
            case 'null':
                $query->whereNull($field);

                return;
            case 'not_null':
                $query->whereNotNull($field);

                return;
            case 'in':
                $query->whereIn($field, $data);

                return;
            case 'not_in':
                $query->whereNotIn($field, $data);

                return;
            default:
                $query->where($field, $operator ?: '=', $data);
        }
    }

    /**
     * Apply the jory data on an existing query.
     *
     * @param $query
     * @throws LaravelJoryCallException
     * @throws LaravelJoryException
     */
    public function applyOnQuery($query): void
    {
        $jory = $this->getJory();
        $this->beforeQueryBuild($query, $jory);

        // Apply filters if there are any
        if ($jory->getFilter()) {
            $this->applyFilter($query, $jory->getFilter());
        }
        $this->applySorts($query, $jory->getSorts());
        $this->applyOffsetAndLimit($query, $jory->getOffset(), $jory->getLimit());

        $this->afterQueryBuild($query, $jory);
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

        $relationName = camel_case($relation->getName());

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

            if ($related === null) {
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
            $this->$customMethodName($query, $sort->getOrder());

            return;
        }

        $model = $query->getModel();
        if (method_exists($model, $customMethodName)) {
            $model->$customMethodName($query, $sort->getOrder());

            return;
        }

        // Always apply the sort on the table of the model which
        // is being queried even if a join is applied (e.g. when filtering
        // a belongsToMany relation), so we prefix the field with the table name.
        $field = $query->getModel()->getTable().'.'.$sort->getField();
        $this->applyDefaultSort($query, $field, $sort->getOrder());
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
     */
    protected function applyOffsetAndLimit($query, int $offset = null, int $limit = null): void
    {
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
     * Set the builder to return the record count instead of the records.
     *
     * @return JoryBuilder
     */
    public function count(): self
    {
        $this->count = true;

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
     * @param bool $count
     */
    protected function beforeQueryBuild($query, Jory $jory, $count = false)
    {
        if (! $count) {
            $this->selectOnlyRootTable($query);
            if ($this->config->getLimitDefault() !== null) {
                $query->limit($this->config->getLimitDefault());
            }
        }
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
     * @param bool $count
     */
    protected function afterQueryBuild($query, Jory $jory, $count = false)
    {
        if (! $count) {
            $this->applyDefaultSortsFromConfig($query);
        }
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

    /**
     * Get the jory object which needs to be applied.
     *
     * @return Jory
     * @throws LaravelJoryException
     */
    protected function getJory(): Jory
    {
        // If instance already is set return this one.
        if ($this->jory) {
            return $this->jory;
        }

        // If a parser has been set return the one from the parser
        if ($this->joryParser) {
            $jory = $this->joryParser->getJory();

            $this->jory = $jory;

            return $this->jory;
        }

        throw new LaravelJoryException('No jorydata has been set on JoryBuilder.');
    }

    /**
     * Alter the query to select only the columns of
     * the model which is being queried.
     *
     * @param $query
     */
    protected function selectOnlyRootTable($query): void
    {
        $table = $query->getModel()->getTable();
        $query->select($table.'.*');
    }

    /**
     * Create the config for this builder.
     *
     * This config will be used to:
     *      - Show the options for the resource when using the OPTIONS http method
     *      - Fields:
     *          - Validate if the requested fields are available.
     *          - Update the Jory's fields attribute with the ones marked to be shown by default
     *              when no particular fields are requested.
     *
     * @param Config $config
     */
    protected function config(Config $config): void
    {

    }

    /**
     * Validate the Jory object by the settings in the Config.
     *
     * @throws LaravelJoryCallException
     * @throws LaravelJoryException
     */
    protected function validate(): void
    {
        (new Validator($this->config, $this->getJory()))->validate();
    }

    /**
     * Get the Config.
     *
     * @return Config
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * Get a new Jory object with only fields and relations based on
     * the original Jory in the request and the JoryBuilder's config.
     *
     * This new Jory will be used to export the models to arrays.
     *
     * @return \JosKolenberg\Jory\Jory
     * @throws \JosKolenberg\Jory\Exceptions\JoryException
     * @throws \JosKolenberg\LaravelJory\Exceptions\LaravelJoryException
     */
    protected function getJoryForArrayExport()
    {
        $jory = new Jory();

        $originalJory = $this->getJory();

        if ($originalJory->getFields() !== null) {
            // There are fields specified in the request, use these
            $jory->setFields($originalJory->getFields());
        } elseif ($this->config->getFields() !== null) {
            // No fields set in the request, but there are fields
            // specified in the config, than we will update the fields
            // with the ones to be shown by default.
            $defaultFields = [];
            foreach ($this->config->getFields() as $field) {
                if ($field->isShownByDefault()) {
                    $defaultFields[] = $field->getField();
                }
                $jory->setFields($defaultFields);
            }
        } else {
            // No fields set in request or config.
            // No action needed, the full model's toArray() result will be returned during export.
        }

        // Apply relations
        $baseModel = $this->builder->getModel();
        foreach ($originalJory->getRelations() as $originalRelation) {
            $relationName = camel_case($originalRelation->getName());
            $relatedModel = $baseModel->$relationName()->getModel();
            $relatedJoryBuilder = $relatedModel::jory();
            $relatedJoryBuilder->applyJory($originalRelation->getJory());

            $jory->addRelation(new Relation($originalRelation->getName(), $relatedJoryBuilder->getJoryForArrayExport()));
        }

        return $jory;
    }

    /**
     * Apply any the sorts marked as default in the config on the query.
     *
     * @param $query
     * @throws \JosKolenberg\Jory\Exceptions\JoryException
     */
    protected function applyDefaultSortsFromConfig($query): void
    {
        $defaultSorts = [];
        if ($this->config->getSorts() !== null) {
            foreach ($this->config->getSorts() as $sort) {
                if ($sort->getDefaultIndex() !== null) {
                    $defaultSorts[$sort->getDefaultIndex()] = new Sort($sort->getField(), $sort->getDefaultOrder());
                }
            }
            ksort($defaultSorts);
            foreach ($defaultSorts as $sort) {
                $this->applySort($query, $sort);
            }
        }
    }

    /**
     * Get the key on which data should be returned.
     *
     * @return null|string
     */
    protected function getDataResponseKey()
    {
        return config('jory.response.data-key');
    }

    /**
     * Get the key on which errors should be returned.
     *
     * @return null|string
     */
    protected function getErrorResponseKey()
    {
        return config('jory.response.errors-key');
    }
}
