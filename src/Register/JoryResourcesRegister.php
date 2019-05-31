<?php

namespace JosKolenberg\LaravelJory\Register;

use Illuminate\Support\Collection;
use JosKolenberg\LaravelJory\Exceptions\RegistrationNotFoundException;
use JosKolenberg\LaravelJory\Exceptions\ResourceNotFoundException;
use JosKolenberg\LaravelJory\JoryResource;

/**
 * Class JoryBuildersRegister.
 *
 * Collects the registered JoryBuilders.
 */
class JoryResourcesRegister
{

    protected $joryResources;

    /**
     * Any additional registrars to deliver registrations
     *
     * @var array<RegistersJoryResources>
     */
    protected $registrars = [];

    public function __construct()
    {
        $this->joryResources = new Collection();
    }

    public function add(JoryResource $joryResource): ? JoryResourcesRegister
    {
        $this->joryResources->prepend($joryResource);

        return $this;
    }

    /**
     * Add a registrar for delivering registrations
     *
     * @param RegistersJoryResources $registrar
     */
    public function addRegistrar(RegistersJoryResources $registrar)
    {
        $this->registrars[] = $registrar;
    }

    public function getByModelClass(string $modelClass): ? JoryResource
    {
        foreach ($this->getAllJoryResources() as $joryResource) {
            if ($joryResource->getModelClass() === $modelClass) {
                return $joryResource;
            }
        }

        throw new RegistrationNotFoundException('No joryResource found for model ' . $modelClass . '. Does ' . $modelClass . ' have an associated JoryResource?');
    }

    public function getByUri(string $uri): ? JoryResource
    {
        foreach ($this->getAllJoryResources() as $registration) {
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

        foreach ($this->getAllJoryResources()->sortBy(function($joryResource){
            return $joryResource->getUri();
        }) as $joryResource) {
            $result[] = $joryResource->getUri();
        }

        return $result;
    }

    /**
     * Get all registrations registered by all the registrars.
     *
     * @return Collection
     */
    protected function getAllJoryResources(): Collection
    {
        // Manual registrations get precedence.
        $joryResources = $this->joryResources;

        foreach ($this->registrars as $registrar){
            $this->mergeJoryResources($joryResources, $registrar->getJoryResources());
        }

        return $joryResources;
    }

    /**
     * Merge the additional registration collection into the
     * subject collection and filter duplicates.
     *
     * @param Collection $subject
     * @param Collection $additional
     */
    protected function mergeJoryResources(Collection $subject, Collection $additional)
    {
        foreach ($additional as $joryResource){
            /**
             * If the existing collection already has a joryResource for
             * this model, don't register it again.
             */
            if($subject->contains(function ($existing) use ($joryResource) {
                return $existing->getUri() === $joryResource->getUri();
            })){
                continue;
            }

            $subject->push($joryResource);
        }
    }
}
