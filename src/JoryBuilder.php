<?php

namespace JosKolenberg\LaravelJory;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
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
     * JoryBuilder constructor.
     * @param JoryResource $joryResource
     */
    public function __construct(JoryResource $joryResource)
    {
        $this->joryResource = $joryResource;
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
        $builder = $this->builder;

        $this->applyOnCountQuery($builder);

        return $builder->count();
    }

    /**
     * Tell if any record exists based on the filters in the Jory object.
     *
     * @return bool
     */
    public function getExists(): bool
    {
        $builder = $this->builder;

        $this->applyOnCountQuery($builder);

        return $builder->exists();
    }

    /**
     * Build a new query based on the baseQuery and Jory data.
     *
     * @return Builder
     */
    public function buildQuery(): Builder
    {
        $builder = $this->builder;

        $this->applyOnQuery($builder);

        return $builder;
    }

    /**
     * Apply the jory data on an existing query.
     *
     * @param $builder
     * @return mixed
     */
    public function applyOnQuery($builder)
    {
        $jory = $this->joryResource->getJory();

        $this->applySelects($builder, $this->joryResource);

        // Apply filters if there are any
        if ($jory->getFilter()) {
            $this->applyFilter($builder, $this->joryResource);
        }

        $this->authorizeQuery($builder);

        $this->applySorts($builder, $this->joryResource);
        $this->applyOffsetAndLimit($builder, $jory->getOffset(), $jory->getLimit());

        return $builder;
    }

    /**
     * Apply the jory data on an existing query.
     *
     * @param $builder
     * @return mixed
     */
    public function applyOnCountQuery($builder)
    {
        // Apply filters if there are any
        if ($this->joryResource->getJory()->getFilter()) {
            $this->applyFilter($builder, $this->joryResource);
        }

        $this->authorizeQuery($builder);

        return $builder;
    }

    /**
     * Apply an offset and limit on the query.
     *
     * @param $builder
     * @param int|null $offset
     * @param int|null $limit
     */
    protected function applyOffsetAndLimit($builder, int $offset = null, int $limit = null): void
    {
        if ($offset !== null) {
            // Check on null, so even 0 will be applied.
            // this can be overruled by the request this way.
            $builder->offset($offset);
        }
        if ($limit !== null) {
            $builder->limit($limit);
        }
    }

    /**
     * @param $builder
     * @return void
     */
    protected function authorizeQuery($builder): void
    {
        $builder->where(function($builder) {
            $this->joryResource->authorize($builder, Auth::user());
        });
    }
}
