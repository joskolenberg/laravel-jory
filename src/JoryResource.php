<?php

namespace JosKolenberg\LaravelJory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use JosKolenberg\Jory\Jory;
use JosKolenberg\LaravelJory\Attributes\Attribute;
use JosKolenberg\LaravelJory\Config\Config;
use JosKolenberg\LaravelJory\Config\Field;
use JosKolenberg\LaravelJory\Config\Filter;
use JosKolenberg\LaravelJory\Config\Relation;
use JosKolenberg\LaravelJory\Config\Sort;
use JosKolenberg\LaravelJory\Config\Validator;
use JosKolenberg\LaravelJory\Helpers\CaseManager;
use JosKolenberg\LaravelJory\Scopes\FilterScope;
use JosKolenberg\LaravelJory\Scopes\SortScope;
use JosKolenberg\LaravelJory\Traits\ConvertsModelToArray;

abstract class JoryResource
{
    use ConvertsModelToArray {
        modelToArray as convertModelToArray;
    }

    /**
     * @var string
     */
    protected $modelClass;

    /**
     * @var boolean
     */
    protected $routes = true;

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
     * @var CaseManager
     */
    protected $case;

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
    public function getUri(): string
    {
        if (! $this->uri) {
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
    public function getModelClass(): string
    {
        return $this->modelClass;
    }

    /**
     * Add a field which can be requested by the API users..
     *
     * @param $name
     * @param Attribute|null $getter
     * @return Field
     */
    public function field($name, Attribute $getter = null): Field
    {
        return $this->config->field($name, $getter);
    }

    /**
     * Add a filter option which can be applied by the API users.
     *
     * @param $field
     * @param FilterScope|null $scope
     * @return Filter
     */
    public function filter($field, FilterScope $scope = null): Filter
    {
        return $this->config->filter($field, $scope);
    }

    /**
     * Add a sort option which can be applied by the API users.
     *
     * @param $field
     * @param SortScope|null $scope
     * @return Sort
     */
    public function sort($field, SortScope $scope = null): Sort
    {
        return $this->config->sort($field, $scope);
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
        if (! $this->config) {
            $this->config = new Config($this->modelClass);
            $this->configure();
        }

        return $this->config;
    }

    /**
     * Apply a Jory query on the Resource.
     *
     * @param Jory $jory
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
     * Convert a single model to an array based on the
     * fields and relations in the Jory query.
     *
     * @param Model $model
     * @return array
     */
    public function modelToArray(Model $model): array
    {
        return $this->convertModelToArray($model, $this);
    }

    /**
     * Apply any user specific actions on the query.
     *
     * @param $builder
     * @param null $user
     */
    public function authorize($builder, $user = null): void
    {
    }

    /**
     * Tell if the routes should be enabled for this resource.
     *
     * @return bool
     */
    public function hasRoutes(): bool
    {
        return $this->routes;
    }
}