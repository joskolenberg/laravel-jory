<?php

namespace JosKolenberg\LaravelJory;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use JosKolenberg\LaravelJory\Helpers\CaseManager;
use JosKolenberg\LaravelJory\Traits\HandlesJoryFilters;
use JosKolenberg\LaravelJory\Traits\HandlesJorySelects;
use JosKolenberg\LaravelJory\Traits\HandlesJorySorts;
use JosKolenberg\LaravelJory\Traits\LoadsJoryRelations;

/**
 * Class to query models based on Jory data.
 *
 * Class JoryBuilder
 */
class JoryBuilder
{
    use HandlesJorySorts,
        HandlesJoryFilters,
        HandlesJorySelects,
        LoadsJoryRelations;

    /**
     * @var JoryResource
     */
    protected $joryResource;

    /**
     * @var CaseManager
     */
    protected $case = null;

    /**
     * JoryBuilder constructor.
     * @param JoryResource $joryResource
     */
    public function __construct(JoryResource $joryResource)
    {
        $this->joryResource = $joryResource;

        $this->case = app(CaseManager::class);
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
     * Get a collection of Models based on the baseQuery and Jory query.
     *
     * @return Collection
     */
    public function get(): Collection
    {
        $collection = $this->buildQuery()->get();

        $this->loadRelations($collection, $this->joryResource);

        return $collection;
    }

    /**
     * Get the first Model based on the baseQuery and Jory data.
     *
     * @return Model|null
     */
    public function getFirst(): ?Model
    {
        $model = $this->buildQuery()->first();

        if (!$model) {
            return null;
        }

        $this->loadRelations(new Collection([$model]), $this->joryResource);

        return $model;
    }

    /**
     * Count the records based on the filters in the Jory object.
     *
     * @return int
     */
    public function getCount(): int
    {
        $query = $this->builder;

        $this->applyOnCountQuery($query);

        return $query->count();
    }

    /**
     * Build a new query based on the baseQuery and Jory data.
     *
     * @return Builder
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
     * @return mixed
     */
    public function applyOnQuery($query)
    {
        $jory = $this->joryResource->getJory();

        $this->joryResource->beforeQueryBuild($query);

        $this->applySelects($query, $this->joryResource);

        // Apply filters if there are any
        if ($jory->getFilter()) {
            $this->applyFilter($query, $this->joryResource);
        }

        $this->authorizeQuery($query);

        $this->applySorts($query, $this->joryResource);
        $this->applyOffsetAndLimit($query, $jory->getOffset(), $jory->getLimit());

        $this->joryResource->afterQueryBuild($query);

        return $query;
    }

    /**
     * Apply the jory data on an existing query.
     *
     * @param $query
     * @return mixed
     */
    public function applyOnCountQuery($query)
    {
        $this->joryResource->beforeQueryBuild($query, true);

        // Apply filters if there are any
        if ($this->joryResource->getJory()->getFilter()) {
            $this->applyFilter($query, $this->joryResource);
        }

        $this->authorizeQuery($query);

        $this->joryResource->afterQueryBuild($query, true);

        return $query;
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
     * @param $query
     */
    protected function authorizeQuery($query)
    {
        $this->joryResource->authorize($query, Auth::user());
    }
}
