<?php

namespace JosKolenberg\LaravelJory\Config;

use Illuminate\Support\Str;
use JosKolenberg\LaravelJory\Helpers\CaseManager;
use JosKolenberg\LaravelJory\Register\JoryResourcesRegister;

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
     * @var CaseManager
     */
    protected $case = null;

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
     * @return null|string
     */
    public function getType(): ?string
    {
        $registration = app()->make(JoryResourcesRegister::class)->getByModelClass($this->modelClass);

        return $registration ? $registration->getUri() : null;
    }
}
