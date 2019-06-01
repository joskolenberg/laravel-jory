<?php

namespace JosKolenberg\LaravelJory\Config;

use JosKolenberg\LaravelJory\Helpers\CaseManager;
use JosKolenberg\LaravelJory\JoryResource;

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
     * @var JoryResource
     */
    protected $joryResource;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var CaseManager
     */
    protected $case = null;

    /**
     * Relation constructor.
     *
     * @param string $name
     * @param JoryResource $joryResource
     */
    public function __construct(string $name, JoryResource $joryResource)
    {
        $this->name = $name;
        $this->joryResource = $joryResource;

        $this->case = app(CaseManager::class);
    }

    /**
     * Set the relation's description.
     *
     * @param string $description
     * @return $this
     */
    public function description(string $description): Relation
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
        return $this->case->toCurrent($this->name);
    }

    /**
     * Get the description.
     *
     * @return string
     */
    public function getDescription(): string
    {
        if ($this->description === null) {
            return 'The ' . $this->getName() . ' relation.';
        }

        return $this->description;
    }

    /**
     * Get the related joryResource.
     *
     * @return JoryResource
     */
    public function getJoryResource(): JoryResource
    {
        return $this->joryResource;
    }

    /**
     * Get the related model type.
     *
     * @return null|string
     */
    public function getType(): ?string
    {
        return $this->joryResource->getUri();
    }
}
