<?php

namespace JosKolenberg\LaravelJory\Traits;

use JosKolenberg\LaravelJory\Contracts\JoryBuilderInterface;
use JosKolenberg\LaravelJory\GenericJoryBuilder;

/**
 * Trait to mark a Model as Jory-queryable.
 *
 * This trait must be applied to a model as a marker even if the jory method is manually added to the model.
 *
 * Trait JoryTrait
 */
trait JoryTrait
{
    /**
     * Return the JoryBuilder to query on this model.
     * A generic one by default, override this method to apply custom JoryBuilder class.
     *
     * @return JoryBuilderInterface
     */
    public static function jory(): JoryBuilderInterface
    {
        return (new GenericJoryBuilder())->onModel(static::class);
    }
}
