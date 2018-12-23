<?php

namespace JosKolenberg\LaravelJory\Config;

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
            return 'The ' . $this->name . ' relation.';
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
        $registered = config('jory.routes');
        $type = null;
        foreach ($registered as $key => $className) {
            if ($this->modelClass === $className) {
                $type = $key;
                break;
            }
        }

        return $type ? $type : 'Not defined.';
    }
}
