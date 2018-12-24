<?php

namespace JosKolenberg\LaravelJory\Register;

/**
 * Class JoryBuilderRegistration
 *
 * Collects data for registered Models with associated JoryBuilders and routes.
 */
class JoryBuilderRegistration
{
    /**
     * @var string
     */
    protected $modelClass = null;

    /**
     * @var null|string
     */
    protected $builderClass = null;

    /**
     * @var null|string
     */
    protected $uri = null;

    /**
     * JoryBuilderRegistration constructor.
     *
     * @param string $modelClass
     * @param string|null $builderClass
     */
    public function __construct(string $modelClass, string $builderClass = null)
    {
        $this->modelClass = $modelClass;
        $this->builderClass = $builderClass;
        $this->uri = kebab_case(class_basename($modelClass));
    }

    /**
     * Set the associated JoryBuilder class.
     *
     * @param string $builderClass
     * @return JoryBuilderRegistration
     */
    public function builder(string $builderClass): JoryBuilderRegistration
    {
        $this->builderClass = $builderClass;

        return $this;
    }

    /**
     * Set the associated uri for the route.
     *
     * @param string $uri
     * @return JoryBuilderRegistration
     */
    public function uri(string $uri): JoryBuilderRegistration
    {
        $this->uri = $uri;

        return $this;
    }

    /**
     * Get the classname of the associated Model.
     *
     * @return string
     */
    public function getModelClass(): string
    {
        return $this->modelClass;
    }

    /**
     * Get the classname of the associated JoryBuilder.
     *
     * @return null|string
     */
    public function getBuilderClass(): ? string
    {
        return $this->builderClass;
    }

    /**
     * Get the associated uri to be used in routing.
     *
     * @return null|string
     */
    public function getUri(): string
    {
        return $this->uri;
    }
}
