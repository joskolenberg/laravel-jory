<?php

namespace JosKolenberg\LaravelJory\Config;

use Illuminate\Support\Str;
use JosKolenberg\LaravelJory\Helpers\CaseManager;
use JosKolenberg\LaravelJory\JoryResource;
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
    protected $parentClass;

    /**
     * @var JoryResource
     */
    protected $joryResource;

    /**
     * @var CaseManager
     */
    protected $case = null;

    /**
     * Relation constructor.
     *
     * @param string $name
     * @param string $parentClass
     */
    public function __construct(string $name, string $parentClass)
    {
        $this->name = $name;
        $this->parentClass = $parentClass;

        $this->case = app(CaseManager::class);
    }

    /**
     * Get the relation's name in the current case.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->case->toCurrent($this->name);
    }

    /**
     * Get the relation name as configured (which should be the actual relation name)
     *
     * @return string
     */
    public function getOriginalName(): string
    {
        return $this->name;
    }

    /**
     * Get the related joryResource.
     *
     * @return JoryResource
     */
    public function getJoryResource(): JoryResource
    {
        if (!$this->joryResource) {
            $relationMethod = Str::camel($this->name);

            $relatedClass = get_class((new $this->parentClass)->{$relationMethod}()->getRelated());

            $this->joryResource = app()->make(JoryResourcesRegister::class)->getByModelClass($relatedClass);
        }

        return $this->joryResource;
    }

    /**
     * Get the related model type.
     *
     * @return null|string
     */
    public function getType(): ?string
    {
        return $this->getJoryResource()->getUri();
    }
}
