<?php


namespace JosKolenberg\LaravelJory\Register;


interface RegistrarInterface
{
    /**
     * Get a registration by a Model's classname.
     *
     * @param string $modelClass
     * @return \JosKolenberg\LaravelJory\Register\JoryBuilderRegistration|null
     */
    public function getByModelClass(string $modelClass): ? JoryBuilderRegistration;

    /**
     * Get a registration by a Model's classname.
     *
     * @param string $builderClass
     * @return \JosKolenberg\LaravelJory\Register\JoryBuilderRegistration|null
     */
    public function getByBuilderClass(string $builderClass): ? JoryBuilderRegistration;

    /**
     * Get a registration by uri.
     *
     * @param string $uri
     * @return \JosKolenberg\LaravelJory\Register\JoryBuilderRegistration|null
     */
    public function getByUri(string $uri): ? JoryBuilderRegistration;

}