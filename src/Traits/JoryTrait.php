<?php

namespace JosKolenberg\LaravelJory\Traits;

use JosKolenberg\LaravelJory\JoryBuilder;
use JosKolenberg\LaravelJory\Register\JoryBuildersRegister;

/**
 * Trait to make a Model "Jory-queryable".
 *
 * Trait JoryTrait
 */
trait JoryTrait
{
    protected $joryRelations = [];

    /**
     * Return the JoryBuilder to query on this model.
     *
     * @return JoryBuilder
     */
    public static function jory(): JoryBuilder
    {
        return static::getJoryBuilder()->onQuery((new static())->query());
    }

    /**
     * Get a new JoryBuilder instance for the model.
     *
     * @return JoryBuilder
     */
    public static function getJoryBuilder(): JoryBuilder
    {
        $register = app()->make(JoryBuildersRegister::class);
        $registration = $register->getByModelClass(static::class);
        $builderClass = $registration->getBuilderClass();

        return new $builderClass();
    }

    /**
     * Add a Jory relation.
     *
     * @param string $name
     * @param $data
     */
    public function addJoryRelation(string $name, $data)
    {
        $this->joryRelations[$name] = $data;
    }

    /**
     * Get a Jory relation.
     *
     * @param string $name
     * @return mixed
     */
    public function getJoryRelation(string $name)
    {
        return $this->joryRelations[$name];
    }
}
