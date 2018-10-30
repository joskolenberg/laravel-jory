<?php

namespace JosKolenberg\LaravelJory\Blueprint;

/**
 * Class Relation
 *
 * Represents a relation in the blueprint.
 *
 * @package JosKolenberg\LaravelJory\Blueprint
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
    protected $type;

    /**
     * @var string
     */
    protected $description;

    /**
     * Relation constructor.
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
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
     * Set the related model type.
     *
     * @param string $type
     * @return Relation
     */
    public function type(string $type): self
    {
        $this->type = $type;
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
        if($this->description === null){
            return "The " . $this->name . " relation.";
        }
        return $this->description;
    }

    /**
     * Get the related model type.
     *
     * @return string
     */
    public function getType(): string
    {
        if ($this->type === null) {
            return 'Not defined.';
        }

        return $this->type;
    }

}