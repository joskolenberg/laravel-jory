<?php

namespace JosKolenberg\LaravelJory\Config;

use JosKolenberg\LaravelJory\Register\JoryBuildersRegister;

/**
 * Class Relation.
 *
 * Represents a relation in the config.
 */
class Relation
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $modelClass;

    /**
     * @var string
     */
    protected $description;

    /**
     * Relation constructor.
     *
     * @param string $name
     * @param string $modelClass
     */
    public function __construct(string $name, string $modelClass)
    {
        $this->name = $name;
        $this->modelClass = $modelClass;
    }

    /**
     * Set the relation's description.
     *
     * @param string $description
     * @return Relation
     */
    public function description(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the description.
     *
     * @return string
     */
    public function getDescription(): string
    {
        if ($this->description === null) {
            return 'The '.$this->name.' relation.';
        }

        return $this->description;
    }

    /**
     * Get the related model class.
     *
     * @return string
     */
    public function getModelClass(): string
    {
        return $this->modelClass;
    }

    /**
     * Get the related model type.
     *
     * @return string
     */
    public function getType(): string
    {
        $registration = app()->make(JoryBuildersRegister::class)->getRegistrationByModelClass($this->modelClass);

        return $registration ? $registration->getUri() : 'Not defined.';
    }

    /**
     * Turn the relation into camelCase.
     */
    public function toCamelCase()
    {
        $this->name = camel_case($this->name);
    }
}
