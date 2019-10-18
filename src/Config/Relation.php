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
     * @var null|array
     */
    protected $select = null;

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
     * When using explicitSelect we only query for the requested fields on the model.
     *
     * In order to load a relation we might need some foreign key fields on this
     * model for the relation to load correctly. For example; if a user has
     * a belongsTo company relation we probably need the company_id field
     * in order to load the company. Use this method to select this
     * field in the query when this relation is requested.
     *
     * @param mixed $fields
     * @return Relation
     */
    public function select($fields): Relation
    {
        if(!is_array($fields)){
            $fields = [$fields];
        }

        $this->select = $fields;

        return $this;
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

    /**
     * Get the fields to be selected in the query.
     *
     * @return null|array
     */
    public function getSelect():? array
    {
        return $this->select;
    }
}
