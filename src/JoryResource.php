<?php


namespace JosKolenberg\LaravelJory;


use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use JosKolenberg\Jory\Exceptions\JoryException;
use JosKolenberg\Jory\Jory;
use JosKolenberg\LaravelJory\Config\Config;
use JosKolenberg\LaravelJory\Config\Field;
use JosKolenberg\LaravelJory\Config\Filter;
use JosKolenberg\LaravelJory\Config\Relation;
use JosKolenberg\LaravelJory\Config\Sort;
use JosKolenberg\LaravelJory\Config\Validator;
use JosKolenberg\LaravelJory\Helpers\CaseManager;
use JosKolenberg\LaravelJory\Helpers\CaseManager as CaseManagerAlias;
use JosKolenberg\LaravelJory\Helpers\FilterHelper;
use JosKolenberg\LaravelJory\Helpers\ResourceNameHelper;
use JosKolenberg\LaravelJory\Register\JoryResourcesRegister;

abstract class JoryResource
{

    /**
     * @var string
     */
    protected $modelClass;

    /**
     * @var string
     */
    protected $uri;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Jory
     */
    protected $jory;

    /**
     * @var CaseManagerAlias
     */
    protected $case;

    /**
     * @var array
     */
    protected $relatedJoryResources;

    /**
     * Configure the JoryResource.
     *
     * @return void
     */
    abstract protected function configure(): void;

    /**
     * Get the uri for this resource which can be
     * used in the URL to query this resource.
     *
     * @return string
     */
    public function getUri()
    {
        if (!$this->uri) {
            // If no uri is set explicitly we default to the kebabcased model class.
            return Str::kebab(class_basename($this->modelClass));
        }

        return $this->uri;
    }

    /**
     * Get the related model's fully qualified class name.
     *
     * @return string
     */
    public function getModelClass()
    {
        return $this->modelClass;
    }


    /**
     * Add a field which can be requested by the API users..
     *
     * @param $field
     * @return Field
     */
    public function field($field): Field
    {
        return $this->config->field($field);
    }

    /**
     * Add a filter option which can be applied by the API users.
     *
     * @param $field
     * @return Filter
     */
    public function filter($field): Filter
    {
        return $this->config->filter($field);
    }

    /**
     * Add a sort option which can be applied by the API users.
     *
     * @param $field
     * @return Sort
     */
    public function sort($field): Sort
    {
        return $this->config->sort($field);
    }

    /**
     * Set the default value for limit parameter
     * in case the API user doesn't set one.
     *
     * @param null|int $default
     * @return $this
     */
    public function limitDefault(?int $default): JoryResource
    {
        $this->config->limitDefault($default);

        return $this;
    }

    /**
     * Set the maximum value for limit parameter,
     * the API users can't exceed this value.
     *
     * @param null|int $max
     * @return $this
     */
    public function limitMax(?int $max): JoryResource
    {
        $this->config->limitMax($max);

        return $this;
    }

    /**
     * Add a relation option which can be requested by the API users.
     *
     * When no joryResource is given, the method will find the related model
     * and joryResource by calling the relationMethod. If you don't want
     * this to happen you can supply the joryResource to prevent this.
     *
     * @param string $name
     * @param string $joryResource
     * @return Relation
     */
    public function relation(string $name, string $joryResource = null): Relation
    {
        return $this->config->relation($name, $joryResource);
    }

    /**
     * Set this resource to use explicitSelect.
     *
     * By default a query will be executed selecting all fields using table name and asterisk. (e.g. users.*)
     *
     * With explicitSelect enabled the query will select fields explicitly based on the requested fields.
     * By default the selected database field will be equal to a field's name. Use the select() method
     * when defining the field to alter this behaviour, this could be useful for custom attributes
     * which rely on other database fields.
     * E.g. $this->field('full_name')->select(['first_name', 'last_name']);
     *
     * @return void
     */
    public function explicitSelect(): void
    {
        $this->config->explicitSelect(true);
    }

    /**
     * Get the Configuration.
     *
     * @return Config
     */
    public function getConfig(): Config
    {
        if (!$this->config) {
            $this->config = new Config($this->modelClass);
            $this->configure();
        }

        return $this->config;
    }

    /**
     * Hook into the collection right after it is fetched and before it is turned into an array.
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
     * @param bool $count
     */
    public function beforeQueryBuild($query, $count = false): void
    {
    }

    /**
     * Hook into the query after all settings in Jory object
     * are applied and just before the query is executed.
     *
     * @param $query
     * @param bool $count
     */
    public function afterQueryBuild($query, $count = false): void
    {
    }

    /**
     * Tell if the Jory query requests the given field.
     *
     * @param $field
     * @return bool
     */
    protected function hasField($field): bool
    {
        return $this->jory->hasField($this->getCase()->toCurrent($field));
    }

    /**
     * Tell if the Jory query has a filter on the given field.
     *
     * @param $field
     * @return bool
     */
    protected function hasFilter($field): bool
    {
        return $this->jory->hasFilter($this->getCase()->toCurrent($field));
    }

    /**
     * Tell if the Jory query has a sort on the given field.
     *
     * @param $field
     * @return bool
     */
    protected function hasSort($field): bool
    {
        return $this->jory->hasSort($this->getCase()->toCurrent($field));
    }

    /**
     * Apply a Jory query on the Resource.
     *
     * @param Jory $jory
     * @throws JoryException
     */
    public function setJory(Jory $jory): void
    {
        $this->jory = $this->getConfig()->applyToJory($jory);
    }

    /**
     * Get the Jory query.
     *
     * @return Jory
     */
    public function getJory(): Jory
    {
        return $this->jory;
    }

    /**
     * Validate the Jory query against the configuration.
     *
     * @throws Exceptions\LaravelJoryCallException
     */
    public function validate(): void
    {
        (new Validator($this->getConfig(), $this->jory))->validate();
    }

    /**
     * Get a fresh instance of this JoryResource.
     *
     * We want to build fresh copies now and then because
     * we don't want to use the same instance twice when
     * loading relations in which case we could be
     * querying the same model multiple times.
     *
     * @return JoryResource
     */
    public function fresh(): JoryResource
    {
        return new static();
    }

    /**
     * Get the CaseManager.
     *
     * @return CaseManager
     */
    public function getCase(): CaseManagerAlias
    {
        return app(CaseManagerAlias::class);
    }

    /**
     * Extend Laravel's default "where operators" with is_null, not_null etc.
     *
     * @param mixed $query
     * @param $field
     * @param $operator
     * @param $data
     */
    protected function applyWhere($query, $field, $operator, $data): void
    {
        FilterHelper::applyWhere($query, $field, $operator, $data);
    }

    /**
     * Get an associative array of all relations requested in the Jory query.
     *
     * The key of the array holds the name of the relation (including any
     * aliases) The values of the array are JoryResource objects which
     * in turn hold the Jory query object for the relation.
     *
     * We build this array here once so we don't have to grab for
     * a new JoryResource for each record we want to convert to
     * an array leading into bad performance.
     *
     * @return array
     * @throws \JosKolenberg\Jory\Exceptions\JoryException
     */
    public function getRelatedJoryResources(): array
    {
        if (!$this->relatedJoryResources) {
            $this->relatedJoryResources = [];

            foreach ($this->jory->getRelations() as $relation) {
                $relationName = ResourceNameHelper::explode($relation->getName())->baseName;

                $relatedJoryResource = $this->getConfig()->getRelation($relationName)->getJoryResource()->fresh();

                $relatedJoryResource->setJory($relation->getJory());

                $this->relatedJoryResources[$relation->getName()] = $relatedJoryResource;
            }
        }

        return $this->relatedJoryResources;
    }

    /**
     * Convert a single model to an array based on the
     * fields and relations in the Jory query.
     *
     * @param Model $model
     * @return array
     * @throws \JosKolenberg\Jory\Exceptions\JoryException
     */
    public function modelToArray(Model $model): array
    {
        $jory = $this->getJory();

        $result = [];

        /**
         * Export this model's attributes to an array. We will use Eloquent's
         * attributesToArray() method so we get the casting which is set
         * in the model's casts array. When the value is not present
         * (because it's not visible for the serialisation)
         * we will call for the property directly.
         */
        $raw = $model->attributesToArray();
        foreach ($jory->getFields() as $field) {
            $result[$field] = array_key_exists(Str::snake($field), $raw) ? $raw[Str::snake($field)] : $model->{Str::snake($field)};
        }

        // Add the relations to the result
        foreach ($this->getRelatedJoryResources() as $relationName => $relatedJoryResource) {
            $relationAlias = $relationName;

            // Split the relation name in Laravel's relation name and the alias, if there is one.
            $relationParts = explode(' as ', $relationName);
            if (count($relationParts) > 1) {
                $relationAlias = $relationParts[1];
            }

            // Get the related records which were fetched earlier. These are stored in the model under the full relation's name including alias
            $related = $model->joryRelations[$relationName];

            if ($related === null) {
                // No related model found
                $result[$relationAlias] = null;
            } elseif ($related instanceof Model) {
                // A related model is found
                $result[$relationAlias] = $relatedJoryResource->modelToArray($related);
            } else {
                // A related collection
                $relationResult = [];
                foreach ($related as $relatedModel) {
                    $relationResult[] = $relatedJoryResource->modelToArray($relatedModel);
                }
                $result[$relationAlias] = $relationResult;
            }
        }

        return $result;
    }

    /**
     * Apply any user specific actions on the query.
     *
     * @param $query
     * @param null $user
     */
    public function authorize($query, $user = null): void
    {
    }
}