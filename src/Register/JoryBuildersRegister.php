<?php

namespace JosKolenberg\LaravelJory\Register;

/**
 * Class JoryBuildersRegister.
 *
 * Collects the registered JoryBuilders.
 */
class JoryBuildersRegister
{
    /**
     * @var array
     */
    protected $registrations = [];

    /**
     * Add a registration.
     *
     * @param \JosKolenberg\LaravelJory\Register\JoryBuilderRegistration $registration
     * @return \JosKolenberg\LaravelJory\Register\JoryBuilderRegistration
     */
    public function add(JoryBuilderRegistration $registration): ? JoryBuilderRegistration
    {
        // There can be only one registration for a model
        // When a second one is registered for the same model remove the previous
        foreach ($this->registrations as $key => $existingRegistration) {
            if ($registration->getModelClass() == $existingRegistration->getModelClass()) {
                unset($this->registrations[$key]);
            }
        }

        $this->registrations[] = $registration;

        return $registration;
    }

    /**
     * Get a registration by a Model's classname.
     *
     * @param string $modelClass
     * @return \JosKolenberg\LaravelJory\Register\JoryBuilderRegistration|null
     */
    public function getByModelClass(string $modelClass): ? JoryBuilderRegistration
    {
        foreach ($this->registrations as $registration) {
            if ($registration->getModelClass() === $modelClass) {
                return $registration;
            }
        }

        return null;
    }

    /**
     * Get a registration by a Model's classname.
     *
     * @param string $builderClass
     * @return \JosKolenberg\LaravelJory\Register\JoryBuilderRegistration|null
     */
    public function getByBuilderClass(string $builderClass): ? JoryBuilderRegistration
    {
        foreach ($this->registrations as $registration) {
            if ($registration->getBuilderClass() === $builderClass) {
                return $registration;
            }
        }

        return null;
    }

    /**
     * Get a registration by uri.
     *
     * @param string $uri
     * @return \JosKolenberg\LaravelJory\Register\JoryBuilderRegistration|null
     */
    public function getByUri(string $uri): ? JoryBuilderRegistration
    {
        foreach ($this->registrations as $registration) {
            if ($registration->getUri() == $uri) {
                return $registration;
            }
        }

        return null;
    }

    /**
     * Get an array of all registered uri's.
     *
     * @return array
     */
    public function getUrisArray(): array
    {
        $result = [];
        foreach ($this->registrations as $registration) {
            $result[] = $registration->getUri();
        }

        return $result;
    }
}
