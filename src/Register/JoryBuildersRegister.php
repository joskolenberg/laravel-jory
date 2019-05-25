<?php

namespace JosKolenberg\LaravelJory\Register;

use Illuminate\Support\Collection;
use JosKolenberg\LaravelJory\Exceptions\RegistrationNotFoundException;
use JosKolenberg\LaravelJory\Exceptions\ResourceNotFoundException;

/**
 * Class JoryBuildersRegister.
 *
 * Collects the registered JoryBuilders.
 */
class JoryBuildersRegister
{

    /**
     * The registrar for registering JoryBuilders manually.
     *
     * @var ManualRegistrar
     */
    protected $manualRegistrar = null;

    /**
     * Any additional registrars to deliver registrations
     *
     * @var array<RegistrarInterface>
     */
    protected $registrars = [];

    /**
     * JoryBuildersRegister constructor.
     * @param ManualRegistrar $manualRegistrar
     */
    public function __construct(ManualRegistrar $manualRegistrar)
    {
        $this->manualRegistrar = $manualRegistrar;
    }

    /**
     * Add a registrar for delivering registrations
     *
     * @param RegistrarInterface $registrar
     */
    public function addRegistrar(RegistrarInterface $registrar)
    {
        $this->registrars[] = $registrar;
    }

    /**
     * Add a registration to the manualRegistrar.
     *
     * @param JoryBuilderRegistration $registration
     * @return JoryBuilderRegistration
     */
    public function add(JoryBuilderRegistration $registration): ? JoryBuilderRegistration
    {
        // Proxy to the manual registrar
        $this->manualRegistrar->add($registration);

        return $registration;
    }

    /**
     * Get a registration by a Model's classname.
     *
     * @param string $modelClass
     * @return JoryBuilderRegistration|null
     * @throws RegistrationNotFoundException
     */
    public function getByModelClass(string $modelClass): ? JoryBuilderRegistration
    {
        foreach ($this->getAllRegistrations() as $registration) {
            if ($registration->getModelClass() === $modelClass) {
                return $registration;
            }
        }

        throw new RegistrationNotFoundException('No registration found for model ' . $modelClass . '. Does ' . $modelClass . ' have an associated JoryBuilder?');
    }

    /**
     * Get a registration by a Model's classname.
     *
     * @param string $builderClass
     * @return JoryBuilderRegistration|null
     * @throws RegistrationNotFoundException
     */
    public function getByBuilderClass(string $builderClass): ? JoryBuilderRegistration
    {
        foreach ($this->getAllRegistrations() as $registration) {
            if ($registration->getBuilderClass() === $builderClass) {
                return $registration;
            }
        }

        throw new RegistrationNotFoundException('No registration found for builderClass ' . $builderClass . '. Does ' . $builderClass . ' have an associated Model?');
    }

    /**
     * Get a registration by uri.
     *
     * @param string $uri
     * @return JoryBuilderRegistration|null
     * @throws ResourceNotFoundException
     */
    public function getByUri(string $uri): ? JoryBuilderRegistration
    {
        foreach ($this->getAllRegistrations() as $registration) {
            if ($registration->getUri() == $uri) {
                return $registration;
            }
        }

        throw new ResourceNotFoundException($uri);
    }

    /**
     * Get an array of all registered uri's.
     *
     * @return array
     */
    public function getUrisArray(): array
    {
        $result = [];

        foreach ($this->getAllRegistrations()->sortBy(function($registration){
            return $registration->getUri();
        }) as $registration) {
            $result[] = $registration->getUri();
        }

        return $result;
    }

    /**
     * Get all registrations registered by all the registrars.
     *
     * @return Collection
     */
    protected function getAllRegistrations(): Collection
    {
        // Manual registrations get precedence.
        $registrations = $this->manualRegistrar->getRegistrations();

        foreach ($this->registrars as $registrar){
            $this->mergeRegistrations($registrations, $registrar->getRegistrations());
        }

        return $registrations;
    }

    /**
     * Merge the additional registration collection into the
     * subject collection and filter duplicates.
     *
     * @param Collection $subject
     * @param Collection $additional
     */
    protected function mergeRegistrations(Collection $subject, Collection $additional)
    {
        foreach ($additional as $registration){
            /**
             * If the existing collection already has a registration for
             * this model, don't register it again.
             */
            if($subject->contains(function ($existing) use ($registration) {
                return $existing->getModelClass() === $registration->getModelClass();
            })){
                continue;
            }

            $subject->push($registration);
        }
    }
}
