<?php

namespace JosKolenberg\LaravelJory\Register;

/**
 * Class JoryBuildersRegister
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
        $this->registrations[] = $registration;

        return $registration;
    }

    /**
     * Get a registration by a Model's classname.
     *
     * @param string $modelClass
     * @return \JosKolenberg\LaravelJory\Register\JoryBuilderRegistration|null
     */
    public function getRegistrationByModelClass(string $modelClass): ? JoryBuilderRegistration
    {
        foreach ($this->registrations as $registration) {
            if ($registration->getModelClass() == $modelClass) {
                return $registration;
            }
        }

        return null;
    }

    /**
     * Get a registration by a JoryBuilder's classname.
     *
     * @param string $builderClass
     * @return \JosKolenberg\LaravelJory\Register\JoryBuilderRegistration|null
     */
    public function getRegistrationByBuilderClass(string $builderClass): ? JoryBuilderRegistration
    {
        foreach ($this->registrations as $registration) {
            if ($registration->getBuilderClass() == $builderClass) {
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
    public function getRegistrationByUri(string $uri): ? JoryBuilderRegistration
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
