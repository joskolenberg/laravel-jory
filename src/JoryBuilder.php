<?php

namespace JosKolenberg\LaravelJory;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use JosKolenberg\Jory\Exceptions\JoryException;
use JosKolenberg\Jory\Jory;
use JosKolenberg\LaravelJory\Config\Validator;
use JosKolenberg\LaravelJory\Exceptions\LaravelJoryCallException;
use JosKolenberg\LaravelJory\Exceptions\LaravelJoryException;
use JosKolenberg\LaravelJory\Helpers\CaseManager;
use JosKolenberg\LaravelJory\Register\RegistersJoryBuilders;
use JosKolenberg\LaravelJory\Traits\ConvertsModelToArrayByJory;
use JosKolenberg\LaravelJory\Traits\HandlesJoryBuilderConfiguration;
use JosKolenberg\LaravelJory\Traits\HandlesJoryFilters;
use JosKolenberg\LaravelJory\Traits\HandlesJorySorts;
use JosKolenberg\LaravelJory\Traits\LoadsJoryRelations;

/**
 * Class to query models based on Jory data.
 *
 * Class JoryBuilder
 */
abstract class JoryBuilder
{
    use HandlesJorySorts,
        HandlesJoryFilters,
        LoadsJoryRelations,
        RegistersJoryBuilders,
        ConvertsModelToArrayByJory,
        HandlesJoryBuilderConfiguration;

    /**
     * @var Builder
     */
    protected $builder;

    /**
     * @var null|Jory
     */
    protected $jory = null;

    /**
     * @var Model|null
     */
    protected $model = null;

    /**
     * @var CaseManager
     */
    protected $case = null;

    /**
     * JoryBuilder constructor.
     *
     */
    public function __construct()
    {
        $this->case = app(CaseManager::class);

        $this->initConfig();
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
     * Apply a Jory object.
     *
     * @param Jory $jory
     *
     * @return JoryBuilder
     * @throws JoryException
     */
    public function applyJory(Jory $jory): self
    {
        $this->jory = $this->applyConfigToJory($this->config, $jory);

        return $this;
    }

    /**
     * Get a collection of Models based on the baseQuery and Jory data.
     *
     * @return Collection
     * @throws LaravelJoryException
     * @throws JoryException
     */
    public function get(): Collection
    {
        $collection = $this->buildQuery()->get();

        $jory = $this->jory;
        $collection = $this->afterFetch($collection);

        $this->loadRelations($collection, $jory->getRelations());

        return $collection;
    }

    /**
     * Get the first Model based on the baseQuery and Jory data.
     *
     * @return Model|null
     * @throws LaravelJoryException
     * @throws JoryException
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

        $model = $this->afterFetch(new Collection([$model]))->first();

        $this->loadRelations(new Collection([$model]), $this->jory->getRelations());

        return $model;
    }

    /**
     * Count the records based on the filters in the Jory object.
     *
     * @return int
     * @throws LaravelJoryException
     * @throws JoryException
     */
    public function getCount(): int
    {
        $query = clone $this->builder;

        $jory = $this->jory;

        $this->beforeQueryBuild($query, $jory, true);

        // Apply filters if there are any
        if ($jory->getFilter()) {
            $this->applyFilter($query, $jory->getFilter());
        }

        $this->afterQueryBuild($query, $jory, true);

        return $query->count();
    }

    /**
     * Get the result array for the first result.
     *
     * @return array|null
     * @throws LaravelJoryException
     * @throws JoryException
     */
    public function firstToArray(): ?array
    {
        $model = $this->getFirst();
        if (! $model) {
            return null;
        }

        return $this->modelToArray($model);
    }

    /**
     * Get the result array.
     *
     * @return array
     * @throws LaravelJoryException
     * @throws JoryException
     */
    public function toArray(): array
    {
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
     * @throws JoryException
     */
    protected function buildQuery(): Builder
    {
        $query = $this->builder;

        $this->applyOnQuery($query);

        return $query;
    }

    /**
     * Apply the jory data on an existing query.
     *
     * @param $query
     * @throws LaravelJoryException
     * @throws JoryException
     */
    public function applyOnQuery($query): void
    {
        $jory = $this->jory;
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
     * Set the model to query on.
     * This model will we the base result for this builder
     * with fields an relations as applied in the Jory.
     *
     * @param Model $model
     * @return \JosKolenberg\LaravelJory\JoryBuilder
     */
    public function onModel(Model $model): self
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Do some tweaking before the Jory settings are applied to the query.
     *
     * @param $query
     * @param \JosKolenberg\Jory\Jory $jory
     * @param bool $count
     */
    protected function beforeQueryBuild($query, Jory $jory, $count = false): void
    {
        if (! $count) {
            // By default select only the columns from the root table.
            $this->selectOnlyRootTable($query);
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
     *  - Offset/Limit: An offset or limit applied here will overrule the ones requested or configured.
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
     *
     * E.g. 2. you could sort the collection in a way which is hard using queries
     *      but easier done using a collection.
     *
     * @param Collection $collection
     * @return Collection
     */
    protected function afterFetch(Collection $collection): Collection
    {
        return $collection;
    }

    /**
     * Alter the query to select only the columns of
     * the model which is being queried.
     *
     * This way we prevent field conflicts when
     * joins are applied.
     *
     * @param $query
     */
    protected function selectOnlyRootTable($query): void
    {
        $table = $query->getModel()->getTable();
        $query->select($table.'.*');
    }

    /**
     * Validate the Jory object by the settings in the Config.
     *
     * @throws LaravelJoryCallException
     * @throws LaravelJoryException
     * @throws JoryException
     */
    public function validate(): void
    {
        (new Validator($this->getConfig(), $this->jory))->validate();
    }

    /**
     * Convert a single model to an array based on the request in the Jory object.
     *
     * @param Model $model
     * @return array
     * @throws LaravelJoryException
     * @throws JoryException
     */
    public function modelToArray(Model $model): array
    {
        return $this->modelToArrayByJory($model, $this->jory);
    }

    /**
     * Tell if the Jory requests the given field.
     *
     * @param $field
     * @return bool
     */
    protected function hasField($field): bool
    {
        return $this->jory->hasField($this->case->isCamel() ? Str::camel($field) : $field);
    }

    /**
     * Tell if the Jory has a filter on the given field.
     *
     * @param $field
     * @return bool
     */
    protected function hasFilter($field): bool
    {
        return $this->jory->hasFilter($this->case->isCamel() ? Str::camel($field) : $field);
    }

    /**
     * Tell if the Jory has a sort on the given field.
     *
     * @param $field
     * @return bool
     */
    protected function hasSort($field): bool
    {
        return $this->jory->hasSort($this->case->isCamel() ? Str::camel($field) : $field);
    }
}
