<?php

namespace JosKolenberg\LaravelJory\Register;

trait RegistersJoryBuilders
{
    public static function register(string $modelClass, string $builderClass)
    {
        $registration = new JoryBuilderRegistration($modelClass, $builderClass);

        app()->make(JoryBuildersRegister::class)->add($registration);

        return $registration;
    }
}
