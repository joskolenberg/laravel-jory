<?php


namespace JosKolenberg\LaravelJory;


use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use JosKolenberg\Jory\Jory;
use JosKolenberg\LaravelJory\Config\Config;
use JosKolenberg\LaravelJory\Config\Validator;
use JosKolenberg\LaravelJory\Helpers\CaseManager;

abstract class JoryResource
{

    protected $modelClass;
    
    protected $uri;

    protected $config;

    protected $jory;

    protected $case;

    abstract protected function configure();

    public function getUri()
    {
        if(!$this->uri){
            return Str::kebab(class_basename($this->modelClass));
        }

        return $this->uri;
    }

    public function getModelClass()
    {
        return $this->modelClass;
    }

    public function __call($method, $args)
    {
        return $this->config->{$method}(...$args);
    }

    public function getConfig()
    {
        if(!$this->config){
            $this->config = new Config($this->modelClass);
            $this->configure();
        }

        return $this->config;
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
    public function afterFetch(Collection $collection): Collection
    {
        return $collection;
    }

    /**
     * Do some tweaking before the Jory settings are applied to the query.
     *
     * @param $query
     * @param \JosKolenberg\Jory\Jory $jory
     * @param bool $count
     */
    public function beforeQueryBuild($query, Jory $jory, $count = false): void
    {
        if (!$count) {
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
    public function afterQueryBuild($query, Jory $jory, $count = false): void
    {
    }

    /**
     * Tell if the Jory requests the given field.
     *
     * @param $field
     * @return bool
     */
    protected function hasField($field): bool
    {
        return $this->jory->hasField($this->getCase()->isCamel() ? Str::camel($field) : $field);
    }

    /**
     * Tell if the Jory has a filter on the given field.
     *
     * @param $field
     * @return bool
     */
    protected function hasFilter($field): bool
    {
        return $this->jory->hasFilter($this->getCase()->isCamel() ? Str::camel($field) : $field);
    }

    /**
     * Tell if the Jory has a sort on the given field.
     *
     * @param $field
     * @return bool
     */
    protected function hasSort($field): bool
    {
        return $this->jory->hasSort($this->getCase()->isCamel() ? Str::camel($field) : $field);
    }

    public function setJory(Jory $jory)
    {
        $this->jory = $this->getConfig()->applyToJory($jory);
    }

    public function getJory()
    {
        return $this->jory;
    }

    public function validate()
    {
        (new Validator($this->getConfig(), $this->jory))->validate();
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
        $query->select($table . '.*');
    }

    public function fresh()
    {
        return new static();
    }

    public function getCase()
    {
        return app(CaseManager::class);
    }

    /**
     * Apply a filter to a field with default options.
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
            case 'not_like':
                $query->where($field, 'not like', $data);

                return;
            default:
                $query->where($field, $operator ?: '=', $data);
        }
    }
}