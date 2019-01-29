<?php

namespace JosKolenberg\LaravelJory;

use JosKolenberg\Jory\Jory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use JosKolenberg\Jory\Support\Sort;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use JosKolenberg\Jory\Parsers\JsonParser;
use JosKolenberg\Jory\Parsers\ArrayParser;
use JosKolenberg\LaravelJory\Config\Config;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Support\Responsable;
use JosKolenberg\LaravelJory\Config\Validator;
use JosKolenberg\Jory\Exceptions\JoryException;
use JosKolenberg\LaravelJory\Helpers\CaseManager;
use JosKolenberg\LaravelJory\Parsers\RequestParser;
use JosKolenberg\Jory\Contracts\JoryParserInterface;
use JosKolenberg\LaravelJory\Routes\BuildsJoryRoutes;
use JosKolenberg\LaravelJory\Traits\HandlesJorySorts;
use JosKolenberg\LaravelJory\Traits\HandlesJoryFilters;
use JosKolenberg\LaravelJory\Traits\LoadsJoryRelations;
use JosKolenberg\LaravelJory\Register\RegistersJoryBuilders;
use JosKolenberg\LaravelJory\Exceptions\LaravelJoryException;
use JosKolenberg\LaravelJory\Traits\ConvertsModelToArrayByJory;
use JosKolenberg\LaravelJory\Exceptions\LaravelJoryCallException;

/**
 * Class to query models based on Jory data.
 *
 * Class JoryBuilder
 */
class JoryBuilder implements Responsable
{
    use BuildsJoryRoutes,
        RegistersJoryBuilders,
        HandlesJoryFilters,
        HandlesJorySorts,
        LoadsJoryRelations,
        ConvertsModelToArrayByJory;

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
        return $this->modeToArrayByJory($model, $this->getJory());
    }
}
