<?php

namespace JosKolenberg\LaravelJory;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use JosKolenberg\Jory\Exceptions\JoryException;
use JosKolenberg\Jory\Jory;
use JosKolenberg\LaravelJory\Exceptions\LaravelJoryException;
use JosKolenberg\LaravelJory\Helpers\CaseManager;
use JosKolenberg\LaravelJory\Traits\HandlesJoryFilters;
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
     * @throws LaravelJoryException
     * @throws JoryException
     */
    public function get(): Collection
    {
        $collection = $this->buildQuery()->get();

        $collection = $this->joryResource->afterFetch($collection);

        $this->loadRelations($collection, $this->joryResource->getJory()->getRelations());

        return $collection;
    }

    /**
     * Get the first Model based on the baseQuery and Jory data.
     *
     * @return Model|null
     * @throws JoryException
     * @throws LaravelJoryException
     */
    public function getFirst(): ?Model
    {
        $model = $this->buildQuery()->first();

        if (!$model) {
            return null;
        }

        $model = $this->joryResource->afterFetch(new Collection([$model]))->first();

        $this->loadRelations(new Collection([$model]), $this->joryResource->getJory()->getRelations());

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

        $this->joryResource->beforeQueryBuild($query, true);

        // Apply filters if there are any
        if ($this->joryResource->getJory()->getFilter()) {
            $this->applyFilter($query, $this->joryResource->getJory()->getFilter());
        }

        $this->joryResource->afterQueryBuild($query, true);

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
     */
    public function applyOnQuery($query): void
    {
        $jory = $this->joryResource->getJory();

        $this->joryResource->beforeQueryBuild($query);

        // Apply filters if there are any
        if ($jory->getFilter()) {
            $this->applyFilter($query, $jory->getFilter());
        }

        $this->applySorts($query, $jory->getSorts());
        $this->applyOffsetAndLimit($query, $jory->getOffset(), $jory->getLimit());

        $this->joryResource->afterQueryBuild($query);
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

}
