<?php

namespace JosKolenberg\LaravelJory;

use JosKolenberg\Jory\Jory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
use JosKolenberg\LaravelJory\Helpers\CaseManager;
use JosKolenberg\LaravelJory\Parsers\RequestParser;
use JosKolenberg\Jory\Contracts\JoryParserInterface;
use JosKolenberg\LaravelJory\Routes\BuildsJoryRoutes;
use JosKolenberg\LaravelJory\Register\RegistersJoryBuilders;
use JosKolenberg\LaravelJory\Exceptions\LaravelJoryException;
use JosKolenberg\LaravelJory\Exceptions\LaravelJoryCallException;

/**
 * Class to query models based on Jory data.
 *
 * Class JoryBuilder
 */
class JoryBuilder implements Responsable
{
    use BuildsJoryRoutes, RegistersJoryBuilders;

    /**
     * @var string
     */
    protected $modelClass;

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
     * @var CaseManager
     */
    protected $case = null;

    /**
     * We only want to apply the settings in the config once.
     * We'll keep track of that by this flag.
     *
     * @var bool
     */
    protected $configHasBeenApplied = false;

    /**
     * JoryBuilder constructor.
     *
     * @param string $modelClass
     */
    public function __construct(string $modelClass)
    {
        $this->modelClass = $modelClass;

        $this->case = app(CaseManager::class);

        // Create the config based on the settings in config()
        $this->config = new Config($this->modelClass);
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
     * @throws \JosKolenberg\Jory\Exceptions\JoryException
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
     * @throws LaravelJoryException
     * @throws \JosKolenberg\Jory\Exceptions\JoryException
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

        $jory = $this->getJory();
        $model = $this->afterFetch(new Collection([$model]), $jory)->first();

        $this->loadRelations(new Collection([$model]), $this->getJory()->getRelations());

        return $model;
    }

    /**
     * Count the records based on the filters in the Jory object.
     *
     * @return int
     * @throws LaravelJoryException
     * @throws \JosKolenberg\Jory\Exceptions\JoryException
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
     * @throws \JosKolenberg\Jory\Exceptions\JoryException
     */
    public function toArray(): ?array
    {
        if ($this->first) {
            $model = $this->getFirst();
            if (! $model) {
                return null;
            }

            return $this->modelToArray($model);
        }

        $models = $this->get();

        $result = [];
        foreach ($models as $model) {
            $result[] = $this->modelToArray($model);
        }

        return $result;
    }

    /**
     * Build a new query based on the baseQuery and Jory data.
     *
     * @return Builder
     * @throws LaravelJoryException
     * @throws \JosKolenberg\Jory\Exceptions\JoryException
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
        $field = $query->getModel()->getTable().'.'.($this->case->isCamel() ? snake_case($filter->getField()) : $filter->getField());
        $this->applyDefaultFieldFilter($query, $field, $filter->getOperator(), $filter->getData());
    }

    /**
     * Get the custom method name to look for to apply a filter.
     *
     * @param Filter $filter
     *
     * @return string
     */
    protected function getCustomFilterMethodName(Filter $filter): string
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
    public function toResponse($request): Response
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
            case 'is_null':
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
     * @throws LaravelJoryException
     * @throws \JosKolenberg\Jory\Exceptions\JoryException
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
     * @throws \JosKolenberg\LaravelJory\Exceptions\LaravelJoryException
     * @throws \JosKolenberg\Jory\Exceptions\JoryException
     */
    protected function loadRelations(Collection $models, array $relations): void
    {
        foreach ($relations as $relation) {
            $this->loadRelation($models, $relation);
        }

        // We clear Eloquent's relations, so any filtering on relations
        // doesn't affect any custom attributes which rely on relations.
        $models->each(function ($model) {
            $model->setRelations([]);
        });

        // Hook into the afterFetch() method on the related JoryBuilder
        $this->afterFetch($models, $this->getJory());
    }

    /**
     * Load the given relation on a collection of models.
     *
     * @param Collection $collection
     * @param Relation $relation
     * @throws \JosKolenberg\LaravelJory\Exceptions\LaravelJoryException
     * @throws \JosKolenberg\Jory\Exceptions\JoryException
     */
    protected function loadRelation(Collection $collection, Relation $relation): void
    {
        if ($collection->isEmpty()) {
            return;
        }

        $relationName = $relation->getName();

        // Remove the alias part if the relation has one
        $relationParts = explode('_as_', $relationName);
        if (count($relationParts) > 1) {
            $relationName = $relationParts[0];
        }

        // Laravel's relations are in camelCase, convert if we're not in camelCase mode
        $relationName = ! $this->case->isCamel() ? camel_case($relationName) : $relationName;

        // Retrieve the model which will be queried to get the appropriate JoryBuilder
        $relatedModel = $collection->first()->{$relationName}()->getRelated();
        $joryBuilder = $relatedModel::getJoryBuilder();

        $collection->load([
            $relationName => function ($query) use ($joryBuilder, $relation, $relatedModel) {
                // Apply the data in the subjory (filtering/sorting/...) on the query
                $joryBuilder->applyJory($relation->getJory());
                $joryBuilder->applyOnQuery($query);
            },
        ]);

        // Put all retrieved related models in single collection to load subrelations in a single call
        $allRelated = new Collection();
        foreach ($collection as $model) {
            $related = $model->$relationName;

            // We store the related records under the full relation name including alias
            $model->addJoryRelation($relation->getName(), $related);

            if ($related === null) {
                continue;
            }

            if ($related instanceof Model) {
                $allRelated->push($related);
            } else {
                foreach ($related as $item) {
                    $allRelated->push($item);
                }
            }
        }

        // Load the subrelations
        $joryBuilder->loadRelations($allRelated, $relation->getJory()->getRelations());
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
        $field = $query->getModel()->getTable().'.'.($this->case->isCamel() ? snake_case($sort->getField()) : $sort->getField());
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
    protected function beforeQueryBuild($query, Jory $jory, $count = false): void
    {
        if (! $count) {
            $this->selectOnlyRootTable($query);
            if ($this->getConfig()->getLimitDefault() !== null) {
                $query->limit($this->getConfig()->getLimitDefault());
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
    protected function afterQueryBuild($query, Jory $jory, $count = false): void
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

    /**
     * Get the jory object which needs to be applied.
     *
     * @return Jory
     * @throws LaravelJoryException
     * @throws \JosKolenberg\Jory\Exceptions\JoryException
     */
    protected function getJory(): Jory
    {
        // If instance already is set return this one.
        if ($this->jory) {
            $this->applyConfigToJory();

            return $this->jory;
        }

        // If a parser has been set return the one from the parser
        if ($this->joryParser) {
            $jory = $this->joryParser->getJory();

            $this->jory = $jory;

            $this->applyConfigToJory();

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
     * @throws \JosKolenberg\Jory\Exceptions\JoryException
     */
    protected function validate(): void
    {
        (new Validator($this->getConfig(), $this->getJory()))->validate();
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
     * Apply the settings in the Config on the Jory.
     *
     * When no fields are specified in the request, the default fields in Config will be set on the Jory.
     *
     * @return void
     * @throws \JosKolenberg\Jory\Exceptions\JoryException
     */
    public function applyConfigToJory(): void
    {
        if ($this->configHasBeenApplied) {
            return;
        }

        if ($this->jory->getFields() === null && $this->getConfig()->getFields() !== null) {
            // No fields set in the request, but there are fields
            // specified in the config, than we will update the fields
            // with the ones to be shown by default.
            $defaultFields = [];
            foreach ($this->getConfig()->getFields() as $field) {
                if ($field->isShownByDefault()) {
                    $defaultFields[] = $field->getField();
                }
            }
            $this->jory->setFields($defaultFields);
        }

        if ($this->getConfig()->getSorts() !== null) {
            // When default sorts are defined, add them to the Jory
            // When no sorts are requested, the default sorts in the builder will be applied.
            // When sorts are requested, the default sorts are applied after the requested ones.
            $defaultSorts = [];
            foreach ($this->getConfig()->getSorts() as $sort) {
                if ($sort->getDefaultIndex() !== null) {
                    $defaultSorts[$sort->getDefaultIndex()] = new Sort($sort->getField(), $sort->getDefaultOrder());
                }
            }
            ksort($defaultSorts);
            foreach ($defaultSorts as $sort) {
                $this->jory->addSort($sort);
            }
        }

        $this->configHasBeenApplied = true;
    }

    /**
     * Get the key on which data should be returned.
     *
     * @return null|string
     */
    protected function getDataResponseKey(): ?string
    {
        return config('jory.response.data-key');
    }

    /**
     * Get the key on which errors should be returned.
     *
     * @return null|string
     */
    protected function getErrorResponseKey(): ?string
    {
        return config('jory.response.errors-key');
    }

    /**
     * Convert a single model to an array based on the request in the Jory object.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return array
     * @throws \JosKolenberg\LaravelJory\Exceptions\LaravelJoryException
     * @throws \JosKolenberg\Jory\Exceptions\JoryException
     */
    public function modelToArray(Model $model): array
    {
        $jory = $this->getJory();

        // When no fields are specified, we'll use all the model's fields
        // if fields are specified, we use only these.
        if ($jory->getFields() === null) {
            $result = $model->toArray();

            if ($this->case->isCamel()) {
                // Laravel's toArray() method returns snake_case keys, but we want camelCase; so convert it
                $result = $this->case->arrayKeysToCamel($result);
            }
        } else {
            $result = [];
            foreach ($jory->getFields() as $field) {
                $result[$field] = $this->case->isCamel() ? $model->{snake_case($field)} : $model->$field;
            }
        }

        // Add the relations to the result
        foreach ($jory->getRelations() as $relation) {
            $relationName = $relation->getName();
            $relationAlias = $relationName;

            // Split the relation name in Laravel's relation name and the alias, if there is one.
            $relationParts = explode('_as_', $relationName);
            if (count($relationParts) > 1) {
                $relationName = $relationParts[0];
                $relationAlias = $relationParts[1];
            }

            // Laravel's relations are in camelCase, convert if we're not in camelCase mode
            $relationName = ! $this->case->isCamel() ? camel_case($relationName) : $relationName;

            // Get the related records which were fetched earlier. These are stored in the model under the full relation's name including alias
            $related = $model->getJoryRelation($relation->getName());

            // Get the related JoryBuilder to convert the related records to arrays
            $relatedModel = $model->{$relationName}()->getRelated();
            $relatedJoryBuilder = $relatedModel::getJoryBuilder()->applyJory($relation->getJory());

            if ($related === null) {
                // No related model found
                $result[$relationAlias] = null;
            } elseif ($related instanceof Model) {
                // A related model is found
                $result[$relationAlias] = $relatedJoryBuilder->modelToArray($related);
            } else {
                // A related collection
                $relationResult = [];
                foreach ($related as $relatedModel) {
                    $relationResult[] = $relatedJoryBuilder->modelToArray($relatedModel);
                }
                $result[$relationAlias] = $relationResult;
            }
        }

        return $result;
    }
}
