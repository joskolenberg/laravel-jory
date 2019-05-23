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
    protected $manualRegistrar = [];

    public function __construct(ManualRegistrar $manualRegistrar)
    {
        $this->manualRegistrar = $manualRegistrar;
    }

    /**
     * Add a registration.
     *
     * @param \JosKolenberg\LaravelJory\Register\JoryBuilderRegistration $registration
     * @return \JosKolenberg\LaravelJory\Register\JoryBuilderRegistration
     */
    public function add(JoryBuilderRegistration $registration): ? JoryBuilderRegistration
    {
        // Proxy to manual registrar
        $this->manualRegistrar->add($registration);

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
        foreach ($this->manualRegistrar->getRegistrations() as $registration) {
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
        foreach ($this->manualRegistrar->getRegistrations() as $registration) {
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
        foreach ($this->manualRegistrar->getRegistrations() as $registration) {
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
        foreach ($this->manualRegistrar->getRegistrations() as $registration) {
            $result[] = $registration->getUri();
        }

        return $result;
    }
}
