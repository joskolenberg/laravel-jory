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
     * Override to apply a custom JoryBuilder class for the model.
     *
     * @return JoryBuilder
     */
    public static function getJoryBuilder(): JoryBuilder
    {
        $register = app()->make(JoryBuildersRegister::class);

        $registration = $register->getRegistrationByModelClass(static::class);

        if (! $registration || ! $registration->getBuilderClass()) {
            return app()->makeWith(JoryBuilder::class, ['modelClass' => static::class]);
        } else {
            $builderClass = $registration->getBuilderClass();

            return new $builderClass(static::class);
        }
    }
}
